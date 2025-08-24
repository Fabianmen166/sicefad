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
        if (!Schema::hasTable('exchangeable_bases_analyses')) {
            Schema::create('exchangeable_bases_analyses', function (Blueprint $table) {
                $table->id();
                $table->string('process_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('analytical_control_id');
                $table->string('codigo_interno')->nullable();
                $table->decimal('peso_muestra', 8, 4)->nullable();
                $table->decimal('pw', 8, 4)->nullable();
                $table->decimal('v_extractante', 8, 2)->nullable();
                $table->decimal('lectura_blanco', 8, 4)->nullable();
                $table->decimal('factor_dilucion', 8, 4)->nullable();
                $table->decimal('bases_cambiables_mg_l', 8, 4)->nullable();
                $table->decimal('bases_cambiables_mg_kg', 8, 4)->nullable();
                $table->text('observaciones_item')->nullable();
                
                $table->timestamps();

                $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
                $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
                $table->foreign('analytical_control_id')->references('id')->on('analytical_controls')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('exchangeable_bases_analyses');
    }
}; 