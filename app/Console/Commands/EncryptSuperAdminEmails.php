<?php

namespace App\Console\Commands;

use App\Support\SystemEncrypter;
use Illuminate\Console\Command;

class EncryptSuperAdminEmails extends Command
{
    protected $signature = 'security:encrypt-super-admins
                            {emails* : Email addresses}';

    protected $description = 'Encrypt bootstrap super-admin email addresses';

    public function handle(): int
    {
        try {
            $emails = array_values(
                array_unique(
                    array_map(
                        fn (string $email): string =>
                            strtolower(trim($email)),
                        $this->argument('emails'),
                    ),
                ),
            );

            $encrypted = SystemEncrypter::encryptSuperAdminData(
                implode(',', $emails),
            );

            $this->newLine();

            $this->info(
                'SUPER_ADMIN_EMAILS generated successfully.'
            );

            $this->newLine();

            $this->line(
                'SUPER_ADMIN_EMAILS=' . $encrypted
            );

            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}