<?php

namespace App\Filament\Shop\Resources\TransactionResource\Pages;

use App\Filament\Shop\Resources\TransactionResource;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Transaction Details')->components([
                Infolists\Components\TextEntry::make('id')->label('TX #'),
                Infolists\Components\TextEntry::make('served_at')->dateTime(),
                Infolists\Components\TextEntry::make('customer.name')->label('Customer')->placeholder('Walk-in'),
                Infolists\Components\TextEntry::make('payment_method')->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Infolists\Components\TextEntry::make('mpesa_reference')->placeholder('—'),
                Infolists\Components\IconEntry::make('is_free_haircut')->boolean()->label('Free Haircut'),
                
                \Filament\Infolists\Components\TextEntry::make('_services_list')
                    ->label('Services & Staff')
                    ->getStateUsing(fn ($record) => $record->items->map(fn($i) => ($i->service->name ?? 'Service') . ' — handled by ' . ($i->staff->name ?? 'Unknown') . ' (KES ' . number_format($i->line_total) . ')'))
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('subtotal')->money('KES'),
                Infolists\Components\TextEntry::make('discount')->label('Loyalty Credit')->money('KES'),
                Infolists\Components\TextEntry::make('total')->money('KES')->size('lg')->weight('bold'),
                Infolists\Components\TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
