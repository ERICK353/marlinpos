<?php

namespace App\Filament\Reception\Resources\NoteResource\Pages;

use App\Filament\Reception\Resources\NoteResource;
use Filament\Resources\Pages\ListRecords;

class ListNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
