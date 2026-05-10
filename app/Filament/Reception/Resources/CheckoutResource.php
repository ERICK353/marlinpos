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
                                $itemsData = $get('items') ?? [];
                                $serviceIds = array_column($itemsData, 'service_id');
                                if (! empty($serviceIds)) {
                                    $subtotal = Service::whereIn('id', $serviceIds)->sum('price');
                                    $set('subtotal', $subtotal);
                                    $set('discount', 0);
                                    $set('total', $subtotal);
                                }
                            }
                        }),

                    Forms\Components\Select::make('customer_id')
                        ->label('Select Customer')
                        ->placeholder('Search by name or phone…')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => 
                            Customer::where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($c) => [$c->id => ($c->name ?? 'Unnamed') . " ({$c->phone})"])
                        )
                        ->getOptionLabelUsing(fn ($value) => Customer::find($value) ? (Customer::find($value)->name ?? 'Unnamed') . ' (' . Customer::find($value)->phone . ')' : null)
                        ->required(fn (Get $get) => ! $get('is_walk_in') && ! $get('enroll_new'))
                        ->visible(fn (Get $get) => ! $get('is_walk_in') && ! $get('enroll_new'))
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if (! $state) {
                                $set('_customer_name', null);
                                $set('_loyalty_count', 0);
                                $set('_loyalty_eligible', false);
                                return;
                            }

                            $customer = Customer::find($state);
                            if ($customer) {
                                $eligible = $customer->isEligibleForFreeHaircut();
                                $set('_customer_name', $customer->name ?? 'Enrolled member');
                                $set('_loyalty_count', $customer->loyalty_count);
                                $set('_loyalty_eligible', $eligible);

                                // Recalculate totals
                                $itemsData = $get('items') ?? [];
                                $serviceIds = array_column($itemsData, 'service_id');
                                if (! empty($serviceIds)) {
                                    $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');
                                    $subtotal = 0;
                                    $maxHaircutPrice = 0;
                                    $hasHaircut = false;

                                    foreach ($itemsData as $item) {
                                        if ($serviceId = $item['service_id'] ?? null) {
                                            $service = $services[$serviceId] ?? null;
                                            if ($service) {
                                                $subtotal += $service->price;
                                                if ($service->is_haircut) {
                                                    $hasHaircut = true;
                                                    if ($service->price > $maxHaircutPrice) {
                                                        $maxHaircutPrice = $service->price;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $discount = 0;
                                    if ($eligible && $hasHaircut) {
                                        $subtotal = $subtotal - $maxHaircutPrice + 250;
                                        $discount = 250;
                                    }
                                    
                                    $set('subtotal', $subtotal);
                                    $set('discount', $discount);
                                    $set('total', max(0, $subtotal - $discount));
                                }
                            }
                        }),

                    // Keep phone input for manual entry/enrollment if not found in dropdown
                    Forms\Components\TextInput::make('customer_phone')
                        ->label('New Customer Phone')
                        ->tel()
                        ->placeholder('Enter phone to enroll…')
                        ->visible(fn (Get $get) => ! $get('is_walk_in') && blank($get('customer_id')))
                        ->live(onBlur: true)
                        ->dehydrated(false)
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            if (blank($state)) return;
                            
                            // If phone already exists, switch to that customer automatically
                            $customer = Customer::where('phone', $state)->first();
                            if ($customer) {
                                $set('customer_id', $customer->id);
                                $set('_customer_name', $customer->name ?? 'Enrolled member');
                                $set('_loyalty_count', $customer->loyalty_count);
                                $set('_loyalty_eligible', $customer->isEligibleForFreeHaircut());
                                $set('enroll_new', false);
                            }
                        }),

                    Forms\Components\Placeholder::make('_customer_info')
                        ->label('Haircut Loyalty Status')
                        ->visible(fn (Get $get) => filled($get('customer_id')))
                        ->content(function (Get $get) {
                            $count    = $get('_loyalty_count') ?? 0;
                            $eligible = (bool) $get('_loyalty_eligible');
                            $name     = $get('_customer_name') ?? 'Customer';
                            if ($eligible) {
                                return "⭐ {$name} — FREE HAIRCUT ELIGIBLE! (9/9 haircuts completed)";
                            }
                            return "{$name} — Haircut progress: {$count}/9 haircuts";
                        })
                        ->columnSpanFull(),

                    // New enrollment toggle
                    Forms\Components\Toggle::make('enroll_new')
                        ->label('Enroll in Haircut Loyalty Program')
                        ->live()
                        ->dehydrated(false)
                        ->visible(fn (Get $get) => blank($get('customer_id')) && filled($get('customer_phone'))),

                    Forms\Components\TextInput::make('new_customer_name')
                        ->label('Customer Name (optional)')
                        ->maxLength(100)
                        ->dehydrated(false)
                        ->visible(fn (Get $get) => (bool) $get('enroll_new')),

                    Forms\Components\TextInput::make('initial_loyalty_count')
                        ->label('Initial Haircut Count')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(9)
                        ->helperText('Previous haircuts before enrollment (0-9).')
                        ->dehydrated(false)
                        ->visible(fn (Get $get) => (bool) $get('enroll_new')),
                ])
                ->columns(2),

            // ── Services ──────────────────────────────────────────────────────
            \Filament\Schemas\Components\Section::make('Services')
                ->icon('heroicon-o-scissors')
                ->components([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
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

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Price')
                                ->numeric()
                                ->prefix('KES')
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn ($record) => filled($record)),

                            Forms\Components\Hidden::make('line_total')
                                ->dehydrated(),
                        ])
                        ->columns(fn ($record) => filled($record) ? 3 : 2)
                        ->defaultItems(1)
                        ->live()
                        ->afterStateUpdated(function (?array $state, Set $set, Get $get) {
                            $serviceIds = array_column($state ?? [], 'service_id');
                            if (empty($serviceIds)) {
                                $set('subtotal', 0);
                                $set('discount', 0);
                                $set('total', 0);
                                return;
                            }
                            
                            $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');
                            $subtotal = 0;
                            $maxHaircutPrice = 0;
                            $hasHaircut = false;

                            foreach ($state as $item) {
                                if ($serviceId = $item['service_id'] ?? null) {
                                    $service = $services[$serviceId] ?? null;
                                    if ($service) {
                                        $subtotal += $service->price;
                                        if ($service->is_haircut) {
                                            $hasHaircut = true;
                                            if ($service->price > $maxHaircutPrice) {
                                                $maxHaircutPrice = $service->price;
                                            }
                                        }
                                    }
                                }
                            }

                            $eligible = (bool) $get('_loyalty_eligible');
                            $discount = 0;

                            if ($eligible && $hasHaircut) {
                                // For free haircuts, we record 250 for the staff.
                                // We adjust the subtotal and set the discount to 250.
                                $subtotal = $subtotal - $maxHaircutPrice + 250;
                                $discount = 250;
                            }
                            
                            $set('subtotal', $subtotal);
                            $set('discount', $discount);
                            $set('total', max(0, $subtotal - $discount));
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
                        ->placeholder('e.g. QGH7R3KLMN (optional)')
                        ->visible(fn (Get $get) => $get('payment_method') === 'mpesa')
                        ->maxLength(50),

                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')->prefix('KES')
                        ->numeric()->disabled()->dehydrated()->default(0),

                    Forms\Components\TextInput::make('discount')
                        ->label('Haircut Reward')->prefix('KES')
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->customer_id) return 'Walk-in';
                        return $state ?? 'Unnamed';
                    }),
                Tables\Columns\TextColumn::make('customer.phone')->label('Phone')->placeholder('—'),
                Tables\Columns\TextColumn::make('customer.loyalty_count')
                    ->label('New Progress')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return '—';
                        $percent = min(100, round(($state / 9) * 100));
                        $color = $state >= 9 ? '#10b981' : '#10b981';
                        
                        return "
                            <div class='flex items-center gap-3' style='min-width: 120px;'>
                                <div class='flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden'>
                                    <div class='h-full' style='width: {$percent}%; background-color: {$color};'></div>
                                </div>
                                <span class='text-xs font-medium'>{$state}/9</span>
                            </div>
                        ";
                    }),
                Tables\Columns\TextColumn::make('items.staff.name')
                    ->label('Staff')
                    ->listWithLineBreaks()
                    ->bulleted(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        default => strtoupper($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        default => 'gray',
                    })
                    ->description(fn ($record) => $record->payment_method === 'mpesa' ? $record->mpesa_reference : null),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Gross')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')->money('KES'),
                Tables\Columns\IconColumn::make('is_free_haircut')->boolean()->label('Free'),
                Tables\Columns\TextColumn::make('served_at')->dateTime()->since(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('print_receipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Transaction $record) => route('receipt.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Transaction Details')->components([
                \Filament\Infolists\Components\TextEntry::make('id')->label('TX #'),
                \Filament\Infolists\Components\TextEntry::make('served_at')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                \Filament\Infolists\Components\TextEntry::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        default => strtoupper($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        default => 'gray',
                    }),
                \Filament\Infolists\Components\TextEntry::make('total')->label('Total Paid')->money('KES'),
                \Filament\Infolists\Components\IconEntry::make('is_free_haircut')->boolean()->label('Free Haircut'),
                
                \Filament\Infolists\Components\TextEntry::make('_services_list')
                    ->label('Services & Staff')
                    ->getStateUsing(fn ($record) => $record->items->map(fn($i) => ($i->service->name ?? 'Service') . ' — handled by ' . ($i->staff->name ?? 'Unknown') . ' (KES ' . number_format($i->line_total) . ')'))
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->columnSpanFull(),

                \Filament\Infolists\Components\TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
            ])->columns(2),
        ]);
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
