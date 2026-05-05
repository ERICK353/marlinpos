<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;

class PaymentMethodChartWidget extends ChartWidget
{
    protected ?string $heading = 'Payment Methods (This Month)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $cashTotal = Trend::query(Transaction::where('payment_method', 'cash'))
            ->between($start, $end)
            ->perMonth()
            ->sum('total')
            ->first()
            ->aggregate ?? 0;

        $mpesaTotal = Trend::query(Transaction::where('payment_method', 'mpesa'))
            ->between($start, $end)
            ->perMonth()
            ->sum('total')
            ->first()
            ->aggregate ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Payment Methods',
                    'data' => [(float)$cashTotal, (float)$mpesaTotal],
                    'backgroundColor' => ['#fbbf24', '#22c55e'],
                ],
            ],
            'labels' => ['Cash', 'M-Pesa'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
