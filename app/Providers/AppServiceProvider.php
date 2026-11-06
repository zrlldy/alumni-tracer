<?php

namespace App\Providers;

use App\Notifications\AlumniWelcome;
use App\Notifications\BrandedEmailVerification;
use App\Notifications\BrandedPasswordReset;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Navigation\NavigationItem;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Support\Htmlable;
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

        NavigationItem::configureUsing(function (NavigationItem $item): void {
            $label = $item->getLabel();

            // Support HTML navigation labels.
            if ($label instanceof Htmlable) {
                $label = strip_tags($label->toHtml());
            }

            $label = trim(
                html_entity_decode(
                    (string) $label,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                )
            );

            if (blank($label)) {
                return;
            }

            $item->extraAttributes([
                'data-tooltip' => $label,
            ], merge: true);
        });

    }
}
