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
        Schema::create('boron_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            $table->string('consecutive_no')->nullable();
            $table->date('analysis_date');
            $table->string('equipment_used')->nullable();
            $table->string('method_interval')->nullable();
            $table->string('analyst_name')->nullable();
            $table->text('observations')->nullable();
            
            // Campos específicos de cada item de ensayo
            $table->string('internal_code')->nullable();
            $table->decimal('sample_weight', 8, 4)->nullable();
            $table->decimal('pw', 8, 4)->nullable();
            $table->decimal('extractant_volume', 8, 2)->nullable();
            $table->decimal('blank_reading', 8, 4)->nullable();
            $table->decimal('dilution_factor', 8, 4)->nullable();
            $table->decimal('available_boron_mg_l', 8, 4)->nullable();
            $table->decimal('available_boron_mg_kg', 10, 4)->nullable();
            $table->text('item_observations')->nullable();
            
            // Review fields
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('review_observations')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('review_date')->nullable();
            
            $table->timestamps();

            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            
            // Índices para mejorar el rendimiento de las consultas de revisión
            $table->index('review_status');
            $table->index('reviewed_by');
            $table->index('review_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints and indexes first
        Schema::table('boron_analyses', function (Blueprint $table) {
            $table->dropIndex(['review_status']);
            $table->dropIndex(['reviewed_by']);
            $table->dropIndex(['review_date']);
            $table->dropForeign(['reviewed_by']);
        });

        Schema::dropIfExists('boron_analyses');
    }
};
