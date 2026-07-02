<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 20)->unique(); // suit number e.g. 6617
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->date('booking_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->smallInteger('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('advance_paid', 10, 2)->default(0.00);
            $table->string('colour_note', 150)->nullable(); // "Write for colour"
            $table->text('extra_notes')->nullable();        // extra design notes
            $table->enum('status', [
                'pending', 'stitching', 'ready', 'delivered', 'returned', 'cancelled'
            ])->default('pending');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
