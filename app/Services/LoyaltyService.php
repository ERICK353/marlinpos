<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;

class LoyaltyService
{
    /**
     * Apply loyalty logic to a transaction for a known customer.
     *
     * - If loyalty_count >= 9 → mark as free shave, reset counter.
     * - Otherwise increment loyalty counter.
     * - Always increment total_visits.
     */
    public function apply(Customer $customer, Transaction $transaction): void
    {
        // Ensure we have the latest items and services loaded
        $transaction->loadMissing('items.service');

        // Count how many actual haircuts are in this transaction
        $haircutsInTransaction = $transaction->items->filter(function ($item) {
            if (! $item->service) return false;
            return $item->service->is_haircut || 
                   stripos($item->service->name, 'Hair Cut') !== false || 
                   stripos($item->service->name, 'Haircut') !== false;
        })->count();

        // Ensure the customer instance is fresh
        $customer->refresh();

        // Process each haircut individually
        for ($i = 0; $i < $haircutsInTransaction; $i++) {
            if ($customer->isEligibleForFreeHaircut()) {
                // Hit the 10th haircut!
                $customer->loyalty_count = 0;
                $customer->free_haircuts_used++;
            } else {
                // Increment counter towards the 10th
                $customer->loyalty_count++;
            }
        }

        $customer->total_visits++;
        $customer->save();

        // If this is a free haircut transaction, ensure one haircut item is recorded as 250 KES
        // for the staff revenue dashboard.
        if ($transaction->is_free_haircut) {
            $freeItem = $transaction->items->filter(fn($i) => $i->service?->is_haircut)->sortByDesc('unit_price')->first();
            if ($freeItem) {
                $freeItem->update([
                    'unit_price' => 250,
                    'line_total' => 250,
                ]);
                
                // Also ensure the transaction subtotal/discount reflect this 250 value
                // so the total remains correct (0 for the free item).
                $otherItemsTotal = $transaction->items->where('id', '!=', $freeItem->id)->sum('line_total');
                $transaction->subtotal = $otherItemsTotal + 250;
                $transaction->discount = 250;
            }
        }

        $transaction->save();
    }

    /**
     * Record a visit for a customer without applying a free shave
     * (used when loyalty is not applicable, e.g. M-Pesa walk-in with no loyalty card).
     */
    public function recordVisit(Customer $customer): void
    {
        $customer->increment('total_visits');
    }
}
