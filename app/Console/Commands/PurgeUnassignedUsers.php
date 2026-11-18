<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\SystemSecurity;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class PurgeUnassignedUsers extends Command
{
    protected $signature = 'users:purge-unassigned
                            {--days=30 : Number of days before permanent deletion}
                            {--minutes= : Use this many minutes instead of days}
                            {--dry-run : Preview eligible accounts without deleting them or sending emails}';

    protected $description = 'Permanently delete users without an allocation after the specified retention period';

    public function handle(): int
    {
        $minutes = $this->option('minutes');
        $useMinutes = $minutes !== null || $this->input->hasParameterOption('--minutes', true);
        $unit = $useMinutes ? 'minutes' : 'days';
        $retention = filter_var(
            $useMinutes ? $minutes : $this->option('days'),
            FILTER_VALIDATE_INT
        );

        if ($retention === false || $retention < 1) {
            $this->error("The number of {$unit} must be a positive integer.");

            return self::INVALID;
        }

        $cutoff = $useMinutes ? now()->subMinutes($retention) : now()->subDays($retention);
        $retentionLabel = $retention === 1 ? rtrim($unit, 's') : $unit;

        $excludedEmails = collect(SystemSecurity::superAdminEmails())
            ->merge(User::developerEmails())
            ->map(fn ($email): string => mb_strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // The default User scope excludes soft-deleted accounts.
        $query = User::query()
            ->whereNull('user_allocation_id')
            ->where('created_at', '<=', $cutoff);

        if ($excludedEmails !== []) {
            $query->whereNotIn(
                DB::raw('LOWER(TRIM(email))'),
                $excludedEmails
            );
        }

        $total = (clone $query)->count();

        $this->newLine();
        $this->info('UNASSIGNED USER CLEANUP');
        $this->line('Registration cutoff: ' . $cutoff->toDateTimeString());
        $this->line("Retention period: {$retention} {$retentionLabel}");
        $this->line('Protected email addresses: ' . count($excludedEmails));
        $this->line('Eligible accounts: ' . $total);
        $this->newLine();

        if ($this->option('dry-run')) {
            $users = (clone $query)
                ->orderBy('id')
                ->limit(50)
                ->get([
                    'id',
                    'name',
                    'email',
                    'user_allocation_id',
                    'created_at',
                ]);

            $this->table(
                ['ID', 'Name', 'Email', 'Allocation ID', 'Registered'],
                $users->map(fn (User $user): array => [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->user_allocation_id ?? 'NULL',
                    $user->created_at?->toDateTimeString(),
                ])->all()
            );

            if ($total > 50) {
                $this->warn("Showing 50 of {$total} eligible accounts.");
            }

            $this->info('Dry run completed. No accounts were deleted or emailed.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $skipped = 0;
        $failed = 0;
        $emailsSent = 0;
        $emailsFailed = 0;

        $query->orderBy('id')->chunkById(
            100,
            function ($users) use (
                $cutoff,
                $excludedEmails,
                &$deleted,
                &$skipped,
                &$failed,
                &$emailsSent,
                &$emailsFailed
            ): void {
                foreach ($users as $candidate) {
                    try {
                        $recipient = DB::transaction(
                            function () use (
                                $candidate,
                                $cutoff,
                                $excludedEmails
                            ): ?string {
                                // Recheck the latest account state while holding the row lock.
                                $user = User::query()
                                    ->whereKey($candidate->id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($user === null) {
                                    return null;
                                }

                                $recipient = trim((string) $user->email);
                                $normalizedEmail = mb_strtolower($recipient);

                                if (
                                    in_array($normalizedEmail, $excludedEmails, true)
                                    || SystemSecurity::isBootstrapSuperAdmin($user)
                                ) {
                                    return null;
                                }

                                if (
                                    $user->user_allocation_id !== null
                                    || $user->created_at === null
                                    || $user->created_at->gt($cutoff)
                                ) {
                                    return null;
                                }

                                return $user->forceDelete() ? $recipient : null;
                            }
                        );
                    } catch (Throwable $exception) {
                        $failed++;

                        Log::error('Permanent deletion of unassigned user failed.', [
                            'user_id' => $candidate->id,
                            'error' => $exception->getMessage(),
                        ]);

                        $this->error("Failed to delete user ID {$candidate->id}.");

                        continue;
                    }

                    if ($recipient === null) {
                        $skipped++;

                        continue;
                    }

                    $deleted++;

                    // Send after the transaction commits and releases the row lock.
                    try {
                        $this->sendDeletionEmail($recipient);
                        $emailsSent++;
                    } catch (Throwable $exception) {
                        $emailsFailed++;

                        Log::error('Account deletion email failed.', [
                            'user_id' => $candidate->id,
                            'error' => $exception->getMessage(),
                        ]);

                        $this->warn("Account deleted, but email failed for user ID {$candidate->id}.");
                    }
                }
            }
        );

        $this->newLine();

        $this->info('USER CLEANUP RESULTS');
        $this->line("Permanently deleted: {$deleted}");
        $this->line("Skipped: {$skipped}");
        $this->line("Deletion failures: {$failed}");
        $this->line("Deletion emails sent: {$emailsSent}");
        $this->line("Deletion email failures: {$emailsFailed}");

        Log::info('Unassigned user cleanup completed.', [
            'retention_' . $unit => $retention,
            'cutoff' => $cutoff->toDateTimeString(),
            'eligible' => $total,
            'deleted' => $deleted,
            'skipped' => $skipped,
            'failed' => $failed,
            'emails_sent' => $emailsSent,
            'emails_failed' => $emailsFailed,
        ]);

        return ($failed > 0 || $emailsFailed > 0)
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function sendDeletionEmail(string $recipient): void
    {
        $appName = config('app.name');

        $body = <<<TEXT
        Hello,

        Your {$appName} account was permanently deleted because it had no assigned role.

        You can create a new account. After registering, please contact your administrator for help getting the role you need.

        Thank you,
        The {$appName} team
        TEXT;

        $sentMessage = Mail::raw($body, function (Message $message) use ($recipient, $appName): void {
            $message->to($recipient)->subject("Your {$appName} account was deleted");
        });

        if ($sentMessage === null) {
            throw new RuntimeException('The mailer did not send the account deletion email.');
        }
    }
}
