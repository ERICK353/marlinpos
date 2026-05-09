<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class ReceptionPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('reception')
            ->path('reception')
            ->login()
            ->brandName('Malyn Executive Barber')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->widgets([
                \App\Filament\Reception\Widgets\ReceptionStatsWidget::class,
                \App\Filament\Reception\Widgets\ShopRevenueChart::class,
                \App\Filament\Reception\Widgets\PaymentMethodChart::class,
                \App\Filament\Reception\Widgets\ServiceDistributionChart::class,
                \App\Filament\Reception\Widgets\CustomerTypeChart::class,
            ])
            ->discoverResources(in: app_path('Filament/Reception/Resources'), for: 'App\\Filament\\Reception\\Resources')
            ->discoverPages(in: app_path('Filament/Reception/Pages'), for: 'App\\Filament\\Reception\\Pages')
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Reception/Widgets'), for: 'App\\Filament\\Reception\\Widgets')
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
                InitializeTenancyByDomain::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
