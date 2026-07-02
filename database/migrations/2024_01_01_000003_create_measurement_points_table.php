<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_points', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedTinyInteger('garment_type_id');
            $table->string('code', 40);
            $table->string('name_en', 60);
            $table->string('name_ur', 60);
            $table->string('icon', 40)->nullable(); // UI icon id
            $table->smallInteger('sort_order')->default(0);

            $table->unique(['garment_type_id', 'code']);
            $table->foreign('garment_type_id')
                  ->references('id')->on('garment_types')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_points');
    }
};
