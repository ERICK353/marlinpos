<?php

namespace App\Filament\Reception\Resources;

use App\Filament\Reception\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-users'; }
    public static function getNavigationLabel(): string { return 'Customers'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Register Customer')->components([
                Forms\Components\TextInput::make('name')
                    ->label('Full Name')->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone Number')->tel()->required()
                    ->unique(ignoreRecord: true)->maxLength(20),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('loyalty_count')
                    ->label('Loyalty')
                    ->formatStateUsing(fn ($s) => "{$s}/9")
                    ->badge()
                    ->color(fn ($s) => $s >= 9 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('total_visits')->label('Visits'),
                Tables\Columns\TextColumn::make('enrolled_at')->date()->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view'   => Pages\ViewCustomer::route('/{record}'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
