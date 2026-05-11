<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Seed default categories
        DB::table('expense_categories')->insert([
            ['name' => 'Electricity',    'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internet',       'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shop Supplies',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Water',          'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Repairs',        'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Drinking Water', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other',          'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
