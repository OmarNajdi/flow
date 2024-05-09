<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->navigationItems([
                NavigationItem::make('Flow Accelerator Website')
                    ->url('https://flow.ps', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-globe-europe-africa')
                    ->group('Flow Accelerator')
                    ->sort(99),
                NavigationItem::make('970TechMap')
                    ->url('https://970techmap.ps/', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-map-pin')
                    ->group('Flow Accelerator')
                    ->sort(99),
//                NavigationItem::make('Flow Accelerator Facebook')
//                    ->url('https://www.facebook.com/accelerator.Flow', shouldOpenInNewTab: true)
//                    ->icon('bi-facebook')
//                    ->group('Flow Accelerator')
//                    ->sort(99),
//                NavigationItem::make('Flow Accelerator LinkedIn')
//                    ->url('https://www.linkedin.com/company/flow-accelerator/', shouldOpenInNewTab: true)
//                    ->icon('bi-linkedin')
//                    ->group('Flow Accelerator')
//                    ->sort(99),
//                NavigationItem::make('Flow Accelerator YouTube')
//                    ->url('https://www.youtube.com/channel/UCFakgIkVNQSHnHI_wYhfGWw')
//                    ->icon('bi-youtube')
//                    ->group('Flow Accelerator')
//                    ->sort(99),
//                NavigationItem::make('Flow Accelerator Instagram')
//                    ->url('https://www.instagram.com/flow.accelerator/', shouldOpenInNewTab: true)
//                    ->icon('bi-instagram')
//                    ->group('Flow Accelerator')
//                    ->sort(99),
            ])
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
            ]);
    }
}
