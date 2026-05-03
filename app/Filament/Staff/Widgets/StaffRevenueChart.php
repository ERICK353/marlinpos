<?php

namespace App\Filament\Staff\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class StaffRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Trend (Last 7 Days)';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $userId = auth()->id();

        // Use Trend package to aggregate line_total from transaction_items
        // joined with transactions to get the served_at date.
        $data = Trend::query(
            TransactionItem::query()
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transaction_items.staff_user_id', $userId)
        )
            ->dateColumn('transactions.served_at')
            ->between(
                start: Carbon::today()->subDays(6),
                end: Carbon::today(),
            )
            ->perDay()
            ->sum('transaction_items.line_total');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (KES)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('D, M j'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
