<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PaymentMethodChart extends ChartWidget
{
    protected ?string $heading = 'Payment Methods';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Transaction::query()
            ->select('payment_method', DB::raw('count(*) as count'))
            ->groupBy('payment_method')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // blue (cash)
                        '#10b981', // emerald (mpesa)
                    ],
                    'hoverOffset'     => 4,
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => $data->pluck('payment_method')->map(fn ($m) => strtoupper($m))->toArray(),
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
