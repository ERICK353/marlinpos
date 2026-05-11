<?php

namespace App\Filament\Reception\Resources\CheckoutResource\Pages;

use App\Filament\Reception\Resources\CheckoutResource;
use App\Models\Customer;
use App\Models\Service;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\LoyaltyService;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckout extends CreateRecord
{
    protected static string $resource = CheckoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Grab all form state including non-dehydrated virtual fields
        $raw = $this->form->getRawState();
        // Stamp reception user + timestamp
        $data['reception_user_id'] = auth()->id();
        $data['served_at']         = now();

        // Register new customer if operator chose to enroll
        if (! empty($raw['enroll_new']) && ! empty($raw['customer_phone'])) {
            $phone = (string) $raw['customer_phone'];
            
            // Use updateOrCreate to safely handle existing phone numbers
            $customer = Customer::updateOrCreate(
                ['phone' => $phone],
                [
                    'name' => $raw['new_customer_name'] ?? null,
                    // initial_loyalty_count is only applied during creation in the next block if we want,
                    // but updateOrCreate doesn't easily support "only on create" for specific fields.
                    // So we check existence first or use the firstOrNew approach.
                ]
            );

            // If it was just created (no loyalty count yet), set the initial count and wallet
            if ($customer->wasRecentlyCreated) {
                $customer->update([
                    'loyalty_count'  => $raw['initial_loyalty_count'] ?? 0,
                    'credit_balance' => $raw['initial_wallet_balance'] ?? 0,
                    'enrolled_at'    => now(),
                ]);
            }

            $data['customer_id'] = $customer->id;

            // ── Loyalty: If newly enrolled customer has 9+, mark as eligible ──
            if ($customer->isEligibleForFreeHaircut()) {
                $raw['_loyalty_eligible'] = true;
            }
        }

        // ── Wallet: if payment method is 'wallet', deduct full total ──────────
        if (($data['payment_method'] ?? '') === 'wallet' && ! empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) {
                // If they were just created, use the raw initial balance since the DB might not have synced yet
                $balance = $customer->wasRecentlyCreated 
                    ? (float)($raw['initial_wallet_balance'] ?? 0) 
                    : (float)$customer->credit_balance;
                    
                $data['credit_used'] = min((float) $data['total'], $balance);
            }
        }

        // ── Wallet: cap credit_used at customer's actual balance (cash+credit combo) ──
        if (($data['payment_method'] ?? '') !== 'wallet' && ! empty($data['customer_id']) && ! empty($data['credit_used'])) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) {
                $data['credit_used'] = min((float) $data['credit_used'], (float) $customer->credit_balance);
            }
        } elseif (empty($data['credit_used'])) {
            $data['credit_used'] = 0;
        }

        // ── Wallet: store change as credit if toggled ───────────────────────
        $data['credit_stored'] = 0;
        if (($raw['store_change_as_credit'] ?? '0') === '1' && ! empty($data['customer_id'])) {
            $data['credit_stored'] = max(0, (float) ($data['change_due'] ?? 0));
        }

        // Derive free-haircut flag from loyalty state
        $isFree = (bool) ($raw['_loyalty_eligible'] ?? false);
        $data['is_free_haircut'] = $isFree;

        // ── Snapshot Prices: Ensure historical prices are kept ──────────────
        if (! empty($data['items'])) {
            $serviceIds = array_column($data['items'], 'service_id');
            $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

            // If it's a loyalty reward, find the most expensive haircut index
            $maxPrice = 0;
            $maxIndex = -1;
            if ($isFree) {
                foreach ($data['items'] as $idx => $it) {
                    $s = $services[$it['service_id']] ?? null;
                    if ($s && $s->is_haircut && $s->price > $maxPrice) {
                        $maxPrice = $s->price;
                        $maxIndex = $idx;
                    }
                }
            }

            foreach ($data['items'] as $index => &$item) {
                $service = $services[$item['service_id']] ?? null;
                if ($service) {
                    $finalPrice = $service->price;
                    
                    // If this is the chosen free haircut, set price to 250
                    if ($isFree && $index === $maxIndex) {
                        $finalPrice = 250;
                    }
                    
                    $item['unit_price'] = $finalPrice;
                    $item['line_total'] = $finalPrice;
                }
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $transaction = $this->record;

        if ($transaction->customer_id) {
            $customer = $transaction->customer()->firstOrFail();

            // ── Deduct wallet credit used in this transaction ───────────────
            if ((float) $transaction->credit_used > 0) {
                $customer->deductCredit((float) $transaction->credit_used);
            }

            // ── Store change as wallet credit for future visits ────────────
            if ((float) $transaction->credit_stored > 0) {
                $customer->addCredit((float) $transaction->credit_stored);
            }

            // Apply loyalty: increment counter or grant free haircut
            app(LoyaltyService::class)->apply($customer, $transaction);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

