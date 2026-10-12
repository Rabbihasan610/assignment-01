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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('product_unit_id')
                ->nullable()
                ->constrained('product_units')
                ->nullOnDelete();

            $table->enum('adjustment_type', [
                'IN',
                'OUT',
                'DAMAGE',
                'RETURN',
                'ADJUSTMENT',
            ]);
            $table->decimal('quantity', 15, 4);
            $table->decimal('conversion_factor', 15, 4)
                ->default(1);
            $table->decimal('base_quantity', 15, 4);
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(
                ['product_id', 'adjustment_type'],
                'stock_adjustments_product_type_index'
            );
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
