<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class ReceptionStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today();
        $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : Carbon::today()->endOfDay();

        $isFiltered = $startDate || $endDate;

        if ($isFiltered) {
            // Use the selected date range
            $revenue  = Transaction::whereBetween('served_at', [$start, $end])->sum('total');
            $expenses = Expense::whereBetween('spent_at', [$start, $end])->sum('amount');
            $net      = $revenue - $expenses;

            $bonusesCount = \App\Models\TransactionItem::where('is_bonus', true)
                ->whereHas('transaction', fn ($q) => $q->whereBetween('served_at', [$start, $end]))
                ->count();

            $cashAmount = Transaction::whereBetween('served_at', [$start, $end])
                ->where(function ($q) {
                    $q->where('payment_method', 'cash')->orWhere('cash_paid', '>', 0);
                })
                ->get()
                ->sum(fn ($t) => $t->payment_method === 'cash' ? $t->total : $t->cash_paid);

            $mpesaAmount = Transaction::whereBetween('served_at', [$start, $end])
                ->where(function ($q) {
                    $q->where('payment_method', 'mpesa')->orWhere('mpesa_paid', '>', 0);
                })
                ->get()
                ->sum(fn ($t) => $t->payment_method === 'mpesa' ? $t->total : $t->mpesa_paid);

            $label = Carbon::parse($start)->format('d M') . ' – ' . Carbon::parse($end)->format('d M Y');

            return [
                Stat::make("Revenue ({$label})", 'KES ' . number_format($revenue, 2))
                    ->description('Total shop sales in selected range')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),
                Stat::make("Cash & M-Pesa ({$label})", 'KES ' . number_format($cashAmount + $mpesaAmount, 2))
                    ->description('Cash: KES ' . number_format($cashAmount) . ' | M-Pesa: KES ' . number_format($mpesaAmount))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('info'),
                Stat::make("Net Revenue ({$label})", 'KES ' . number_format($net, 2))
                    ->description('Profit after expenses')
                    ->descriptionIcon('heroicon-m-calculator')
                    ->color($net >= 0 ? 'success' : 'danger'),
            ];
        }

        // Default: today + this month
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $todayRevenue    = Transaction::whereDate('served_at', $today)->sum('total');
        $monthlyRevenue  = Transaction::whereBetween('served_at', [$monthStart, $monthEnd])->sum('total');
        $monthlyExpenses = Expense::whereBetween('spent_at', [$monthStart, $monthEnd])->sum('amount');
        $netRevenue      = $monthlyRevenue - $monthlyExpenses;


        $monthTransactions = Transaction::whereBetween('served_at', [$monthStart, $monthEnd])->get();
        $monthCash  = $monthTransactions->sum(fn ($t) => $t->payment_method === 'cash' ? $t->total : $t->cash_paid);
        $monthMpesa = $monthTransactions->sum(fn ($t) => $t->payment_method === 'mpesa' ? $t->total : $t->mpesa_paid);

        return [
            Stat::make("Today's Revenue", 'KES ' . number_format($todayRevenue, 2))
                ->description('Total shop sales today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('This Month Cash & M-Pesa', 'KES ' . number_format($monthCash + $monthMpesa, 2))
                ->description('Cash: KES ' . number_format($monthCash) . ' | M-Pesa: KES ' . number_format($monthMpesa))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make("Net Revenue", 'KES ' . number_format($netRevenue, 2))
                ->description('Profit after expenses')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($netRevenue >= 0 ? 'success' : 'danger'),
        ];
    }
}
