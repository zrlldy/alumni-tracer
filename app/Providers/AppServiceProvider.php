<?php

namespace App\Providers;

use App\Notifications\AlumniWelcome;
use App\Notifications\BrandedEmailVerification;
use App\Notifications\BrandedPasswordReset;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(ResetPassword::class, BrandedPasswordReset::class);
        $this->app->bind(VerifyEmail::class, BrandedEmailVerification::class);

        Event::listen(Verified::class, function (Verified $event): void {
            $event->user->notify(new AlumniWelcome);
        });
    }
}
