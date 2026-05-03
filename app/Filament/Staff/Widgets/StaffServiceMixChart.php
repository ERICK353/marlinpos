<?php

namespace App\Filament\Staff\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StaffServiceMixChart extends ChartWidget
{
    protected ?string $heading = 'Service Distribution';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $userId = auth()->id();

        // Get counts of each service performed by this staff member
        $data = TransactionItem::query()
            ->join('services', 'transaction_items.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'))
            ->where('transaction_items.staff_user_id', $userId)
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->limit(5) // Top 5 services
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Services Performed',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#10b981', // emerald
                        '#3b82f6', // blue
                        '#f59e0b', // amber
                        '#ef4444', // red
                        '#8b5cf6', // violet
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
