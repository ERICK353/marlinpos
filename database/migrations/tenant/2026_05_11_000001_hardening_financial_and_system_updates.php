<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add Soft Deletes to core tables
        if (!Schema::hasColumn('customers', 'deleted_at')) {
            Schema::table('customers', function (Blueprint $table) { $table->softDeletes(); });
        }
        if (!Schema::hasColumn('services', 'deleted_at')) {
            Schema::table('services', function (Blueprint $table) { $table->softDeletes(); });
        }
        if (!Schema::hasColumn('expenses', 'deleted_at')) {
            Schema::table('expenses', function (Blueprint $table) { $table->softDeletes(); });
        }

        // 2. Add Wallet fields to customers
        if (!Schema::hasColumn('customers', 'credit_balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('credit_balance', 10, 2)->default(0)->after('enrolled_at');
            });
        }

        // 3. Add financial audit fields to transactions
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'amount_tendered')) {
                $table->decimal('amount_tendered', 10, 2)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('transactions', 'change_due')) {
                $table->decimal('change_due', 10, 2)->nullable()->after('amount_tendered');
            }
            if (!Schema::hasColumn('transactions', 'credit_used')) {
                $table->decimal('credit_used', 10, 2)->default(0)->after('change_due');
            }
            if (!Schema::hasColumn('transactions', 'credit_stored')) {
                $table->decimal('credit_stored', 10, 2)->default(0)->after('credit_used');
            }
            if (!Schema::hasColumn('transactions', 'cash_paid')) {
                $table->decimal('cash_paid', 10, 2)->default(0)->after('credit_stored');
            }
            if (!Schema::hasColumn('transactions', 'mpesa_paid')) {
                $table->decimal('mpesa_paid', 10, 2)->default(0)->after('cash_paid');
            }
            
            // Fix the enum to include 'wallet' and 'split'
            $table->enum('payment_method', ['cash', 'mpesa', 'wallet', 'split'])->change();
        });

        // 4. Add tip field to transaction items (for staff-specific tips)
        if (!Schema::hasColumn('transaction_items', 'tip_amount')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->decimal('tip_amount', 10, 2)->default(0)->after('line_total');
            });
        }

        // 5. Add status to expenses
        if (!Schema::hasColumn('expenses', 'status')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('status')->default('paid')->after('amount'); // paid, unpaid
            });
        }
    }
 
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('credit_balance');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('status');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['amount_tendered', 'change_due', 'credit_used', 'credit_stored', 'cash_paid', 'mpesa_paid']);
            $table->enum('payment_method', ['cash', 'mpesa'])->change();
        });
    }
};
