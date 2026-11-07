<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\SystemSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PurgeUnassignedUsers extends Command
{
    protected $signature = 'users:purge-unassigned
                            {--days=30 : Number of days before permanent deletion}
                            {--dry-run : Preview eligible accounts without deleting them}';

    protected $description = 'Permanently delete users without an allocation after the specified number of days';

    public function handle(): int
    {
        $days = filter_var(
            $this->option('days'),
            FILTER_VALIDATE_INT
        );

        if ($days === false || $days < 1) {
            $this->error('The number of days must be a positive integer.');

            return self::INVALID;
        }

        $cutoff = now()->subDays($days);

        /*
         * Protected accounts:
         *
         * 1. All configured Super Admin emails.
         * 2. All configured developer emails.
         *
         * These accounts are exempt regardless of their
         * user_allocation_id or registration date.
         */
        $excludedEmails = collect(SystemSecurity::superAdminEmails())
            ->merge(User::developerEmails())
            ->map(fn ($email): string => mb_strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        /*
         * Eligible accounts:
         *
         * 1. user_allocation_id IS NULL
         * 2. Registration date is at least the configured age.
         * 3. Email is not protected.
         *
         * Soft-deleted accounts are excluded by default
         * because this uses User::query().
         */
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
        $this->line('Retention period: ' . $days . ' days');
        $this->line('Protected email addresses: ' . count($excludedEmails));
        $this->line('Eligible accounts: ' . $total);
        $this->newLine();

        /*
         * Preview mode.
         * No database records will be deleted.
         */
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
                [
                    'ID',
                    'Name',
                    'Email',
                    'Allocation ID',
                    'Registered',
                ],
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

            $this->info('Dry run completed. No accounts were deleted.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $skipped = 0;
        $failed = 0;

        /*
         * Process in batches to avoid loading every account into memory.
         */
        $query->orderBy('id')->chunkById(
            100,
            function ($users) use (
                $cutoff,
                $excludedEmails,
                &$deleted,
                &$skipped,
                &$failed
            ): void {
                foreach ($users as $candidate) {
                    try {
                        $wasDeleted = DB::transaction(
                            function () use (
                                $candidate,
                                $cutoff,
                                $excludedEmails
                            ): bool {
                                /*
                                 * Lock the account and reload it.
                                 * This protects against concurrent
                                 * allocation changes.
                                 */
                                $user = User::query()
                                    ->whereKey($candidate->id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($user === null) {
                                    return false;
                                }

                                $email = mb_strtolower(
                                    trim((string) $user->email)
                                );

                                /*
                                 * Never delete protected administrators
                                 * or developers, regardless of allocation.
                                 */
                                if (
                                    in_array(
                                        $email,
                                        $excludedEmails,
                                        true
                                    )
                                    || SystemSecurity::isBootstrapSuperAdmin($user)
                                ) {
                                    return false;
                                }

                                /*
                                 * Recheck eligibility immediately before
                                 * permanent deletion.
                                 */
                                if (
                                    $user->user_allocation_id !== null
                                    || $user->created_at === null
                                    || $user->created_at->gt($cutoff)
                                ) {
                                    return false;
                                }

                                /*
                                 * Permanent physical deletion.
                                 *
                                 * Existing User model forceDeleting
                                 * protection remains active.
                                 */
                                return (bool) $user->forceDelete();
                            }
                        );

                        if ($wasDeleted) {
                            $deleted++;
                        } else {
                            $skipped++;
                        }
                    } catch (Throwable $exception) {
                        $failed++;

                        Log::error(
                            'Permanent deletion of unassigned user failed.',
                            [
                                'user_id' => $candidate->id,
                                'error' => $exception->getMessage(),
                            ]
                        );

                        $this->error(
                            "Failed to delete user ID {$candidate->id}."
                        );
                    }
                }
            }
        );

        $this->newLine();

        $this->info('USER CLEANUP RESULTS');
        $this->line("Permanently deleted: {$deleted}");
        $this->line("Skipped: {$skipped}");
        $this->line("Failed: {$failed}");

        Log::info('Unassigned user cleanup completed.', [
            'retention_days' => $days,
            'cutoff' => $cutoff->toDateTimeString(),
            'eligible' => $total,
            'deleted' => $deleted,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
