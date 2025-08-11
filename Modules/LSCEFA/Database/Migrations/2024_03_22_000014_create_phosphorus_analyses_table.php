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
        Schema::create('phosphorus_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis');
            $table->string('equipo_utilizado')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('nombre_analista')->nullable();
            $table->text('observaciones')->nullable();
            
            // Campos específicos de cada item de ensayo
            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('pw', 8, 4)->nullable();
            $table->decimal('v_extractante', 8, 2)->nullable();
            $table->decimal('lectura_blanco', 8, 4)->nullable();
            $table->decimal('factor_dilucion', 8, 4)->nullable();
            $table->decimal('fosforo_disponible_mg_l', 8, 4)->nullable();
            $table->decimal('fosforo_disponible_mg_kg', 8, 4)->nullable();
            $table->text('observaciones_item')->nullable();
            
            // Columnas en inglés
            $table->string('analysis_date')->nullable();
            $table->string('equipment_used')->nullable();
            $table->string('method_interval')->nullable();
            $table->string('analyst_name')->nullable();
            $table->text('observations')->nullable();
            $table->string('internal_code')->nullable();
            $table->decimal('sample_weight', 8, 4)->nullable();
            $table->decimal('extractant_volume', 8, 2)->nullable();
            $table->decimal('blank_reading', 8, 4)->nullable();
            $table->decimal('dilution_factor', 8, 4)->nullable();
            $table->decimal('available_phosphorus_mg_l', 8, 4)->nullable();
            $table->decimal('available_phosphorus_mg_kg', 8, 4)->nullable();
            $table->text('item_observations')->nullable();
            
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phosphorus_analyses');
    }
}; 