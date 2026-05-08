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
                    ->label('Haircuts Completed (Initial)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(9)
                    ->helperText('Initial count of haircuts for existing customers (0-9).'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->placeholder('Unnamed'),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('loyalty_count')
                    ->label('Haircut Progress')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $percent = min(100, round(($state / 9) * 100));
                        $color = $state >= 9 ? '#10b981' : '#10b981'; // Using emerald for both for a clean look, or different for contrast
                        $bg = $state >= 9 ? 'bg-emerald-100' : 'bg-gray-100';
                        $bar = $state >= 9 ? 'bg-emerald-500' : 'bg-primary-500';
                        
                        return "
                            <div class='flex items-center gap-3' style='min-width: 140px;'>
                                <div class='flex-1 h-2 bg-gray-200 rounded-full overflow-hidden shadow-inner'>
                                    <div class='h-full transition-all duration-500' style='width: {$percent}%; background-color: " . ($state >= 9 ? '#10b981' : '#10b981') . ";'></div>
                                </div>
                                <span class='text-xs font-bold' style='color: " . ($state >= 9 ? '#059669' : '#374151') . "'>{$state}/9</span>
                            </div>
                        ";
                    }),
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
