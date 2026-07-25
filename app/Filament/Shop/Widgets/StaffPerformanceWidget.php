<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class StaffPerformanceWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;
    protected static ?string $heading = 'Staff Performance';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : Carbon::now()->endOfMonth();

        return $table
            ->query(
                User::where('role', 'staff')
                    ->withCount([
                        'transactionItems as transactions_count' => fn ($q) =>
                            $q->whereHas('transaction', fn ($t) => $t->whereBetween('served_at', [$start, $end])),
                    ])
                    ->withSum(
                        ['transactionItems as revenue_sum' => fn ($q) =>
                            $q->whereHas('transaction', fn ($t) => $t->whereBetween('served_at', [$start, $end]))],
                        'line_total'
                    )
                    ->withSum(
                        ['transactionItems as commission_sum' => fn ($q) =>
                            $q->whereHas('transaction', fn ($t) => $t->whereBetween('served_at', [$start, $end]))],
                        'commission_amount'
                    )
                    ->orderByDesc('revenue_sum')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Staff'),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transactions')
                    ->badge()->color('info'),
                Tables\Columns\TextColumn::make('revenue_sum')
                    ->label('Revenue')
                    ->formatStateUsing(fn ($state) => 'KES ' . number_format($state ?? 0, 2))
                    ->badge()->color('success'),
                Tables\Columns\TextColumn::make('commission_sum')
                    ->label('Commission')
                    ->getStateUsing(function (User $record) use ($start, $end) {
                        return $record->transactionItems()
                            ->whereHas('transaction', fn ($t) => $t->whereBetween('served_at', [$start, $end]))
                            ->get()
                            ->sum(fn ($item) => $item->commission_amount ?? ($item->line_total * ($record->commission_rate ?? 40) / 100));
                    })
                    ->formatStateUsing(fn ($state) => 'KES ' . number_format($state ?? 0, 2))
                    ->badge()->color('warning'),
            ])
            ->paginated(false);
    }
}
