<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffTransactionResource\Pages;
use App\Models\Transaction;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class StaffTransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-banknotes'; }
    public static function getNavigationLabel(): string { return 'My Transactions'; }
    public static function getModelLabel(): string { return 'Transaction'; }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('staff_user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('TX #')->sortable(),
                Tables\Columns\TextColumn::make('customer.phone')->label('Customer')->placeholder('Walk-in'),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Services')
                    ->getStateUsing(fn (Transaction $record) =>
                        $record->items->map(fn ($i) => $i->service->name)->join(', ')
                    ),
                Tables\Columns\TextColumn::make('total')->money('KES'),
                Tables\Columns\IconColumn::make('is_free_shave')->boolean()->label('Free'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($s) => strtoupper($s)),
                Tables\Columns\TextColumn::make('served_at')->dateTime()->sortable(),
            ])
            ->defaultSort('served_at', 'desc')
            ->actions([\Filament\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStaffTransactions::route('/')];
    }

    public static function canCreate(): bool { return false; }
}
