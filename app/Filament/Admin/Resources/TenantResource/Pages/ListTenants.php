<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('register')
                ->label('Register New Shop')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => \App\Filament\Admin\Pages\RegisterTenant::getUrl()),
        ];
    }
}
