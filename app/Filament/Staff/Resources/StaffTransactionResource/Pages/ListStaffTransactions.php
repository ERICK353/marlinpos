<?php

namespace App\Filament\Staff\Resources\StaffTransactionResource\Pages;

use App\Filament\Staff\Resources\StaffTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListStaffTransactions extends ListRecords
{
    protected static string $resource = StaffTransactionResource::class;

    protected function getHeaderActions(): array { return []; }
}
