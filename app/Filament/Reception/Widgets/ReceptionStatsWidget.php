<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use App\Models\StaffLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ReceptionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Carbon::today();

        $todayRevenue = Transaction::whereDate('served_at', $today)->sum('total');
        $todayTransactions = Transaction::whereDate('served_at', $today)->count();
        $activeStaff = StaffLog::whereNull('logged_out_at')->whereDate('log_date', $today)->count();

        return [
            Stat::make("Today's Revenue", 'KES ' . number_format($todayRevenue, 2))
                ->description('Total shop sales today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make("Total Transactions", $todayTransactions)
                ->description('Number of customers served')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make("Active Staff", $activeStaff)
                ->description('Staff currently clocked in')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
