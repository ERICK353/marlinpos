<?php

namespace App\Filament\Admin\Resources\StaffLogResource\Pages;

use App\Filament\Admin\Resources\StaffLogResource;
use Filament\Resources\Pages\ListRecords;

class ListStaffLogs extends ListRecords
{
    protected static string $resource = StaffLogResource::class;

    protected function getHeaderActions(): array { return []; }
}
