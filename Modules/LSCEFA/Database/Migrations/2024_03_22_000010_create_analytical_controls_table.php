<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalyticalControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analytical_controls', function (Blueprint $table) {
            $table->id();
             $table->string('process_id');
             $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            
            // Campos de controle analíticos para CIC
            // 1. Blanco método
            $table->string('blanco_identificacion')->nullable();
            $table->decimal('blanco_lcm', 8, 2)->nullable();
            $table->decimal('blanco_valor_leido', 8, 2)->nullable();
            $table->string('blanco_aceptable')->nullable();
            $table->text('blanco_observaciones')->nullable();

            // 2. Control de Laboratorio (CRM/SRM)
            $table->string('error_identificacion')->nullable();
            $table->decimal('error_valor_teorico', 8, 2)->nullable();
            $table->decimal('error_valor_leido', 8, 2)->nullable();
            $table->decimal('error_porcentaje', 8, 2)->nullable();
            $table->string('error_aceptable')->nullable();
            $table->text('error_observaciones')->nullable();

            // 3. Recuperación de Estándar (Spike Recovery)
            $table->string('recuperacion_identificacion')->nullable();
            $table->decimal('recuperacion_valor_teorico', 8, 2)->nullable();
            $table->decimal('recuperacion_valor_leido', 8, 2)->nullable();
            $table->decimal('recuperacion_porcentaje', 8, 2)->nullable();
            $table->string('recuperacion_aceptable')->nullable();
            $table->text('recuperacion_observaciones')->nullable();

            // 4. Duplicados (DPR/RPD)
            $table->string('dpr_identificacion')->nullable();
            $table->decimal('dpr_replica1', 8, 2)->nullable();
            $table->decimal('dpr_replica2', 8, 2)->nullable();
            $table->decimal('dpr_porcentaje', 8, 2)->nullable();
            $table->string('dpr_aceptable')->nullable();
            $table->text('dpr_observaciones')->nullable();
            
            // Campos específicos para controles analíticos de Fósforo
            // 1. Controles Analíticos (Estándar A y B)
            $table->json('controles_analiticos')->nullable();
            
            // 2. DPR de Curva de Calibración
            $table->decimal('dpr_duplicado_a', 8, 4)->nullable();
            $table->decimal('dpr_duplicado_b', 8, 4)->nullable();
            $table->decimal('dpr_resultado', 8, 4)->nullable();
            $table->string('dpr_aceptabilidad')->nullable();
            
            // Campos de curva de calibración
            $table->decimal('curva_valor_leido', 8, 4)->nullable();
            $table->decimal('curva_error_porcentaje', 8, 4)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analytical_controls');
    }
}
