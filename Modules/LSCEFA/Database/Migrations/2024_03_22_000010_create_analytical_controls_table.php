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

            // Campos de controle analíticos
            $table->decimal('masa_suelo', 10, 4)->nullable();
            $table->decimal('masa_agua', 10, 4)->nullable();
            $table->decimal('masa_suelo_seco', 10, 4)->nullable();
            $table->decimal('humedad_fortificada_teorica', 10, 4)->nullable();
            $table->decimal('humedad_obtenida', 10, 4)->nullable();
            $table->decimal('humedad_fortificada', 10, 4)->nullable();
            $table->decimal('recuperacion', 10, 4)->nullable();
            $table->string('valor_referencia')->nullable();
            $table->string('valor_obtenido')->nullable();
            $table->string('valor_leido')->nullable(); // Añadido para consistencia
            $table->string('blanco_metodo')->nullable();
            $table->string('resultado')->nullable();
            $table->string('limite_cuantificacion_metodo')->nullable();
            $table->string('rango_metodo')->nullable();
            $table->decimal('humedad_replica_1', 10, 4)->nullable();
            $table->decimal('humedad_replica_2', 10, 4)->nullable();
            $table->decimal('replica_1', 10, 4)->nullable(); // Añadido para replica 1
            $table->decimal('replica_2', 10, 4)->nullable();
            $table->decimal('dpr', 10, 4)->nullable();
            $table->string('identificacion_mf')->nullable(); // ID para muestra fortificada
            $table->string('identificacion_mr')->nullable(); // ID para muestra de referencia
            $table->string('identificacion_dm')->nullable(); // ID para muestra duplicada
            $table->string('identificacion_bm')->nullable(); // ID para muestra de blanco
            $table->enum('estado', ['Aceptable', 'No Aceptable'])->nullable();
            $table->text('observaciones')->nullable();

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
