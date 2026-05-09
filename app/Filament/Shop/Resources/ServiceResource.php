<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-sparkles'; }
    public static function getNavigationGroup(): ?string { return 'Shop'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Service Details')->components([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(100),
                Forms\Components\TextInput::make('price')
                    ->numeric()->prefix('KES')->required()->minValue(0),
                Forms\Components\Textarea::make('description')
                    ->rows(2)->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()->default(0)->label('Sort Order'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Service Active')
                    ->default(true)
                    ->inline(false),
                Forms\Components\Toggle::make('is_haircut')
                    ->label('Loyalty Program (Haircut)')
                    ->helperText('Enable this to count this service towards the 9+1 loyalty program.')
                    ->default(false)
                    ->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price')->money('KES')->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\IconColumn::make('is_haircut')->boolean()->label('Loyalty'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
