<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class ExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Shop Expenses (Monthly — Current Year)';
    protected ?string $maxHeight = '300px';
    protected string $color = 'danger';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $data = Trend::model(Expense::class)
            ->between(
                start: Carbon::now()->startOfYear(),
                end: Carbon::now()->endOfYear(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Expenses (KES)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgba(239, 68, 68, 1)',
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
