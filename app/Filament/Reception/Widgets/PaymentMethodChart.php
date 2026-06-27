<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class PaymentMethodChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Payment Methods';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 10;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $data = $query
            ->select('payment_method', DB::raw('sum(total) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->pluck('total_amount')->toArray(),
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
