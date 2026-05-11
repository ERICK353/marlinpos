<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\ExpenseCategoryResource\Pages;
use App\Models\ExpenseCategory;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-tag'; }
    public static function getNavigationGroup(): ?string { return 'Finance'; }
    public static function getNavigationLabel(): string { return 'Expense Categories'; }
    public static function getModelLabel(): string { return 'Category'; }
    public static function getPluralModelLabel(): string { return 'Expense Categories'; }
    public static function getNavigationSort(): ?int { return 3; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Category Details')->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->trashed()) {
                            return "{$state} <span style='background-color:#fee2e2;color:#b91c1c;padding:1px 5px;border-radius:9999px;font-size:8px;font-weight:800;text-transform:uppercase;margin-left:4px;border:1px solid #fecaca;'>Deleted</span>";
                        }
                        return $state;
                    }),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->since()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->visible(fn ($record) => ! $record->trashed()),
                \Filament\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn ($record) => $record->trashed())
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->restore()),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn ($record) => ! $record->trashed()),
                \Filament\Actions\ForceDeleteAction::make()
                    ->visible(fn ($record) => $record->trashed()),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenseCategories::route('/'),
            'create' => Pages\CreateExpenseCategory::route('/create'),
            'edit'   => Pages\EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}
