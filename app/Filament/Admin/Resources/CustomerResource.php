<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-user-group'; }
    public static function getNavigationGroup(): ?string { return 'Shop'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make()->components([
                Forms\Components\TextInput::make('name')->maxLength(100),
                Forms\Components\TextInput::make('phone')->tel()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('loyalty_count')->numeric()->disabled()->label('Loyalty Progress'),
                Forms\Components\TextInput::make('total_visits')->numeric()->disabled(),
                Forms\Components\TextInput::make('free_shaves_used')->numeric()->disabled(),
                Forms\Components\DateTimePicker::make('enrolled_at')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Customer ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->placeholder('Walk-in'),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('loyalty_count')
                    ->label('Loyalty')
                    ->formatStateUsing(fn ($state) => "{$state} / 9")
                    ->badge()
                    ->color(fn ($state) => $state >= 9 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('total_visits')->label('Visits')->sortable(),
                Tables\Columns\TextColumn::make('free_shaves_used')->label('Free Shaves'),
                Tables\Columns\TextColumn::make('enrolled_at')->date()->sortable()->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([\Filament\Actions\ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view'  => Pages\ViewCustomer::route('/{record}'),
        ];
    }

    public static function canCreate(): bool { return false; }
}
