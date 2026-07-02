<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_garments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('garment_type_id');
            $table->smallInteger('quantity')->default(1);

            $table->unique(['order_id', 'garment_type_id']);
            $table->index('garment_type_id');
            $table->foreign('garment_type_id')
                  ->references('id')->on('garment_types')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_garments');
    }
};
