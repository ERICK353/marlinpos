<?php

namespace App\Filament\Staff\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StaffStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // Today's stats
        $todayRevenue = TransactionItem::where('staff_user_id', $userId)
            ->whereHas('transaction', fn ($q) => $q->whereDate('served_at', $today))
            ->sum('line_total');

        $todayServices = TransactionItem::where('staff_user_id', $userId)
            ->whereHas('transaction', fn ($q) => $q->whereDate('served_at', $today))
            ->count();

        // Monthly stats
        $monthRevenue = TransactionItem::where('staff_user_id', $userId)
            ->whereHas('transaction', fn ($q) => $q->where('served_at', '>=', $monthStart))
            ->sum('line_total');

        $freeShaves = TransactionItem::where('staff_user_id', $userId)
            ->whereHas('transaction', fn ($q) => $q->where('served_at', '>=', $monthStart)->where('is_free_shave', true))
            ->count();

        return [
            Stat::make("Today's Revenue", 'KES ' . number_format($todayRevenue, 2))
                ->description('Total from services handled today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make("Today's Services", $todayServices)
                ->description('Services performed today')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make("Monthly Revenue", 'KES ' . number_format($monthRevenue, 2))
                ->description('Earnings for ' . Carbon::now()->format('F'))
                ->descriptionIcon('heroicon-m-chart-bar'),
            Stat::make("Loyalty Impact", $freeShaves)
                ->description('Free shaves given this month')
                ->descriptionIcon('heroicon-m-gift')
                ->color('warning'),
        ];
    }
}
