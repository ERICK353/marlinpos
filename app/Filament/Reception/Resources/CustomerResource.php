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
                Forms\Components\TextInput::make('loyalty_count')
                    ->label('Starting Haircuts')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(9)
                    ->helperText('Number of haircuts they already have (0-9).'),
                Forms\Components\TextInput::make('credit_balance')
                    ->label('Wallet Balance')
                    ->prefix('KES')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record) => filled($record)),
            ])->columns(2),
        ]);
    }
 
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withTrashed())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->placeholder('Unnamed')
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        $name = $state ?? 'Unnamed';
                        if ($record->trashed()) {
                            return "{$name} <span style='background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; border: 1px solid #fecaca; margin-left: 8px;'>Deleted</span>";
                        }
                        return $name;
                    }),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('loyalty_count')
                    ->label('Haircut Count')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return '—';
                        $percent = min(100, round(($state / 9) * 100));
                        $color = '#10b981';
                        
                        return "
                            <div class='flex items-center gap-3' style='min-width: 140px;'>
                                <div class='flex-1 h-2 bg-gray-200 rounded-full overflow-hidden shadow-inner'>
                                    <div class='h-full transition-all duration-500' style='width: {$percent}%; background-color: {$color};'></div>
                                </div>
                                <span class='text-xs font-bold' style='color: #374151'>{$state}/9</span>
                            </div>
                        ";
                    }),
                Tables\Columns\TextColumn::make('total_visits')->label('Visits'),
                Tables\Columns\TextColumn::make('credit_balance')
                    ->label('Wallet')
                    ->money('KES')
                    ->badge()
                    ->color(fn ($state) => (float)$state > 0 ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('enrolled_at')->date()->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
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
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view'   => Pages\ViewCustomer::route('/{record}'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
