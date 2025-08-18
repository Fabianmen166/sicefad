<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarbonoAnalysesTable_2025 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('carbono_analyses', function (Blueprint $table) {
        $table->id();

        // Relación con processes
        $table->string('process_id'); // FK a processes.process_id

        // Información general
        $table->string('consecutivo_no')->nullable();
        $table->date('fecha_analisis')->nullable();

        $table->string('unidades_reporte_equipo')->nullable();
        $table->string('nombre_metodo')->nullable();
        $table->string('equipo_utilizado')->nullable();
        $table->string('intervalo_metodo')->nullable();
        $table->string('resolucion_instrumental')->nullable();
        $table->string('codigo_interno')->nullable();
        $table->string('valor_cot_leido')->nullable();
        $table->string('valor_leido')->nullable();
        $table->decimal('porcentaje_humedad', 6, 2)->nullable();
        $table->decimal('peso_muestra', 8, 4)->nullable();

        // Datos específicos de carbono
        $table->decimal('volumen_sulfato_blanco', 8, 2)->nullable();
        $table->decimal('volumen_sulfato_muestra', 8, 2)->nullable();
        $table->decimal('volumen_dicromato', 8, 2)->nullable();
        $table->decimal('molaridad_sulfato', 5, 3)->nullable();
        $table->decimal('porcentaje_co_total', 5, 2)->nullable();
        $table->decimal('porcentaje_cot', 5, 2)->nullable();
        $table->decimal('porcentaje_mo', 5, 2)->nullable();
        $table->decimal('fortificado', 10, 4)->nullable();
        $table->decimal('cot_muestra', 10, 4)->nullable();
        $table->decimal('error_analitico', 10, 4)->nullable();

        $table->text('observaciones')->nullable();

        // Revisión
        $table->string('review_status')->nullable();
        $table->unsignedBigInteger('reviewed_by')->nullable();
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
    Schema::dropIfExists('carbono_analyses');
}

}
