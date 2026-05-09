<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-building-office-2'; }
    public static function getNavigationLabel(): string { return 'Tenants (Shops)'; }
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Tenant Details')->components([
                    Forms\Components\TextInput::make('id')
                        ->label('Tenant Name (ID)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('This will automatically generate the shop URL (e.g., yourshop.localhost) and database.')
                        ->alphaDash(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Workspace ID')->searchable(),
                Tables\Columns\TextColumn::make('domains.domain')
                    ->badge()
                    ->separator(',')
                    ->searchable()
                    ->label('Domains'),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
