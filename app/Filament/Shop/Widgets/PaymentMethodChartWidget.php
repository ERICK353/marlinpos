<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PaymentMethodChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    protected ?string $heading = 'Payment Methods (This Month)';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        $cashTotal = Transaction::where('payment_method', 'cash')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $mpesaTotal = Transaction::where('payment_method', 'mpesa')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Payment Methods',
                    'data' => [(float)$cashTotal, (float)$mpesaTotal],
                    'backgroundColor' => ['#fbbf24', '#22c55e'],
                    'hoverOffset'     => 4,
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => ['Cash', 'M-Pesa'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1000,
            ],
            'cutout'  => '70%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels'   => [
                        'usePointStyle' => true,
                        'pointStyle'    => 'circle',
                    ],
                ],
            ],
            'responsive' => true,
            'elements'   => [
                'arc' => [
                    'borderRadius' => 10,
                    'borderWidth'  => 0,
                ],
            ],
        ];
    }
}
