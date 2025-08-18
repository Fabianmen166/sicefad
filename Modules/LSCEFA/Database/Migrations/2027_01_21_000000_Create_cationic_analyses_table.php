<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCationicAnalysesTable_2027 extends Migration
{
    public function up()
    {
        Schema::create('cationic_analyses', function (Blueprint $table) {
            $table->id();

            $table->string('process_id'); // Clave foránea para relacionar con la tabla de processes
           
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis')->nullable();
            // Usuario que realiza el análisis

            // Detalles del laboratorio y equipo
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->decimal('temperatura_laboratorio', 6, 2)->nullable();
            $table->string('nombre_metodo')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('equipo_utilizado')->nullable();
            $table->string('unidades_reporte_equipo')->nullable();
            $table->string('resolucion_instrumental')->nullable();
            $table->date('fecha_fin_analisis')->nullable();
            $table->string('nombre_analista')->nullable();

            // Parámetros de la muestra
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('vol_naoh_muestra', 8, 2)->nullable();
            $table->decimal('vol_naoh_blanco', 8, 2)->nullable();
            $table->decimal('normalidad_naoh', 8, 2)->nullable();
            $table->decimal('humedad_porcentaje', 8, 2)->nullable();
            $table->decimal('cic_resultado', 8, 2)->nullable();

            // Resultados de cationes intercambiables
            $table->decimal('concentracion_calcio', 8, 2)->nullable();
            $table->decimal('concentracion_magnesio', 8, 2)->nullable();
            $table->decimal('concentracion_sodio', 8, 2)->nullable();
            $table->decimal('concentracion_potasio', 8, 2)->nullable();
            $table->decimal('capacidad_intercambio_cationico', 8, 2)->nullable();

            // Observaciones generales
            $table->text('observaciones')->nullable();

            // Revisión
            $table->string('review_status')->nullable(); // 'pendiente', 'aprobado', 'rechazado'
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Usuario que revisa
            $table->string('reviewer_role')->nullable();
            $table->timestamp('review_date')->nullable();
            $table->text('review_observations')->nullable();

            $table->timestamps();

            // Relaciones
            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
           
        });
    }

    public function down()
    {
        Schema::dropIfExists('cationic_analyses');
    }
} 