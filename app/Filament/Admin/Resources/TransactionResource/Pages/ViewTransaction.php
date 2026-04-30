<?php

namespace App\Filament\Admin\Resources\TransactionResource\Pages;

use App\Filament\Admin\Resources\TransactionResource;
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
                Infolists\Components\TextEntry::make('customer.phone')->label('Customer Phone')->placeholder('Walk-in'),
                Infolists\Components\TextEntry::make('customer.name')->label('Customer Name')->placeholder('—'),
                Infolists\Components\TextEntry::make('staff.name')->label('Barber'),
                Infolists\Components\TextEntry::make('reception.name')->label('Reception'),
                Infolists\Components\TextEntry::make('payment_method')->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Infolists\Components\TextEntry::make('mpesa_reference')->placeholder('—'),
                Infolists\Components\IconEntry::make('is_free_shave')->boolean()->label('Free Shave'),
                Infolists\Components\TextEntry::make('subtotal')->money('KES'),
                Infolists\Components\TextEntry::make('discount')->money('KES'),
                Infolists\Components\TextEntry::make('total')->money('KES')->size('lg')->weight('bold'),
                Infolists\Components\TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
            ])->columns(3),
        ]);
    }
}
