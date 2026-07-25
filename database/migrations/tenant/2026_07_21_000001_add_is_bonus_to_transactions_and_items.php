<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'is_bonus')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->boolean('is_bonus')->default(false)->after('is_free_haircut');
            });
        }

        if (!Schema::hasColumn('transaction_items', 'is_bonus')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->boolean('is_bonus')->default(false)->after('commission_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'is_bonus')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('is_bonus');
            });
        }

        if (Schema::hasColumn('transaction_items', 'is_bonus')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->dropColumn('is_bonus');
            });
        }
    }
};
