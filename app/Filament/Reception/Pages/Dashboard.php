<?php

namespace App\Filament\Reception\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('startDate')
                    ->label('Start Date'),
                DatePicker::make('endDate')
                    ->label('End Date'),
            ])
            ->columns(2);
    }
}
