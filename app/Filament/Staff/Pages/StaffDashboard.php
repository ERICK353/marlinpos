<?php

namespace App\Filament\Staff\Pages;

use App\Models\StaffLog;
use Filament\Panel;
use Filament\Pages\Dashboard as BaseDashboard;

class StaffDashboard extends BaseDashboard
{
    public static function getNavigationIcon(): string { return 'heroicon-o-home'; }
    public static function getRoutePath(Panel $panel): string { return '/'; }
    public function getTitle(): string { return 'My Dashboard'; }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Staff\Widgets\StaffStatsWidget::class,
            \App\Filament\Staff\Widgets\StaffRevenueChart::class,
            \App\Filament\Staff\Widgets\StaffServiceMixChart::class,
            \App\Filament\Staff\Widgets\StaffRecentServicesWidget::class,
        ];
    }

    public function mount(): void
    {
        // Record time-in for today if not already done
        StaffLog::firstOrCreate(
            [
                'user_id'  => auth()->id(),
                'log_date' => today(),
            ],
            [
                'logged_in_at' => now(),
            ]
        );
    }
}
