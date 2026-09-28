<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AlumniPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('online')
            ->path('online')
            ->brandName('Alumni Tracer')
            // ->brandLogo(asset('favicon.png'))
            ->favicon(asset('favicon.ico'))
            ->profile(isSimple: false)
            ->login(Login::class)
            // ->login()
            ->registration()
            ->passwordReset()
            ->colors([
                'primary' => Color::hex('#F77F00'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable()->recoveryCodeCount(10)->codeWindow(4),
                EmailAuthentication::make()->codeExpiryMinutes(2),
            ])
            ->plugins([
                AuthUIEnhancerPlugin::make()
                ->formPanelPosition('left')
                ->formPanelWidth('45%')
                // ->formPanelBackgroundColor(Color::Zinc, '300')
                // ->mobileFormPanelPosition('bottom')
                ->showEmptyPanelOnMobile(false)
                ->emptyPanelView('login'),
            ])
            ->viteTheme('resources/css/filament/online/theme.css');
    }
}
