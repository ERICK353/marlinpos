<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-users'; }
    public static function getNavigationGroup(): ?string { return 'Staff Management'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('email', '!=', 'developer@marlin.local')
            ->withTrashed();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Account Details')->components([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()->maxLength(20),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin'     => 'Admin / Owner',
                        'staff'     => 'Staff',
                        'reception' => 'Reception / Operator',
                    ])
                    ->required(),
                Forms\Components\Select::make('gender')
                    ->options([
                        'male'   => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                Forms\Components\TextInput::make('commission_rate')
                    ->label('Commission Rate (%)')
                    ->numeric()
                    ->default(40)
                    ->suffix('%')
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Default is 40%. This is the percentage of service price the staff receives.'),
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
                    ->sortable()
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        $name = $state ?? 'Unnamed';
                        if ($record->trashed()) {
                            return "{$name} <span style='background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; border: 1px solid #fecaca; margin-left: 8px;'>Deleted</span>";
                        }
                        return $name;
                    }),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->placeholder('—'),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'male'   => 'info',
                        'female' => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'admin'     => 'warning',
                        'staff'     => 'info',
                        'reception' => 'success',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin'     => 'Admin',
                        'staff'     => 'Staff',
                        'reception' => 'Reception',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(['admin' => 'Admin', 'staff' => 'Staff', 'reception' => 'Reception']),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
                \Filament\Actions\ForceDeleteAction::make(),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
