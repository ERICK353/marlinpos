<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('transaction_items', 'tip_amount')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->renameColumn('tip_amount', 'discount_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('transaction_items', 'discount_amount')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->renameColumn('discount_amount', 'tip_amount');
            });
        }
    }
};
