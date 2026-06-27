<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\ExpenseCategory;
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
                    ->options(fn () => ExpenseCategory::options())
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('KES')
                    ->required()
                    ->minValue(0),
                Forms\Components\ToggleButtons::make('status')
                    ->options([
                        'paid'   => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->colors([
                        'paid'   => 'success',
                        'unpaid' => 'danger',
                    ])
                    ->icons([
                        'paid'   => 'heroicon-o-check-circle',
                        'unpaid' => 'heroicon-o-x-circle',
                    ])
                    ->inline()
                    ->default('paid')
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
                    ->color('info')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $cat = ExpenseCategory::withTrashed()->where('name', $state)->first();
                        if ($cat && $cat->trashed()) {
                            return "{$state} <span style='background-color:#fee2e2;color:#b91c1c;padding:1px 4px;border-radius:9999px;font-size:8px;font-weight:800;text-transform:uppercase;margin-left:3px;border:1px solid #fecaca;'>Deleted</span>";
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('KES')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Summary')
                            ->formatStateUsing(function ($state, $query) {
                                $paid = $query->clone()->where('status', 'paid')->sum('amount');
                                $unpaid = $query->clone()->where('status', 'unpaid')->sum('amount');
                                return "Paid: " . number_format($paid) . " | Unpaid: " . number_format($unpaid) . " | Total: " . number_format($state);
                            })
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'   => 'success',
                        'unpaid' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recorded By')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn () => ExpenseCategory::withTrashed()->orderBy('name')->pluck('name', 'name')->toArray()),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid'   => 'Paid',
                        'unpaid' => 'Unpaid',
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
                \Filament\Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Paid')
                    ->modalDescription('Are you sure you want to mark this expense as paid?')
                    ->action(fn ($record) => $record->update(['status' => 'paid'])),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulk_mark_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'paid'])),
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
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
