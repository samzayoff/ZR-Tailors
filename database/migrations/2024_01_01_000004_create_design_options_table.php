<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_options', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->enum('category', ['stitch', 'cuff_kaaj', 'extra', 'style', 'button']);
            $table->string('code', 40);
            $table->string('name_en', 80)->nullable();
            $table->string('name_ur', 80);
            $table->string('icon', 40)->nullable();
            $table->boolean('is_default')->default(false); // pre-ticked on new order
            $table->smallInteger('sort_order')->default(0);

            $table->unique(['category', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_options');
    }
};
