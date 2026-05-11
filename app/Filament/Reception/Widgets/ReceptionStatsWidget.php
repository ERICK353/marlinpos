<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use App\Models\Expense;
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
        
        $monthlyRevenue = Transaction::whereMonth('served_at', now()->month)
            ->whereYear('served_at', now()->year)
            ->sum('total');

        $monthlyExpenses = Expense::whereMonth('spent_at', now()->month)
            ->whereYear('spent_at', now()->year)
            ->sum('amount');

        $netRevenue = $monthlyRevenue - $monthlyExpenses;

        return [
            Stat::make("Today's Revenue", 'KES ' . number_format($todayRevenue, 2))
                ->description('Total shop sales today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make("Monthly Revenue", 'KES ' . number_format($monthlyRevenue, 2))
                ->description('Total sales this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make("Monthly Expenses", 'KES ' . number_format($monthlyExpenses, 2))
                ->description('Total shop spending this month')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),
            Stat::make("Net Revenue", 'KES ' . number_format($netRevenue, 2))
                ->description('Profit after expenses')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($netRevenue >= 0 ? 'success' : 'danger'),
        ];
    }
}
