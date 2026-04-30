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
