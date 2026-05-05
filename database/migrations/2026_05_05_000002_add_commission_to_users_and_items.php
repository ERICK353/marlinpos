<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->default(40.00)->after('role');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('staff_user_id');
            $table->decimal('commission_amount', 10, 2)->nullable()->after('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'commission_amount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
