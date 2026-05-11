<?php

namespace App\Filament\Reception\Resources\ExpenseResource\Pages;

use App\Filament\Reception\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;
}
