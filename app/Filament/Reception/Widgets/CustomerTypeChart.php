<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CustomerTypeChart extends ChartWidget
{
    protected ?string $heading = 'New vs Returning Customers (This Month)';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $returningCount = Transaction::whereNotNull('customer_id')
            ->where('served_at', '>=', $startOfMonth)
            ->count();

        $newCount = Transaction::whereNull('customer_id')
            ->where('served_at', '>=', $startOfMonth)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => [$newCount, $returningCount],
                    'backgroundColor' => [
                        '#10b981', // Emerald (New/Walk-in)
                        '#3b82f6', // Blue (Returning)
                    ],
                    'hoverOffset' => 4,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => ['Walk-in / New', 'Returning Member'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1000,
            ],
            'cutout' => '70%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'responsive' => true,
            'elements' => [
                'arc' => [
                    'borderRadius' => 10,
                    'borderWidth' => 0,
                ],
            ],
        ];
    }
}
