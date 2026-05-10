<?php

namespace App\Filament\Staff\Widgets;

use App\Models\TransactionItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StaffRecentServicesWidget extends BaseWidget
{
    protected static ?string $heading = 'My Recent Services';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItem::where('staff_user_id', auth()->id())
                    ->with(['transaction.customer', 'service'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction.served_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service'),
                Tables\Columns\TextColumn::make('transaction.customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                Tables\Columns\TextColumn::make('line_total')
                    ->label('Value')
                    ->money('KES'),
                Tables\Columns\TextColumn::make('transaction.payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        default => strtoupper($state ?? '—'),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
