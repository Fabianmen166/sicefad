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
        Schema::create('boron_analysis_details', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            
            // Datos generales del análisis
            $table->string('consecutive_no')->nullable();
            $table->string('applied_methodology')->nullable();
            $table->string('method_interval')->nullable();
            $table->date('analysis_date');
            $table->string('equipment_used')->nullable();
            $table->string('analyst_name')->nullable();
            
            // Controles analíticos - Estándar A
            $table->string('standard_a_identification')->nullable();
            $table->decimal('standard_a_expected_value', 8, 4)->nullable();
            $table->decimal('standard_a_read_value', 8, 4)->nullable();
            $table->decimal('standard_a_error_percentage', 8, 2)->nullable();
            $table->string('standard_a_error_acceptability')->nullable();
            $table->decimal('standard_a_recovery_percentage', 8, 2)->nullable();
            $table->string('standard_a_recovery_acceptability')->nullable();
            $table->decimal('standard_a_dpr_percentage', 8, 2)->nullable();
            $table->string('standard_a_dpr_acceptability')->nullable();
            
            // Controles analíticos - Estándar B
            $table->string('standard_b_identification')->nullable();
            $table->decimal('standard_b_expected_value', 8, 4)->nullable();
            $table->decimal('standard_b_read_value', 8, 4)->nullable();
            $table->decimal('standard_b_error_percentage', 8, 2)->nullable();
            $table->string('standard_b_error_acceptability')->nullable();
            $table->decimal('standard_b_recovery_percentage', 8, 2)->nullable();
            $table->string('standard_b_recovery_acceptability')->nullable();
            $table->decimal('standard_b_dpr_percentage', 8, 2)->nullable();
            $table->string('standard_b_dpr_acceptability')->nullable();
            
            // Curva de calibración
            $table->decimal('calibration_curve_value', 8, 4)->nullable();
            $table->decimal('calibration_curve_read_value', 8, 4)->nullable();
            $table->decimal('calibration_curve_error_percentage', 8, 2)->nullable();
            $table->string('calibration_curve_acceptability')->nullable();
            
            // Duplicados
            $table->decimal('duplicate_a_value', 8, 4)->nullable();
            $table->decimal('duplicate_b_value', 8, 4)->nullable();
            $table->decimal('duplicate_dpr_percentage', 8, 2)->nullable();
            $table->string('duplicate_dpr_acceptability')->nullable();
            
            // Items de ensayo (JSON para múltiples filas)
            $table->json('test_items')->nullable();
            
            // Observaciones generales
            $table->text('general_observations')->nullable();
            
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
        Schema::dropIfExists('boron_analysis_details');
    }
};
