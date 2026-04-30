<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today   = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $todayRevenue   = Transaction::whereDate('served_at', $today)->sum('total');
        $weekRevenue    = Transaction::where('served_at', '>=', $weekStart)->sum('total');
        $monthRevenue   = Transaction::where('served_at', '>=', $monthStart)->sum('total');

        $todayCount  = Transaction::whereDate('served_at', $today)->count();
        $weekCount   = Transaction::where('served_at', '>=', $weekStart)->count();

        return [
            Stat::make('Today\'s Revenue', 'KES ' . number_format($todayRevenue, 2))
                ->description("{$todayCount} customers today")
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('This Week', 'KES ' . number_format($weekRevenue, 2))
                ->description("{$weekCount} transactions")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('This Month', 'KES ' . number_format($monthRevenue, 2))
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
