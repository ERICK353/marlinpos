<?php

namespace App\Filament\Reception\Resources\CustomerResource\Pages;

use App\Filament\Reception\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['enrolled_at'] = Carbon::now();
        return $data;
    }
}
