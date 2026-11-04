<?php

namespace App\Console\Commands;

use App\Support\SystemEncrypter;
use Illuminate\Console\Command;

class GenerateSystemSeal extends Command
{
    protected $signature = 'system:generate-seal';

    protected $description = 'Generate the system integrity seal';

    public function handle(): int
    {
        try {
            $seal = SystemEncrypter::getSeal();

            $this->newLine();

            $this->info('System seal generated successfully.');

            $this->newLine();

            $this->line(
                "'seal' => '" . $seal . "',"
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