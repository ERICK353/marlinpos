<?php

namespace App\Filament\Reception\Resources;

use App\Filament\Reception\Resources\NoteResource\Pages;
use App\Models\Note;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-pencil-square'; }
    public static function getNavigationGroup(): ?string { return null; }
    public static function getNavigationLabel(): string { return 'Notes'; }
    public static function getModelLabel(): string { return 'Note'; }
    public static function getPluralModelLabel(): string { return 'Notes'; }
    public static function getNavigationSort(): ?int { return 10; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Note Details')->components([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('body')
                    ->label('Content')
                    ->rows(5)
                    ->maxLength(5000)
                    ->columnSpanFull(),

                Forms\Components\ToggleButtons::make('priority')
                    ->options([
                        'low'    => 'Low',
                        'normal' => 'Normal',
                        'high'   => 'High',
                    ])
                    ->colors([
                        'low'    => 'gray',
                        'normal' => 'info',
                        'high'   => 'danger',
                    ])
                    ->icons([
                        'low'    => 'heroicon-o-arrow-down',
                        'normal' => 'heroicon-o-minus',
                        'high'   => 'heroicon-o-arrow-up',
                    ])
                    ->inline()
                    ->default('normal')
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high'   => 'danger',
                        'normal' => 'info',
                        'low'    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('body')
                    ->label('Content')
                    ->limit(60)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('By')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low'    => 'Low',
                        'normal' => 'Normal',
                        'high'   => 'High',
                    ]),
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
            'index'  => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit'   => Pages\EditNote::route('/{record}/edit'),
        ];
    }
}
