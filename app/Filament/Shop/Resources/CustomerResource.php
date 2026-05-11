<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\CustomerResource\Pages;
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
                Forms\Components\TextInput::make('loyalty_count')->numeric()->disabled()->label('Haircut Progress'),
                Forms\Components\TextInput::make('total_visits')->numeric()->disabled(),
                Forms\Components\TextInput::make('free_haircuts_used')->numeric()->disabled()->label('Free Haircuts'),
                Forms\Components\DateTimePicker::make('enrolled_at')->disabled(),
                Forms\Components\TextInput::make('credit_balance')
                    ->label('Wallet Balance')
                    ->prefix('KES')
                    ->numeric()
                    ->default(0)
                    ->helperText('Customer stored credit balance.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Customer ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->placeholder('Unnamed'),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('loyalty_count')
                    ->label('Haircuts')
                    ->formatStateUsing(fn ($state) => "{$state} / 9")
                    ->badge()
                    ->color(fn ($state) => $state >= 9 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('total_visits')->label('Visits')->sortable(),
                Tables\Columns\TextColumn::make('free_haircuts_used')->label('Free Haircuts'),
                Tables\Columns\TextColumn::make('enrolled_at')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('credit_balance')
                    ->label('Wallet')
                    ->money('KES')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'success' : 'gray'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCustomers::route('/'),
            'view'  => Pages\ViewCustomer::route('/{record}'),
            'edit'  => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool { return true; }
    public static function canEdit($record): bool { return true; }
    public static function canDelete($record): bool { return true; }
}
