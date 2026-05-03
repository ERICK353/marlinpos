<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class ShopRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Shop Revenue (Last 7 Days)';
    protected ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(Transaction::class)
            ->dateColumn('served_at')
            ->between(
                start: Carbon::today()->subDays(6),
                end: Carbon::today(),
            )
            ->perDay()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (KES)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
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
