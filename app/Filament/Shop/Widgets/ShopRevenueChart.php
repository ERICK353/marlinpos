<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class ShopRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue (Current Year)';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Trend::model(Transaction::class)
            ->dateColumn('served_at')
            ->between(
                start: Carbon::now()->startOfYear(),
                end: Carbon::now()->endOfYear(),
            )
            ->perMonth()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue (KES)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgba(34, 197, 94, 1)',
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
