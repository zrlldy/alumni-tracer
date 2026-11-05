<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Register;
use App\Http\Middleware\RepairFilamentAlpineExpressions;
use App\Support\PluginAccess;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use HTMLMin\HTMLMin\Http\Middleware\MinifyMiddleware;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Sujip\Filament\Turnstile\Contracts\TurnstileClientContract;
use JohnRivera7\FilamentWidgetGrid\FilamentWidgetGridPlugin;
use Livewire\Livewire;

class AlumniPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->spa()
            ->spa(hasPrefetching: true)
            ->id('online')
            ->path('online')
            ->brandLogo(asset('images/alumni-darklogo.svg'))
            ->darkModeBrandLogo(asset('images/alumni-whitelogo.svg'))
            ->brandLogoHeight('2.3rem')
            ->brandName('Alumni Tracer')
            ->favicon(asset('favicon.ico'))
            // ->sidebarCollapsibleOnDesktop()
            // ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('10rem')
            ->topNavigation()


            // ->profile(isSimple: false)
            ->profile(EditProfile::class, 
                isSimple: false,)
            ->userMenuItems([
            'profile' => fn(Action $action) => $action
                ->label('Settings')
                ->icon('heroicon-o-cog-6-tooth'),

            ])
            ->login(Login::class)
            // ->login()
            // ->registration()
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification()
            ->colors([
                'primary' => Color::hex('#D62828'), // Brand red
                'success' => Color::Green,          // Successful actions
                'warning' => Color::hex('#FCBF49'), // Golden yellow
                'danger'  => Color::hex('#D62828'), // Errors and delete actions
                'info'    => Color::hex('#003049'), // Navy for information
                'gray'    => Color::Zinc,           // Neutral backgrounds and text

                // Extra brand color for selected components
                'accent'  => Color::hex('#F77F00'), // Orange
            ])
            // ->navigationGroups([
            //     NavigationGroup::make()
            //         ->label('Management')
            //         ->icon(Heroicon::OutlinedWrenchScrewdriver)
            //         ->collapsed(),
            // ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                function (): string {
                    $livewire = Livewire::current();

                    if ($livewire && filled($livewire->userUndertakingMultiFactorAuthentication ?? null)) {
                        return '';
                    }

                    return view('filament.auth.login-footer')->render();
                },
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => view('filament.vite-assets')->render()
                    . (app(TurnstileClientContract::class)->isConfigured()
                        ? '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>'
                        : ''),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            // ->pages([
            //     Dashboard::class,
            // ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // ->widgets([
            //     AccountWidget::class,
            //     FilamentInfoWidget::class,
            // ])
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
                // RepairFilamentAlpineExpressions::class,
                // MinifyMiddleware::class,
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
                FilamentWidgetGridPlugin::make('online')
                    ->columns(24)
                    ->cellHeight(45)
                    ->maxHeight(60)
                    ->density('comfortable') // or 'compact'
                    ->float(true)
                    ->templates(true)
                    ->canViewWidget(fn (string $widget): bool => true)
                    // ->canCustomize(fn (): bool => Auth::check())
                    ->canCustomize(
                        fn(): bool => PluginAccess::canAccessPageSlug('widget-grid')
                    )
                    ->canManageDefaults(
                        fn(): bool => PluginAccess::canAccessPageSlug('widget-grid')
                    )
                    // ->canManageDefaults(fn (): bool => false),
            ])
            ->viteTheme('resources/css/filament/online/theme.css');
    }
}
