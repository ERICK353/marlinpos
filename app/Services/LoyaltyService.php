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
        if ($customer->isEligibleForFreeShave()) {
            $transaction->is_free_shave = true;
            $transaction->discount      = $transaction->subtotal;
            $transaction->total         = 0;
            $customer->loyalty_count    = 0;
            $customer->free_shaves_used = $customer->free_shaves_used + 1;
        } else {
            $customer->loyalty_count = $customer->loyalty_count + 1;
        }

        $customer->total_visits = $customer->total_visits + 1;
        $customer->save();

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
