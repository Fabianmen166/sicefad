<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConductivityAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('conductivity_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id');
            $table->string('consecutivo_no');
            $table->date('fecha_analisis');
            $table->unsignedBigInteger('user_id');
            $table->string('codigo_equipo');
            $table->string('serial_conductimetro');
            $table->string('serial_sonda_temperatura');
            $table->json('controles_analiticos');
            $table->json('precision_analitica');
            $table->json('items_ensayo');
            $table->text('observaciones')->nullable();
            $table->string('revisado_por')->nullable();
            $table->date('fecha_revision')->nullable();
            $table->string('aprobado')->nullable();
            $table->text('observaciones_revision')->nullable();
            $table->string('review_status')->default('pending');
            $table->string('reviewed_by')->nullable();
            $table->string('reviewer_role')->nullable();
            $table->dateTime('review_date')->nullable();
            $table->text('review_observations')->nullable();
            
            // Campos de proyecto formativo
            $table->string('formative_project_name')->nullable();
            $table->string('formative_project_code')->nullable();
            $table->string('formative_project_instructor')->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('analysis_id')->references('id')->on('service_process_details')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conductivity_analyses');
    }
} 