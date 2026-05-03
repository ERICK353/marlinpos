<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class StaffPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 2;
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
            ])
            ->paginated(false);
    }
}
