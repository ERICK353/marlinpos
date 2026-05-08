<?php

namespace App\Filament\Shop\Resources\StaffLogResource\Pages;

use App\Filament\Shop\Resources\StaffLogResource;
use Filament\Resources\Pages\ListRecords;

class ListStaffLogs extends ListRecords
{
    protected static string $resource = StaffLogResource::class;

    protected function getHeaderActions(): array { return []; }
}
