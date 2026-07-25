<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class RevenueStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        $isFiltered = $startDate || $endDate;

        if ($isFiltered) {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
            $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : Carbon::now()->endOfDay();

            $revenue   = Transaction::whereBetween('served_at', [$start, $end])->sum('total');
            $expenses  = \App\Models\Expense::whereBetween('spent_at', [$start, $end])->sum('amount');
            $profit    = $revenue - $expenses;
            $count     = Transaction::whereBetween('served_at', [$start, $end])->count();

            $label = Carbon::parse($start)->format('d M') . ' – ' . Carbon::parse($end)->format('d M Y');

            $bonusesCount = TransactionItem::where('is_bonus', true)
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
                    ->description("{$count} transactions")
                    ->descriptionIcon('heroicon-m-users')
                    ->color('success'),

                Stat::make("Cash & M-Pesa ({$label})", 'KES ' . number_format($cashAmount + $mpesaAmount, 2))
                    ->description('Cash: KES ' . number_format($cashAmount) . ' | M-Pesa: KES ' . number_format($mpesaAmount))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('info'),

                Stat::make("Net Profit ({$label})", 'KES ' . number_format($profit, 2))
                    ->description('Revenue minus expenses')
                    ->descriptionIcon('heroicon-m-calculator')
                    ->color($profit >= 0 ? 'success' : 'danger'),
            ];
        }

        // Default: today + this week + this month
        $today      = Carbon::today();
        $weekStart  = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $todayRevenue  = Transaction::whereDate('served_at', $today)->sum('total');
        $monthRevenue  = Transaction::where('served_at', '>=', $monthStart)->sum('total');

        $monthExpenses = \App\Models\Expense::where('spent_at', '>=', $monthStart)->sum('amount');
        $monthProfit   = $monthRevenue - $monthExpenses;

        $todayCount = Transaction::whereDate('served_at', $today)->count();


        // Cash vs Mpesa month breakdown
        $monthTransactions = Transaction::where('served_at', '>=', $monthStart)->get();
        $monthCash  = $monthTransactions->sum(fn ($t) => $t->payment_method === 'cash' ? $t->total : $t->cash_paid);
        $monthMpesa = $monthTransactions->sum(fn ($t) => $t->payment_method === 'mpesa' ? $t->total : $t->mpesa_paid);

        return [
            Stat::make('Today\'s Revenue', 'KES ' . number_format($todayRevenue, 2))
                ->description("{$todayCount} customers today")
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('This Month Cash & M-Pesa', 'KES ' . number_format($monthCash + $monthMpesa, 2))
                ->description('Cash: KES ' . number_format($monthCash) . ' | M-Pesa: KES ' . number_format($monthMpesa))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Net Revenue', 'KES ' . number_format($monthProfit, 2))
                ->description('Total profit after expenses')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($monthProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
