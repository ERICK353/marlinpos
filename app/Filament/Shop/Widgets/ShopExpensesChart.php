<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class ShopExpensesChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Expenses (Current Year)';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Trend::model(Expense::class)
            ->dateColumn('spent_at')
            ->between(
                start: Carbon::now()->startOfYear(),
                end: Carbon::now()->endOfYear(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Total Expenses (KES)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => 'rgba(244, 63, 94, 0.8)', // Rose/Red for expenses
                    'borderColor' => 'rgb(244, 63, 94)',
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgba(244, 63, 94, 1)',
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
        ];
    }
}
