<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('payments', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 10, 2)->after('order_id');
            }
            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->date('paid_at')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('payments', 'note')) {
                $table->string('note', 255)->nullable()->after('paid_at');
            }
        });

        DB::table('orders')
            ->where('advance_paid', '>', 0)
            ->whereNotIn('id', function ($q) {
                $q->select('order_id')->from('payments');
            })
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

                if (! empty($rows)) {
                    DB::table('payments')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        // No-op — don't want a rollback to delete the backfilled payments.
    }
};