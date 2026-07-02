<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('paid_at');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index('paid_at');
        });

        
        DB::table('orders')
            ->where('advance_paid', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                $now  = now();
                $rows = [];

                foreach ($orders as $order) {
                    $rows[] = [
                        'order_id'   => $order->id,
                        'amount'     => $order->advance_paid,
                        'paid_at'    => $order->booking_date ?? $order->created_at,
                        'note'       => 'Backfilled from advance_paid total (exact payment date unknown)',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('payments')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};