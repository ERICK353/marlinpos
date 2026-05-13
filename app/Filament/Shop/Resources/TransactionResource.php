<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-banknotes'; }
    public static function getNavigationGroup(): ?string { return 'Shop'; }
    public static function getNavigationSort(): ?int { return 3; }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed())
            ->columns([
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Customer')
                    ->placeholder('Walk-in')
                    ->searchable()
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->customer_id) {
                            $phone = 'Walk-in';
                        } else {
                            $phone = $state ?? 'No Phone';
                        }
                        $tag = $record->customer?->trashed() 
                            ? " <span style='background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; margin-left: 4px; border: 1px solid #fecaca;'>Deleted</span>" 
                            : "";
                        $trashed = $record->trashed() 
                            ? " <span style='background-color: #fef2f2; color: #ef4444; padding: 2px 6px; border-radius: 9999px; font-size: 9px; font-weight: 800; text-transform: uppercase; margin-left: 6px; border: 1px solid #fca5a5;'>Soft Deleted</span>" 
                            : "";
                        return "<span>{$phone}</span>{$tag}{$trashed}";
                    }),
                Tables\Columns\TextColumn::make('id')
                    ->label('Staff')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return $record->items->map(fn($item) => [
                            'name' => $item->staff?->name ?? 'Unknown',
                            'trashed' => (bool)$item->staff?->trashed()
                        ])
                        ->unique('name')
                        ->map(function ($staff) {
                            $tag = $staff['trashed'] 
                                ? " <span style='background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; margin-left: 4px; border: 1px solid #fecaca;'>Deleted</span>" 
                                : "";
                            return "<div style='margin-bottom: 2px;'>• {$staff['name']}{$tag}</div>";
                        })->implode('');
                    })
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('items.staff', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    )->toggleable(),
                Tables\Columns\TextColumn::make('services_unique')
                    ->label('Services')
                    ->getStateUsing(fn ($record) => $record->items->map(fn($i) => $i->service?->name ?? 'Unknown')->unique())
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('items.service', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        'wallet' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        'wallet' => '💰 WALLET',
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Gross Value')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Loyalty Discount')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                /*
                Tables\Columns\TextColumn::make('tip_amount')
                    ->label('Tip Amount')
                    ->money('KES')
                    ->state(fn ($record) => $record->items->sum('tip_amount'))
                    ->toggleable(isToggledHiddenByDefault: true),
                */
                Tables\Columns\TextColumn::make('total')->money('KES')->sortable(),
                Tables\Columns\IconColumn::make('is_free_haircut')->boolean()->label('Free')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('served_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        $time = $record->served_at->diffForHumans();
                        $tag = $record->updated_at->gt($record->created_at->addSeconds(5)) 
                            ? " <span style='background-color: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; margin-left: 4px; border: 1px solid #fde68a;'>Edited</span>" 
                            : "";
                        return "<span>{$time}</span>{$tag}";
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'wallet' => 'Wallet']),
                Tables\Filters\SelectFilter::make('staff')
                    ->relationship('items.staff', 'name')
                    ->label('Staff Member')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_free_haircut')->label('Free Haircut'),
            ])
            ->defaultSort('served_at', 'desc')
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\RestoreAction::make()
                    ->requiresConfirmation(),
                \Filament\Actions\ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Are you absolutely sure you want to permanently delete this transaction? This action cannot be undone and will permanently remove all related financial data.'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\RestoreBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you absolutely sure you want to permanently delete the selected transactions? This action cannot be undone and will permanently remove all related financial data.'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view'  => Pages\ViewTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canRestore($record): bool { return true; }
    public static function canForceDelete($record): bool { return true; }
}
