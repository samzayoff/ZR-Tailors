<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Safety-net migration: your `payments` table exists but is missing
     * columns the app needs (paid_at / note). This adds only what's
     * missing, without touching any rows you already have.
     */
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            // Table doesn't exist at all yet — nothing to patch, the
            // original create_payments_table migration will handle it.
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->date('paid_at')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('payments', 'note')) {
                $table->string('note', 255)->nullable()->after('paid_at');
            }
        });

        // Any existing rows that got created before paid_at existed
        // won't have a value — backfill them using created_at as the
        // best available guess, then make the column required.
        DB::table('payments')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('DATE(created_at)')]);

        // NOTE: not using ->change() here since it needs doctrine/dbal,
        // which this project doesn't have installed. Raw SQL avoids that.
        DB::statement('ALTER TABLE payments MODIFY paid_at DATE NOT NULL');

        if (! Schema::hasTable('payments') || ! $this->hasIndex('payments', 'payments_paid_at_index')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index('paid_at');
            });
        }
    }

    public function down(): void
    {
        // No-op: we don't want a rollback of this safety patch to
        // destroy the paid_at data we just backfilled.
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(1) as cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }
};