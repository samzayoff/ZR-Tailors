<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_garment_id')->constrained('order_garments')->cascadeOnDelete();
            $table->unsignedSmallInteger('measurement_point_id');
            // VARCHAR to keep tailor notation e.g. "20.1.4" (20 inches + 1/4)
            $table->string('value', 20)->nullable();

            $table->unique(['order_garment_id', 'measurement_point_id']);
            $table->foreign('measurement_point_id')
                  ->references('id')->on('measurement_points')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_measurements');
    }
};
