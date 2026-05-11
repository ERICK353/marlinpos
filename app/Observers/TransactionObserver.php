<?php
 
namespace App\Observers;
 
use App\Models\Transaction;
 
class TransactionObserver
{
    /**
     * Handle the Transaction "deleted" event.
     * This handles both normal deletes and soft deletes.
     */
    public function deleted(Transaction $transaction): void
    {
        $customer = $transaction->customer;
        if (! $customer) {
            return;
        }
 
        // 1. Revert Wallet Credit Used (give it back to the customer)
        if ($transaction->credit_used > 0) {
            $customer->credit_balance = ($customer->credit_balance ?? 0) + $transaction->credit_used;
        }

        // 2. Revert Wallet Credit Stored (take it away from the customer)
        if ($transaction->credit_stored > 0) {
            $customer->credit_balance = max(0, ($customer->credit_balance ?? 0) - $transaction->credit_stored);
        }

        // 3. Revert Loyalty Progress
        $haircutCount = $transaction->items()
            ->whereHas('service', fn($q) => $q->where('is_haircut', true))
            ->count();

        if ($haircutCount > 0) {
            $customer->loyalty_count = max(0, ($customer->loyalty_count ?? 0) - $haircutCount);
        }

        // 4. Revert Free Haircut Usage
        if ($transaction->is_free_haircut) {
            $customer->free_haircuts_used = max(0, ($customer->free_haircuts_used ?? 0) - 1);
            // Reverting a free haircut usually means returning the 9 loyalty points used to earn it
            $customer->loyalty_count = min(9, ($customer->loyalty_count ?? 0) + 9);
        }

        $customer->total_visits = max(0, ($customer->total_visits ?? 0) - 1);
        $customer->save();
    }
}
