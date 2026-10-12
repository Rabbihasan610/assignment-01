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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->integer('delivery_man_id')->nullable();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'ASSIGNED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED'])->default('PENDING');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->text('delivery_address')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('delivered_latitude')->nullable();
            $table->string('delivered_longitude')->nullable();
            $table->string('proof_image')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();

            $table->foreignId('accepted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
