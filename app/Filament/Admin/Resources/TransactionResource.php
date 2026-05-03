<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

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
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('TX #')->sortable(),
                Tables\Columns\TextColumn::make('customer.phone')->label('Customer')->placeholder('Walk-in')->searchable(),
                Tables\Columns\TextColumn::make('items.staff.name')->label('Staff')->searchable(),
                Tables\Columns\TextColumn::make('items.service.name')->label('Services')->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn ($state) => $state === 'mpesa' ? 'success' : 'info')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('total')->money('KES')->sortable(),
                Tables\Columns\IconColumn::make('is_free_shave')->boolean()->label('Free'),
                Tables\Columns\TextColumn::make('served_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(['cash' => 'Cash', 'mpesa' => 'M-Pesa']),
                Tables\Filters\TernaryFilter::make('is_free_shave')->label('Free Shave'),
            ])
            ->defaultSort('served_at', 'desc')
            ->actions([\Filament\Actions\ViewAction::make()]);
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
}
