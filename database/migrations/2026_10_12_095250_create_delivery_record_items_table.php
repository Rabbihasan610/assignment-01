<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_record_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_record_id')
                ->constrained('delivery_records')
                ->restrictOnDelete();

            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->restrictOnDelete();

            $table->decimal('quantity', 10, 2);
            $table->decimal('base_quantity', 12, 4);

            $table->timestamps();

            $table->index(['order_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_record_items');
    }
};