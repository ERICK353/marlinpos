<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class PaymentMethodStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        $isFiltered = $startDate || $endDate;

        if ($isFiltered) {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
            $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : Carbon::now()->endOfDay();

            // Calculate cash amount (pure cash + cash component in split / wallet)
            $cashAmount = Transaction::whereBetween('served_at', [$start, $end])
                ->where(function ($q) {
                    $q->where('payment_method', 'cash')
                      ->orWhere('cash_paid', '>', 0);
                })
                ->get()
                ->sum(fn ($t) => $t->payment_method === 'cash' ? $t->total : $t->cash_paid);

            // Calculate mpesa amount (pure mpesa + mpesa component in split / wallet)
            $mpesaAmount = Transaction::whereBetween('served_at', [$start, $end])
                ->where(function ($q) {
                    $q->where('payment_method', 'mpesa')
                      ->orWhere('mpesa_paid', '>', 0);
                })
                ->get()
                ->sum(fn ($t) => $t->payment_method === 'mpesa' ? $t->total : $t->mpesa_paid);

            $totalAmount = $cashAmount + $mpesaAmount;

            $label = Carbon::parse($start)->format('d M') . ' – ' . Carbon::parse($end)->format('d M Y');

            $bonusesCount = TransactionItem::where('is_bonus', true)
                ->whereHas('transaction', fn ($q) => $q->whereBetween('served_at', [$start, $end]))
                ->count();

            return [
                Stat::make("Cash ({$label})", 'KES ' . number_format($cashAmount, 2))
                    ->description('Physical cash collected')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('amber'),

                Stat::make("M-Pesa ({$label})", 'KES ' . number_format($mpesaAmount, 2))
                    ->description('M-Pesa digital payments')
                    ->descriptionIcon('heroicon-m-device-phone-mobile')
                    ->color('success'),

                Stat::make("Bonuses Given ({$label})", number_format($bonusesCount))
                    ->description('Bonuses')
                    ->descriptionIcon('heroicon-m-gift')
                    ->color('warning'),
            ];
        }

        // Default: today's & month's cash and mpesa breakdown
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todayTransactions = Transaction::whereDate('served_at', $today)->get();
        $todayCash = $todayTransactions->sum(fn ($t) => $t->payment_method === 'cash' ? $t->total : $t->cash_paid);
        $todayMpesa = $todayTransactions->sum(fn ($t) => $t->payment_method === 'mpesa' ? $t->total : $t->mpesa_paid);

        $monthTransactions = Transaction::where('served_at', '>=', $monthStart)->get();
        $monthCash = $monthTransactions->sum(fn ($t) => $t->payment_method === 'cash' ? $t->total : $t->cash_paid);
        $monthMpesa = $monthTransactions->sum(fn ($t) => $t->payment_method === 'mpesa' ? $t->total : $t->mpesa_paid);

        $bonusesCount = TransactionItem::where('is_bonus', true)
            ->whereHas('transaction', fn ($q) => $q->where('served_at', '>=', $monthStart))
            ->count();

        return [
            Stat::make("This Month Cash", 'KES ' . number_format($monthCash, 2))
                ->description('Total physical cash this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('amber'),

            Stat::make("This Month M-Pesa", 'KES ' . number_format($monthMpesa, 2))
                ->description('Total M-Pesa mobile money this month')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('success'),

            Stat::make('This Month Bonuses Given', number_format($bonusesCount))
                ->description('Bonuses')
                ->descriptionIcon('heroicon-m-gift')
                ->color('warning'),
        ];
    }
}
