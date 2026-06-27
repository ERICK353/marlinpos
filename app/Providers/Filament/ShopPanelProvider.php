<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
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
use App\Filament\Shop\Widgets\RevenueStatsWidget;
use App\Filament\Shop\Widgets\StaffPerformanceWidget;
use App\Filament\Shop\Widgets\LoyaltyStatsWidget;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class ShopPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('shop')
            ->path('shop')
            ->login()
            ->profile()
            ->brandName('Malyn Executive Barber')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Shop/Resources'), for: 'App\\Filament\\Shop\\Resources')
            ->discoverPages(in: app_path('Filament/Shop/Pages'), for: 'App\\Filament\\Shop\\Pages')
            ->pages([
                \App\Filament\Shop\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Shop/Widgets'), for: 'App\\Filament\\Shop\\Widgets')
            ->widgets([
                RevenueStatsWidget::class,
                StaffPerformanceWidget::class,
                LoyaltyStatsWidget::class,
                \App\Filament\Shop\Widgets\PaymentMethodChartWidget::class,
                \App\Filament\Shop\Widgets\ServiceDistributionChart::class,
                \App\Filament\Shop\Widgets\ShopRevenueChart::class,
                \App\Filament\Shop\Widgets\ShopExpensesChart::class,
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
                InitializeTenancyByDomain::class,

            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
