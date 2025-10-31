<?php

use App\Enums\InventoryCategory;
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
        Schema::dropIfExists('inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('name', 100);
            $table->enum('category', array_column(InventoryCategory::cases(), 'value'))->default(InventoryCategory::CONSUMABLES->value);
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(10);
            $table->timestamps();
        });
    }
};
