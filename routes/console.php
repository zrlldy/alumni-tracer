<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('users:purge-unassigned --days=30')
    ->dailyAt('02:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();