<?php

namespace App\Filament\Staff\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class StaffYearlyRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue Trend (Current Year)';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $userId = auth()->id();

        $data = Trend::query(
            TransactionItem::query()
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transaction_items.staff_user_id', $userId)
        )
            ->dateColumn('transactions.served_at')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('transaction_items.line_total');

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Revenue (KES)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)', // Blue theme
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgba(59, 130, 246, 1)',
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'none',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
