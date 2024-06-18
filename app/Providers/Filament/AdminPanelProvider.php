<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kenepa\TranslationManager\TranslationManagerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => "#018578",
            ])
            ->brandLogo(asset('images/flow.svg'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/favicon-flow.png'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
//                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Social')
                    ->extraSidebarAttributes(['class' => 'social-sidebar-group'])
            ])
            ->navigationItems([
                NavigationItem::make('Flow Accelerator Website')
                    ->label(fn() => __('Flow Accelerator Website'))
                    ->url('https://flow.ps', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-globe-europe-africa')
                    ->group(fn() => __('Flow Accelerator'))
                    ->sort(99),
                NavigationItem::make('970TechMap')
                    ->url('https://970techmap.ps/', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-map-pin')
                    ->group(fn() => __('Flow Accelerator'))
                    ->sort(99),
                NavigationItem::make('Facebook')
                    ->url('https://www.facebook.com/accelerator.Flow', shouldOpenInNewTab: true)
                    ->icon('lineawesome-facebook')
                    ->group('Social')
                    ->sort(99),
                NavigationItem::make('LinkedIn')
                    ->url('https://www.linkedin.com/company/flow-accelerator/', shouldOpenInNewTab: true)
                    ->icon('lineawesome-linkedin')
                    ->group('Social')
                    ->sort(99),
                NavigationItem::make('YouTube')
                    ->url('https://www.youtube.com/channel/UCFakgIkVNQSHnHI_wYhfGWw')
                    ->icon('lineawesome-youtube')
                    ->group('Social')
                    ->sort(99),
                NavigationItem::make('Instagram')
                    ->url('https://www.instagram.com/flow.accelerator/', shouldOpenInNewTab: true)
                    ->icon('lineawesome-instagram')
                    ->group('Social')
                    ->sort(99),
            ])
            ->renderHook(
                'panels::head.start',
                fn() => view('google_analytics'),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(TranslationManagerPlugin::make());
    }
}
