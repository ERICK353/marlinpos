<?php

namespace App\Filament\Reception\Resources\CustomerResource\Pages;

use App\Filament\Reception\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Register Customer')];
    }
}
