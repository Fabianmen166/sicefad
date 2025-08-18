<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcidityAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acidity_analyses', function (Blueprint $table) {
            $table->id();

             $table->string('process_id'); // Clave foránea para relacionar con la tabla de processes
           
           $table->string('consecutivo_no')->nullable();
           $table->string('unidades_reporte_equipo')->nullable();
            
            $table->date('fecha_analisis')->nullable();
            $table->string('equipo_utilizado')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('resolucion_instrumental')->nullable();

            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('consumido_blanco', 6, 2)->nullable();
            $table->decimal('molaridad', 5, 3)->nullable();
            $table->decimal('porcentaje_humedad', 6, 2)->nullable();
            $table->decimal('acidez', 6, 2)->nullable();
            $table->decimal('consumido_muestra', 6, 2)->nullable();
            $table->string('valor_referencia',6, 2)->nullable();
            $table->string('valor_obtenido',6, 2)->nullable();
            $table->decimal('error_analitico', 6, 2)->nullable();
            $table->string('observaciones')->nullable();
            $table->string('review_status')->nullable();



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
        Schema::dropIfExists('acidity_analyses');
    }
}
