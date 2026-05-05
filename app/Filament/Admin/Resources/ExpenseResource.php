<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-banknotes'; }
    public static function getNavigationGroup(): ?string { return 'Finance'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Expense Details')->components([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->options([
                        'Rent'      => 'Rent',
                        'Utilities' => 'Utilities',
                        'Supplies'  => 'Supplies',
                        'Salaries'  => 'Salaries',
                        'Marketing' => 'Marketing',
                        'Other'     => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('KES')
                    ->required(),
                Forms\Components\DateTimePicker::make('spent_at')
                    ->default(now())
                    ->required(),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('spent_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('KES')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recorded By')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Rent'      => 'Rent',
                        'Utilities' => 'Utilities',
                        'Supplies'  => 'Supplies',
                        'Salaries'  => 'Salaries',
                        'Marketing' => 'Marketing',
                        'Other'     => 'Other',
                    ]),
                Tables\Filters\Filter::make('spent_at')
                    ->form([
                        Forms\Components\DatePicker::make('spent_from'),
                        Forms\Components\DatePicker::make('spent_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['spent_from'], fn ($query, $date) => $query->whereDate('spent_at', '>=', $date))
                            ->when($data['spent_until'], fn ($query, $date) => $query->whereDate('spent_at', '<=', $date));
                    })
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
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
