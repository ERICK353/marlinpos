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
use Illuminate\Database\Eloquent\Builder;

class CheckoutResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-shopping-cart'; }
    public static function getNavigationLabel(): string { return 'Checkout'; }
    public static function getModelLabel(): string { return 'Checkout'; }
    public static function getPluralModelLabel(): string { return 'Checkouts'; }
    public static function getNavigationSort(): ?int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Hidden state fields (virtual, not saved directly) ─────────────
            Forms\Components\Hidden::make('_loyalty_eligible')->default(false)->dehydrated(false),
            Forms\Components\Hidden::make('_customer_name')->dehydrated(false),
            Forms\Components\Hidden::make('_loyalty_count')->default(0)->dehydrated(false),

            \Filament\Schemas\Components\Wizard::make([
                // ── STEP 1: Enter Customer Information ────────────────────────────
                \Filament\Schemas\Components\Wizard\Step::make('Enter Customer Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Toggle::make('is_walk_in')
                            ->label('Walk-in Customer (No loyalty tracking)')
                            ->default(false)
                            ->live()
                            ->dehydrated(false)
                            ->disabled(fn ($record) => filled($record))
                            ->visible(fn (Get $get) => ! (bool) $get('enroll_new'))
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state) {
                                    $set('customer_phone', null);
                                    $set('customer_id', null);
                                    $set('_customer_name', null);
                                    $set('_loyalty_count', 0);
                                    $set('_loyalty_eligible', false);
                                    $set('enroll_new', false);
                                }
                            }),

                        Forms\Components\Select::make('customer_id')
                            ->label('Select Existing Customer')
                            ->relationship('customer', 'name', fn ($query) => $query->withoutTrashed())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(fn (Get $get) => ! (bool) $get('is_walk_in') && ! (bool) $get('enroll_new'))
                            ->disabled(fn ($record) => filled($record))
                            ->visible(fn (Get $get) => ! (bool) $get('is_walk_in') && ! (bool) $get('enroll_new'))
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                if (blank($state)) {
                                    $set('_customer_name', null);
                                    $set('_loyalty_count', 0);
                                    $set('_loyalty_eligible', false);
                                    return;
                                }
                                $customer = Customer::find($state);
                                if ($customer) {
                                    $set('_customer_name', $customer->name);
                                    $set('_loyalty_count', $customer->loyalty_count);
                                    $set('_loyalty_eligible', $customer->isEligibleForFreeHaircut());
                                    $set('enroll_new', false);
                                }
                            }),

                        Forms\Components\Toggle::make('enroll_new')
                            ->label('Enroll New Customer')
                            ->live()
                            ->dehydrated(false)
                            ->disabled(fn ($record) => filled($record))
                            ->visible(fn (Get $get) => ! (bool) $get('is_walk_in') && blank($get('customer_id'))),

                        Forms\Components\TextInput::make('customer_phone')
                            ->label('New Customer Phone')
                            ->tel()
                            ->placeholder('Enter phone…')
                            ->required(fn (Get $get) => (bool) $get('enroll_new'))
                            ->visible(fn (Get $get) => (bool) $get('enroll_new'))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (blank($state)) return;
                                $customer = Customer::where('phone', $state)->first();
                                if ($customer) {
                                    $set('customer_id', $customer->id);
                                    $set('_customer_name', $customer->name ?? 'Enrolled member');
                                    $set('_loyalty_count', $customer->loyalty_count);
                                    $set('_loyalty_eligible', $customer->isEligibleForFreeHaircut());
                                    $set('enroll_new', false);
                                }
                            }),

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
                            ->helperText('Previous haircuts (0-9).')
                            ->required(fn (Get $get) => (bool) $get('enroll_new'))
                            ->dehydrated(false)
                            ->visible(fn (Get $get) => (bool) $get('enroll_new'))
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('_loyalty_eligible', (int) $state >= 9);
                            }),

                        Forms\Components\TextInput::make('initial_wallet_balance')
                            ->label('Starting Wallet Balance')
                            ->numeric()
                            ->prefix('KES')
                            ->default(0)
                            ->minValue(0)
                            ->required(fn (Get $get) => (bool) $get('enroll_new'))
                            ->dehydrated(false)
                            ->visible(fn (Get $get) => (bool) $get('enroll_new')),

                        Forms\Components\Placeholder::make('_customer_info')
                            ->label('Haircut Loyalty Status')
                            ->visible(fn (Get $get) => filled($get('customer_id')))
                            ->content(function (Get $get) {
                                $count    = $get('_loyalty_count') ?? 0;
                                $eligible = (bool) $get('_loyalty_eligible');
                                $name     = $get('_customer_name') ?? 'Customer';
                                return $eligible ? "{$name} — Free Haircut Eligible (9/9)" : "{$name} — Progress: {$count}/9";
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                // ── STEP 2: Enter Services Information ────────────────────────────
                \Filament\Schemas\Components\Wizard\Step::make('Enter Services Information')
                    ->icon('heroicon-o-scissors')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->label('Services Performed')
                            ->schema([
                                Forms\Components\Select::make('service_id')
                                    ->label('Service')
                                    ->options(fn () => Service::active()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => filled($record))
                                    ->live()
                                    ->columnSpan(4),

                                Forms\Components\Select::make('staff_user_id')
                                    ->label('Staff Member')
                                    ->options(fn () => User::where('role', 'staff')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->disabled(fn ($record) => filled($record))
                                    ->columnSpan(4),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->disabled()
                                    ->dehydrated()
                                    ->visible(fn ($record) => filled($record))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('discount_amount')
                                    ->label('Service Discount')
                                    ->prefix('KES')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live()
                                    ->disabled(fn ($record) => filled($record))
                                    ->columnSpan(fn ($record) => filled($record) ? 2 : 2),

                                Forms\Components\Toggle::make('is_bonus')
                                    ->label('Bonus')
                                    ->default(false)
                                    ->live()
                                    ->disabled(fn ($record) => filled($record))
                                    ->visible(fn (Get $get) => (bool) $get('../../is_walk_in'))
                                    ->columnSpan(2),
                                
                                Forms\Components\Hidden::make('line_total')->dehydrated(),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addable(fn ($record) => blank($record))
                            ->deletable(fn ($record) => blank($record))
                            ->reorderable(false)
                            ->live()
                            ->afterStateUpdated(function (?array $state, Set $set, Get $get) {
                                $serviceIds = array_column($state ?? [], 'service_id');
                                if (empty($serviceIds)) {
                                    $set('subtotal', 0); $set('discount', 0); $set('total', 0); return;
                                }
                                $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');
                                $subtotal = 0; $maxHaircutPrice = 0; $hasHaircut = false;
                                foreach ($state as $item) {
                                    if ($sId = $item['service_id'] ?? null) {
                                        if ($s = $services[$sId] ?? null) {
                                            $itemIsBonus = (bool) ($item['is_bonus'] ?? false);
                                            if ($itemIsBonus) {
                                                continue;
                                            }
                                            $subtotal += $s->price;
                                            $isHaircut = $s->is_haircut || stripos($s->name, 'Haircut') !== false || stripos($s->name, 'Hair Cut') !== false;
                                            if ($isHaircut) {
                                                $hasHaircut = true;
                                                $maxHaircutPrice = max($maxHaircutPrice, $s->price);
                                            }
                                        }
                                    }
                                }
                                $discount = ((bool) $get('_loyalty_eligible') && $hasHaircut) ? 250 : 0;
                                if ($discount > 0) $subtotal = $subtotal - $maxHaircutPrice + 250;
                                
                                $serviceDiscounts = collect($state ?? [])->sum(fn($i) => (float)($i['discount_amount'] ?? 0));
                                $set('subtotal', $subtotal);
                                $set('discount', $discount);
                                $set('total', max(0, (float)$subtotal - (float)$discount - (float)$serviceDiscounts));
                            })
                            ->required(),
                    ]),

                // ── STEP 3: Enter Payment Information ─────────────────────────────
                \Filament\Schemas\Components\Wizard\Step::make('Enter Payment Information')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Radio::make('payment_method')
                            ->label('Payment Method')
                            ->options(function (Get $get) {
                                $options = [
                                    'cash' => '💵 Cash', 
                                    'mpesa' => '📱 M-Pesa',
                                    'split' => '🌓 Split: Cash + M-Pesa'
                                ];
                                if ($id = $get('customer_id')) {
                                    if ($c = Customer::find($id)) {
                                        $balance = (float) $c->credit_balance;
                                        if ($balance > 0) {
                                            $total = (float) $get('total');
                                            if ($balance >= $total) {
                                                $options['wallet'] = '💰 Full Wallet Payment';
                                            } else {
                                                $options['wallet'] = '🌓 Split: Wallet + Cash/M-Pesa';
                                            }
                                        }
                                    }
                                }
                                return $options;
                            })
                            ->required()
                            ->disabled(fn ($record) => filled($record))
                            ->live()
                            ->inline(),

                        \Filament\Schemas\Components\Grid::make(2)
                            ->visible(fn (Get $get) => $get('payment_method') === 'split')
                            ->schema([
                                Forms\Components\TextInput::make('mpesa_paid')
                                    ->label('Amount via M-Pesa')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('KES')
                                    ->live(debounce: 250)
                                    ->required(fn (Get $get) => $get('payment_method') === 'split')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $total = (float) $get('total');
                                        $mpesa = (float) $state;
                                        $cash_due = max(0, $total - $mpesa);
                                        $set('cash_paid', $cash_due);
                                        $set('amount_tendered', $cash_due);
                                        $set('change_due', 0);
                                    }),
                                Forms\Components\TextInput::make('cash_paid')
                                    ->label('Amount via Cash')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('KES')
                                    ->live(debounce: 250)
                                    ->required(fn (Get $get) => $get('payment_method') === 'split')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $total = (float) $get('total');
                                        $cash = (float) $state;
                                        $set('mpesa_paid', max(0, $total - $cash));
                                        $set('amount_tendered', $cash);
                                        $set('change_due', 0);
                                    }),
                            ]),

                        Forms\Components\Placeholder::make('_split_payment_info')
                            ->label('Split Payment Breakdown')
                            ->visible(fn (Get $get) => $get('payment_method') === 'wallet')
                            ->content(function (Get $get) {
                                if ($id = $get('customer_id')) {
                                    $c = Customer::find($id);
                                    if ($c) {
                                        $balance = (float) $c->credit_balance;
                                        $total = (float) $get('total');
                                        if ($balance < $total) {
                                            $remaining = $total - $balance;
                                            return "Wallet: KES " . number_format($balance, 2) . " | Remaining: KES " . number_format($remaining, 2);
                                        }
                                        return "Full amount of KES " . number_format($total, 2) . " will be deducted from wallet.";
                                    }
                                }
                                return null;
                            }),

                        Forms\Components\Select::make('split_secondary_method')
                            ->label('Pay Remaining Balance Via')
                            ->options(['cash' => '💵 Cash', 'mpesa' => '📱 M-Pesa'])
                            ->required(function (Get $get) {
                                if ($get('payment_method') !== 'wallet') return false;
                                if ($id = $get('customer_id')) {
                                    $c = Customer::find($id);
                                    return $c && (float)$c->credit_balance < (float)$get('total');
                                }
                                return false;
                            })
                            ->visible(function (Get $get) {
                                if ($get('payment_method') !== 'wallet') return false;
                                if ($id = $get('customer_id')) {
                                    $c = Customer::find($id);
                                    return $c && (float)$c->credit_balance < (float)$get('total');
                                }
                                return false;
                            })
                            ->live(),

                        Forms\Components\TextInput::make('mpesa_reference')
                            ->label('M-Pesa Reference (optional)')
                            ->unique(ignoreRecord: true)
                            ->visible(fn (Get $get) => 
                                $get('payment_method') === 'mpesa' || 
                                $get('payment_method') === 'split' ||
                                ($get('payment_method') === 'wallet' && $get('split_secondary_method') === 'mpesa')
                            )
                            ->maxLength(50),

                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')->label('Total Items')->prefix('KES')->numeric()->disabled()->dehydrated(),
                                Forms\Components\TextInput::make('discount')->label('Loyalty Discount')->prefix('KES')->numeric()->disabled()->dehydrated(),
                                Forms\Components\TextInput::make('total')->label('TOTAL')->prefix('KES')->numeric()->disabled()->dehydrated(),
                            ]),

                        Forms\Components\Hidden::make('credit_used')->default(0),
                        Forms\Components\Hidden::make('change_due')->default(0),
                        Forms\Components\Hidden::make('credit_stored')->default(0),

                        // ── Cash Change Calculator ────────────────────────────────
                        Forms\Components\TextInput::make('amount_tendered')
                            ->label(function(Get $get) {
                                if ($get('payment_method') === 'wallet') return 'Additional Cash Given';
                                if ($get('payment_method') === 'split') return 'Physical Cash Given';
                                return 'Amount Given';
                            })
                            ->prefix('KES')
                            ->numeric()
                            ->live(debounce: 250)
                            ->required(fn (Get $get) => 
                                $get('payment_method') === 'cash' || 
                                $get('payment_method') === 'split' ||
                                ($get('payment_method') === 'wallet' && $get('split_secondary_method') === 'cash')
                            )
                            ->minValue(function (Get $get) {
                                $total = (float) $get('total');
                                if ($get('payment_method') === 'wallet') {
                                    if ($id = $get('customer_id')) {
                                        $c = Customer::find($id);
                                        if ($c) {
                                            $balance = (float) $c->credit_balance;
                                            return max(0, $total - $balance);
                                        }
                                    }
                                }
                                if ($get('payment_method') === 'split') {
                                    return (float) $get('cash_paid');
                                }
                                return $total;
                            })
                            ->visible(fn (Get $get) => 
                                $get('payment_method') === 'cash' || 
                                $get('payment_method') === 'split' ||
                                ($get('payment_method') === 'wallet' && $get('split_secondary_method') === 'cash')
                            )
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $total = (float) $get('total');
                                $tendered = (float) $state;
                                if ($get('payment_method') === 'wallet') {
                                    if ($id = $get('customer_id')) {
                                        $c = Customer::find($id);
                                        if ($c) {
                                            $balance = (float) $c->credit_balance;
                                            $remaining = max(0, $total - $balance);
                                            $set('change_due', max(0, $tendered - $remaining));
                                            return;
                                        }
                                    }
                                }
                                if ($get('payment_method') === 'split') {
                                    $cashNeeded = (float) $get('cash_paid');
                                    $set('change_due', max(0, $tendered - $cashNeeded));
                                    return;
                                }
                                $set('change_due', max(0, $tendered - $total));
                            }),

                        Forms\Components\Placeholder::make('_change_display')
                            ->label('Change')
                            ->content(fn (Get $get) => 'KES ' . number_format((float) $get('change_due'), 2))
                            ->visible(fn (Get $get) => 
                                ($get('payment_method') === 'cash' || $get('payment_method') === 'split' || ($get('payment_method') === 'wallet' && $get('split_secondary_method') === 'cash')) 
                                && filled($get('amount_tendered'))
                            ),

                        Forms\Components\Radio::make('store_change_as_credit')
                            ->label('Change Action')
                            ->options([
                                '0' => '💵 Hand to Customer',
                                '1' => '💰 Store in Wallet',
                            ])
                            ->required(fn (Get $get) => 
                                (float)$get('change_due') > 0 && (
                                    $get('payment_method') === 'cash' || 
                                    $get('payment_method') === 'split' ||
                                    ($get('payment_method') === 'wallet' && $get('split_secondary_method') === 'cash')
                                )
                            )
                            ->inline()
                            ->visible(fn (Get $get) =>
                                (float) $get('change_due') > 0 &&
                                (filled($get('customer_id')) || (bool) $get('enroll_new')) &&
                                ($get('payment_method') === 'cash' || $get('payment_method') === 'split' || ($get('payment_method') === 'wallet' && $get('split_secondary_method') === 'cash'))
                            ),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2)->placeholder('Optional notes…')->columnSpanFull(),
                    ]),
            ])
            ->columnSpanFull()
            ->submitAction(new \Illuminate\Support\HtmlString(\Illuminate\Support\Facades\Blade::render('<x-filament::button type="submit" size="sm" color="primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">Save</x-filament::button>'))),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) =>
                $query->orderByDesc('served_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->html()
                    ->getStateUsing(function ($record) {
                        if (! $record->customer_id) return 'Walk-in';
                        return $record->customer?->name ?? 'Unnamed';
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === 'Walk-in') return 'Walk-in';
                        
                        if ($record->customer?->trashed()) {
                            return "
                                <div style='display: flex; align-items: center; gap: 8px;'>
                                    <span>{$state}</span>
                                    <span style='background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; border: 1px solid #fecaca;'>Deleted</span>
                                </div>
                            ";
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('customer.phone')->label('Phone')->placeholder('—')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('customer.loyalty_count')
                    ->label('Progress')
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
                    })->toggleable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Staff')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return $record->items->map(fn($item) => [
                            'name' => $item->staff?->name ?? 'Unknown',
                            'trashed' => (bool)$item->staff?->trashed()
                        ])
                        ->unique('name')
                        ->map(function ($staff) {
                            $tag = $staff['trashed'] 
                                ? " <span style='background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; margin-left: 4px; border: 1px solid #fecaca;'>Deleted</span>" 
                                : "";
                            return "• {$staff['name']}{$tag}";
                        })->implode('<br>');
                    })
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('items.staff', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    )->toggleable(),
                Tables\Columns\TextColumn::make('services_unique')
                    ->label('Services')
                    ->getStateUsing(fn ($record) => $record->items->map(fn($i) => $i->service?->name ?? 'Unknown')->unique())
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('items.service', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 CASH',
                        'mpesa' => '📱 M-PESA',
                        'wallet' => '💰 WALLET',
                        'split' => '🌓 SPLIT',
                        default => strtoupper($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        'wallet' => 'warning',
                        'split' => 'primary',
                        default => 'gray',
                    })
                    ->description(function ($record) {
                        if ($record->payment_method === 'split') {
                            $desc = "📱 KES " . number_format($record->mpesa_paid, 0) . " | 💵 KES " . number_format($record->cash_paid, 0);
                            if ($record->mpesa_reference) $desc .= " ({$record->mpesa_reference})";
                            return $desc;
                        }
                        if ($record->payment_method === 'mpesa') return $record->mpesa_reference;
                        if ($record->payment_method === 'cash') {
                            if ($record->credit_stored > 0) {
                                return "💰 KES " . number_format($record->credit_stored, 0) . " stored in wallet";
                            }
                            if ($record->change_due > 0) {
                                return "💵 KES " . number_format($record->change_due, 0) . " change given";
                            }
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Gross')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Service Discounts')
                    ->money('KES')
                    ->state(fn ($record) => $record->items->sum('discount_amount'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')->money('KES'),
                Tables\Columns\IconColumn::make('is_free_haircut')->boolean()->label('Free')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_bonus')->boolean()->label('Bonus')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('served_at')
                    ->label('Time')
                    ->dateTime()
                    ->since()
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        $time = $record->served_at->diffForHumans();
                        $tag = $record->updated_at->gt($record->created_at->addSeconds(5)) 
                            ? " <span style='background-color: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; margin-left: 4px; border: 1px solid #fde68a;'>Edited</span>" 
                            : "";
                        return "<span>{$time}</span>{$tag}";
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\Action::make('print_receipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Transaction $record) => route('receipt.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
                        'wallet' => '💰 WALLET',
                        'split' => '🌓 SPLIT',
                        default => strtoupper($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'mpesa' => 'success',
                        'cash' => 'info',
                        'wallet' => 'warning',
                        'split' => 'primary',
                        default => 'gray',
                    }),
                \Filament\Infolists\Components\TextEntry::make('total')->label('Total Paid')->money('KES'),
                \Filament\Infolists\Components\IconEntry::make('is_free_haircut')->boolean()->label('Free Haircut'),
                \Filament\Infolists\Components\IconEntry::make('is_bonus')->boolean()->label('Bonus Haircut'),

                \Filament\Infolists\Components\TextEntry::make('mpesa_paid')
                    ->label('Paid via M-Pesa')
                    ->money('KES')
                    ->visible(fn($record) => $record?->payment_method === 'split' || $record?->payment_method === 'mpesa'),
                
                \Filament\Infolists\Components\TextEntry::make('cash_paid')
                    ->label('Paid via Cash')
                    ->money('KES')
                    ->visible(fn($record) => $record?->payment_method === 'split'),

                \Filament\Infolists\Components\TextEntry::make('amount_tendered')->label('Cash Given')->money('KES')->visible(fn($record) => $record?->payment_method === 'cash'),
                \Filament\Infolists\Components\TextEntry::make('change_due')->label('Change Provided')->money('KES')->visible(fn($record) => $record?->payment_method === 'cash' && (float)$record?->credit_stored <= 0),
                \Filament\Infolists\Components\TextEntry::make('credit_used')->label('Wallet Credit Used')->money('KES')->visible(fn($record) => (float)$record?->credit_used > 0),
                \Filament\Infolists\Components\TextEntry::make('credit_stored')->label('Stored as Credit')->money('KES')->visible(fn($record) => (float)$record?->credit_stored > 0),
                
                \Filament\Infolists\Components\TextEntry::make('_services_list')
                    ->label('Services & Staff')
                    ->getStateUsing(fn ($record) => $record->items->map(function($i) {
                        $bonusTag = $i->is_bonus ? ' [Bonus Haircut - KES 0 Rev]' : '';
                        $label = ($i->service->name ?? 'Service') . ' — handled by ' . ($i->staff->name ?? 'Unknown') . ' (KES ' . number_format($i->line_total) . ')' . $bonusTag;
                        if ($i->discount_amount > 0) {
                            $label .= ' [Discount: KES ' . number_format($i->discount_amount) . ']';
                        }
                        return $label;
                    }))
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
    public static function canDelete($record): bool { return true; }
}
