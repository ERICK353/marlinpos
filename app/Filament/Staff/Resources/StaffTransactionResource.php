<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffTransactionResource\Pages;
use App\Models\Transaction;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Infolists;

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
            ->modifyQueryUsing(fn ($query) => $query->whereHas('items', fn ($q) => $q->where('staff_user_id', auth()->id())))
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->customer_id) return 'Walk-in';
                        return $state ?? 'Unnamed';
                    }),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Services')
                    ->getStateUsing(fn (Transaction $record) =>
                        $record->items->map(fn ($i) => $i->service->name . ' (KES ' . number_format($i->line_total, 0) . ')')->join(', ')
                    ),
                Tables\Columns\TextColumn::make('subtotal')->label('Value')->money('KES'),
                Tables\Columns\IconColumn::make('is_free_haircut')->boolean()->label('Free'),
                Tables\Columns\IconColumn::make('is_bonus')->boolean()->label('Bonus'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        'wallet' => '💰 WALLET',
                        default => strtoupper($state ?? '—'),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        'wallet' => 'warning',
                        default => 'gray',
                    }),
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
            ->defaultSort('served_at', 'desc')
            ->actions([\Filament\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Transaction Details')->components([
                Infolists\Components\TextEntry::make('id')->label('TX #'),
                Infolists\Components\TextEntry::make('served_at')->dateTime(),
                Infolists\Components\TextEntry::make('customer.name')->label('Customer')->placeholder('Walk-in'),
                Infolists\Components\TextEntry::make('payment_method')->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        'wallet' => '💰 WALLET',
                        default => strtoupper($state ?? '—'),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        'wallet' => 'warning',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('is_free_haircut')->badge()->label('Reward / Bonus')
                    ->state(function ($record) {
                        if ($record->is_bonus) return 'BONUS HAIRCUT';
                        if ($record->is_free_haircut) return 'FREE HAIRCUT';
                        return 'Standard';
                    })
                    ->color(function ($state) {
                        if ($state === 'BONUS HAIRCUT') return 'success';
                        if ($state === 'FREE HAIRCUT') return 'warning';
                        return 'gray';
                    }),
                Infolists\Components\TextEntry::make('subtotal')->label('Gross Value')->money('KES'),
                Infolists\Components\TextEntry::make('items.service.name')
                    ->label('Services Performed')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStaffTransactions::route('/')];
    }

    public static function canCreate(): bool { return false; }
}
