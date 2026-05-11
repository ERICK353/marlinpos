<?php

namespace App\Filament\Shop\Resources\ExpenseCategoryResource\Pages;

use App\Filament\Shop\Resources\ExpenseCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
