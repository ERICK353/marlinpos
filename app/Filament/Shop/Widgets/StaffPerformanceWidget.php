<?php

namespace App\Filament\Shop\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class StaffPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 7;
    protected static ?string $heading = 'Staff Performance — This Month';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $monthStart = Carbon::now()->startOfMonth();

        return $table
            ->query(
                User::where('role', 'staff')
                    ->withCount([
                        'transactionItems as transactions_count' => fn ($q) =>
                            $q->whereHas('transaction', fn ($t) => $t->where('served_at', '>=', $monthStart)),
                    ])
                    ->withSum(
                        ['transactionItems as revenue_sum' => fn ($q) =>
                            $q->whereHas('transaction', fn ($t) => $t->where('served_at', '>=', $monthStart))],
                        'line_total'
                    )
                    ->withSum(
                        ['transactionItems as commission_sum' => fn ($q) =>
                            $q->whereHas('transaction', fn ($t) => $t->where('served_at', '>=', $monthStart))],
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
                    ->getStateUsing(function (User $record) {
                        $monthStart = Carbon::now()->startOfMonth();
                        return $record->transactionItems()
                            ->whereHas('transaction', fn ($t) => $t->where('served_at', '>=', $monthStart))
                            ->get()
                            ->sum(fn ($item) => $item->commission_amount ?? ($item->line_total * ($record->commission_rate ?? 40) / 100));
                    })
                    ->formatStateUsing(fn ($state) => 'KES ' . number_format($state ?? 0, 2))
                    ->badge()->color('warning'),
            ])
            ->paginated(false);
    }
}
