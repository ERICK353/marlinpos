<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class CustomerTypeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'New vs Returning Customers';
    protected static ?int $sort = 7;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : Carbon::now()->endOfMonth();

        $returningCount = Transaction::whereNotNull('customer_id')
            ->whereBetween('served_at', [$start, $end])
            ->count();

        $newCount = Transaction::whereNull('customer_id')
            ->whereBetween('served_at', [$start, $end])
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
