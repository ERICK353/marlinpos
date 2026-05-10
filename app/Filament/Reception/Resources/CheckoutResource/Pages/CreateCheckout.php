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

        // Register new customer if operator chose to enroll
        if (! empty($raw['enroll_new']) && ! empty($raw['customer_phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $raw['customer_phone']],
                [
                    'name'          => $raw['new_customer_name'] ?? null,
                    'loyalty_count' => $raw['initial_loyalty_count'] ?? 0,
                    'enrolled_at'   => now(),
                ]
            );
            $data['customer_id'] = $customer->id;
        }

        // Stamp reception user + timestamp
        $data['reception_user_id'] = auth()->id();
        $data['served_at']         = now();

        // Derive free-haircut flag from loyalty state
        $isFree = (bool) ($raw['_loyalty_eligible'] ?? false);
        $data['is_free_haircut'] = $isFree;

        // If it's a free haircut, adjust the price of the most expensive haircut item to 250
        // so the staff gets credited 250 instead of 0 or the full price.
        if ($isFree && ! empty($data['items'])) {
            $maxPrice = 0;
            $maxIndex = -1;
            
            // We need to fetch service prices to identify the most expensive haircut
            $serviceIds = array_column($data['items'], 'service_id');
            $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

            foreach ($data['items'] as $index => $item) {
                $service = $services[$item['service_id']] ?? null;
                if ($service && $service->is_haircut) {
                    if ($service->price > $maxPrice) {
                        $maxPrice = $service->price;
                        $maxIndex = $index;
                    }
                }
            }

            if ($maxIndex !== -1) {
                $data['items'][$maxIndex]['unit_price'] = 250;
                $data['items'][$maxIndex]['line_total'] = 250;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $transaction  = $this->record;

        // Apply loyalty: increment counter or grant free shave
        if ($transaction->customer_id) {
            $customer = $transaction->customer()->firstOrFail();
            app(LoyaltyService::class)->apply($customer, $transaction);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
