<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Second safety-net migration: the live `payments` table is also
     * missing created_at / updated_at. This was silently breaking order
     * saves entirely (the payment insert threw inside the same DB
     * transaction as the order save, rolling everything back). This adds
     * whatever's still missing and re-runs the historical backfill for
     * any order that doesn't have a payment logged yet.
     */
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'created_at') || ! Schema::hasColumn('payments', 'updated_at')) {
                $table->timestamps();
            }
        });

        // Defensive: make sure every column the app writes to actually
        // exists, in case the live table diverged in other ways too.
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

        // ── Re-run the backfill for any order missing a payment row ──────
        // (covers orders that failed earlier because the insert kept
        // erroring out before any payment could be written).
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