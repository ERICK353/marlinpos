<?php

namespace App\Filament\Reception\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Service Distribution (This Month)';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Get counts of each service performed across the entire shop this month
        $data = TransactionItem::query()
            ->join('services', 'transaction_items.service_id', '=', 'services.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->select('services.name', DB::raw('count(*) as count'))
            ->whereBetween('transactions.served_at', [$startOfMonth, $endOfMonth])
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Services',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#10b981', // emerald
                        '#3b82f6', // blue
                        '#f59e0b', // amber
                        '#ef4444', // red
                        '#8b5cf6', // violet
                        '#ec4899', // pink
                        '#06b6d4', // cyan
                        '#f97316', // orange
                    ],
                    'hoverOffset' => 4,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
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
