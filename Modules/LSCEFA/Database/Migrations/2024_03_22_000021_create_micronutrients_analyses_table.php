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
        Schema::create('micronutrients_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id');
            $table->string('consecutivo_no');
            $table->date('fecha_analisis');
            $table->unsignedBigInteger('user_id');

            // Datos de equipo/metodología
            $table->string('equipo_utilizado')->nullable();
            $table->string('intervalo_metodo')->nullable();

            // Contenido principal
            $table->json('controles_analiticos');
            $table->json('precision_analitica')->nullable();
            $table->json('veracidad_analitica')->nullable();
            $table->json('items_ensayo');

            // Observaciones y revisión
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

            $table->timestamps();

            // Foreign keys
            $table->foreign('analysis_id')->references('id')->on('service_process_details')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('micronutrients_analyses');
    }
};


