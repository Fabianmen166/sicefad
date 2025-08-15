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
        Schema::create('texture_analysis_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('texture_analysis_id');
            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_arena', 8, 4)->nullable();
            $table->decimal('peso_limo', 8, 4)->nullable();
            $table->decimal('peso_arcilla', 8, 4)->nullable();
            $table->decimal('peso_total', 8, 4)->nullable();
            $table->decimal('porcentaje_arena', 8, 4)->nullable();
            $table->decimal('porcentaje_limo', 8, 4)->nullable();
            $table->decimal('porcentaje_arcilla', 8, 4)->nullable();
            $table->string('clase_textural')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('texture_analysis_id')->references('id')->on('texture_analyses')->onDelete('cascade');
            $table->index('codigo_interno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('texture_analysis_items');
    }
};
