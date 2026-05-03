<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffLogResource\Pages;
use App\Models\StaffLog;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class StaffLogResource extends Resource
{
    protected static ?string $model = StaffLog::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-clock'; }
    public static function getNavigationGroup(): ?string { return 'Staff Management'; }
    public static function getNavigationLabel(): string { return 'Attendance'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Staff')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('log_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('logged_in_at')->time()->label('Time In'),
                Tables\Columns\TextColumn::make('logged_out_at')->time()->label('Time Out')->placeholder('Active'),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn (StaffLog $record) => $record->durationLabel() ?? 'Active')
                    ->badge()
                    ->color(fn (StaffLog $record) => $record->logged_out_at ? 'gray' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')->relationship('user', 'name'),
            ])
            ->defaultSort('log_date', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStaffLogs::route('/')];
    }

    public static function canCreate(): bool { return false; }
}
