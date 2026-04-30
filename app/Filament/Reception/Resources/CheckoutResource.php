<?php

namespace App\Filament\Reception\Resources;

use App\Filament\Reception\Resources\CheckoutResource\Pages;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class CheckoutResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-shopping-cart'; }
    public static function getNavigationLabel(): string { return 'Checkout'; }
    public static function getModelLabel(): string { return 'Transaction'; }
    public static function getPluralModelLabel(): string { return 'Transactions'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Hidden state fields (virtual, not saved directly) ─────────────
            Forms\Components\Hidden::make('customer_id'),
            Forms\Components\Hidden::make('_loyalty_eligible')->default(false)->dehydrated(false),
            Forms\Components\Hidden::make('_customer_name')->dehydrated(false),
            Forms\Components\Hidden::make('_loyalty_count')->default(0)->dehydrated(false),

            // ── Customer ──────────────────────────────────────────────────────
            \Filament\Schemas\Components\Section::make('Customer')
                ->icon('heroicon-o-user')
                ->components([
                    Forms\Components\Toggle::make('is_walk_in')
                        ->label('Walk-in Customer (No loyalty tracking)')
                        ->default(false)
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            if ($state) {
                                $set('customer_phone', null);
                                $set('customer_id', null);
                                $set('_customer_name', null);
                                $set('_loyalty_count', 0);
                                $set('_loyalty_eligible', false);
                                $set('enroll_new', false);

                                // Recalculate without discount
                                $servicesData = $get('services') ?? [];
                                $serviceIds = array_column($servicesData, 'service_id');
                                if (! empty($serviceIds)) {
                                    $subtotal = Service::whereIn('id', $serviceIds)->sum('price');
                                    $set('subtotal', $subtotal);
                                    $set('discount', 0);
                                    $set('total', $subtotal);
                                }
                            }
                        }),

                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Phone Number')
                        ->tel()
                        ->placeholder('Search by phone…')
                        ->required(fn (Get $get) => ! $get('is_walk_in'))
                        ->visible(fn (Get $get) => ! $get('is_walk_in'))
                        ->live(onBlur: true)
                        ->dehydrated(false)
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            if (blank($state)) {
                                $set('customer_id', null);
                                $set('_customer_name', null);
                                $set('_loyalty_count', 0);
                                $set('_loyalty_eligible', false);
                                return;
                            }
                            $customer = Customer::where('phone', $state)->first();
                            if ($customer) {
                                $eligible = $customer->isEligibleForFreeShave();
                                $set('customer_id', $customer->id);
                                $set('_customer_name', $customer->name ?? 'Enrolled member');
                                $set('_loyalty_count', $customer->loyalty_count);
                                $set('_loyalty_eligible', $eligible);

                                // Recalculate total with new loyalty status
                                $servicesData = $get('services') ?? [];
                                $serviceIds = array_column($servicesData, 'service_id');
                                if (! empty($serviceIds)) {
                                    $subtotal = Service::whereIn('id', $serviceIds)->sum('price');
                                    $discount = $eligible ? $subtotal : 0;
                                    $set('subtotal', $subtotal);
                                    $set('discount', $discount);
                                    $set('total', $subtotal - $discount);
                                }
                            } else {
                                $set('customer_id', null);
                                $set('_customer_name', null);
                                $set('_loyalty_count', 0);
                                $set('_loyalty_eligible', false);

                                // Recalculate without discount
                                $servicesData = $get('services') ?? [];
                                $serviceIds = array_column($servicesData, 'service_id');
                                if (! empty($serviceIds)) {
                                    $subtotal = Service::whereIn('id', $serviceIds)->sum('price');
                                    $set('subtotal', $subtotal);
                                    $set('discount', 0);
                                    $set('total', $subtotal);
                                }
                            }
                        }),

                    Forms\Components\Placeholder::make('_customer_info')
                        ->label('Loyalty Status')
                        ->visible(fn (Get $get) => filled($get('customer_id')))
                        ->content(function (Get $get) {
                            $count    = $get('_loyalty_count') ?? 0;
                            $eligible = (bool) $get('_loyalty_eligible');
                            $name     = $get('_customer_name') ?? 'Customer';
                            if ($eligible) {
                                return "⭐ {$name} — FREE SHAVE ELIGIBLE! (9/9 shaves completed)";
                            }
                            return "{$name} — Loyalty progress: {$count}/9 shaves";
                        }),

                    // New enrollment toggle
                    Forms\Components\Toggle::make('enroll_new')
                        ->label('Enroll as new loyalty member')
                        ->live()
                        ->dehydrated(false)
                        ->visible(fn (Get $get) => blank($get('customer_id')) && filled($get('customer_phone'))),

                    Forms\Components\TextInput::make('new_customer_name')
                        ->label('Customer Name (optional)')
                        ->maxLength(100)
                        ->dehydrated(false)
                        ->visible(fn (Get $get) => (bool) $get('enroll_new')),
                ])
                ->columns(2),

            // ── Services ──────────────────────────────────────────────────────
            \Filament\Schemas\Components\Section::make('Services')
                ->icon('heroicon-o-scissors')
                ->components([
                    Forms\Components\Repeater::make('services')
                        ->label('Services Performed')
                        ->schema([
                            Forms\Components\Select::make('service_id')
                                ->label('Service')
                                ->options(fn () => Service::active()->pluck('name', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get) {
                                    // Trigger main recalculation via live
                                }),

                            Forms\Components\Select::make('staff_user_id')
                                ->label('Staff Member')
                                ->options(fn () => User::where('role', 'staff')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (?array $state, Set $set, Get $get) {
                            $serviceIds = array_column($state ?? [], 'service_id');
                            $subtotal = Service::whereIn('id', $serviceIds)->sum('price');
                            $eligible = (bool) $get('_loyalty_eligible');
                            $discount = $eligible ? $subtotal : 0;
                            $set('subtotal', $subtotal);
                            $set('discount', $discount);
                            $set('total', $subtotal - $discount);
                        })
                        ->required(),
                ]),

            // ── Payment ───────────────────────────────────────────────────────
            \Filament\Schemas\Components\Section::make('Payment')
                ->icon('heroicon-o-banknotes')
                ->components([
                    Forms\Components\Radio::make('payment_method')
                        ->label('Payment Method')
                        ->options(['cash' => '💵 Cash', 'mpesa' => '📱 M-Pesa'])
                        ->required()
                        ->live()
                        ->inline(),

                    Forms\Components\TextInput::make('mpesa_reference')
                        ->label('M-Pesa Reference')
                        ->placeholder('e.g. QGH7R3KLMN')
                        ->visible(fn (Get $get) => $get('payment_method') === 'mpesa')
                        ->requiredIf('payment_method', 'mpesa')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')->prefix('KES')
                        ->numeric()->disabled()->dehydrated()->default(0),

                    Forms\Components\TextInput::make('discount')
                        ->label('Loyalty Discount')->prefix('KES')
                        ->numeric()->disabled()->dehydrated()->default(0),

                    Forms\Components\TextInput::make('total')
                        ->label('TOTAL')->prefix('KES')
                        ->numeric()->disabled()->dehydrated()->default(0),

                    Forms\Components\Textarea::make('notes')
                        ->rows(2)->placeholder('Optional notes…')->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) =>
                $query->where('reception_user_id', auth()->id())
                      ->orderByDesc('served_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('TX #'),
                Tables\Columns\TextColumn::make('customer.phone')->label('Customer')->placeholder('Walk-in'),
                Tables\Columns\TextColumn::make('items.staff.name')
                    ->label('Staff')
                    ->listWithLineBreaks()
                    ->bulleted(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($s) => strtoupper($s))
                    ->color(fn ($s) => $s === 'mpesa' ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('total')->money('KES'),
                Tables\Columns\IconColumn::make('is_free_shave')->boolean()->label('Free'),
                Tables\Columns\TextColumn::make('served_at')->dateTime()->since(),
            ])
            ->actions([\Filament\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCheckouts::route('/'),
            'create' => Pages\CreateCheckout::route('/create'),
        ];
    }

    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}
