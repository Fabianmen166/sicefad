<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHumidityAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('humidity_analyses', function (Blueprint $table) {
            $table->id();

           $table->string('process_id'); // Relación con processes
           $table->unsignedBigInteger('service_id')->nullable();  // Relación con services - temporalmente nullable
            $table->unsignedBigInteger('analysis_id')->nullable(); // Relación con service_process_details - temporalmente nullable
            $table->unsignedBigInteger('user_id')->nullable();
           
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis')->nullable();

            // Detalles del horno y equipo
            $table->time('hora_ingreso_horno')->nullable();
            $table->time('hora_salida_horno')->nullable();
            $table->decimal('temperatura_horno', 6, 2)->nullable();
            $table->string('nombre_metodo')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('equipo_utilizado')->nullable();
            $table->string('unidades_reporte_equipo')->nullable();
            $table->string('resolucion_instrumental')->nullable();
            $table->date('fecha_fin_analisis')->nullable();
            $table->string('codigo_interno')->nullable();
            $table->decimal('recuperacion', 8, 4)->nullable();
            $table->decimal('peso_capsula', 8, 4)->nullable();
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('peso_capsula_muestra_humedad', 8, 4)->nullable();
            $table->decimal('peso_capsula_muestra_seca', 8, 4)->nullable();
            $table->decimal('porcentaje_humedad', 6, 2)->nullable();

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
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade'); // Cambiado a services_id
            $table->foreign('analysis_id')->references('id')->on('service_process_details')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('humidity_analyses');
    }
}