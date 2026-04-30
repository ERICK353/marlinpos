<?php

namespace App\Filament\Reception\Resources\CheckoutResource\Pages;

use App\Filament\Reception\Resources\CheckoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckouts extends ListRecords
{
    protected static string $resource = CheckoutResource::class;
    protected static ?string $title = 'Today\'s Transactions';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Checkout'),
        ];
    }
}
