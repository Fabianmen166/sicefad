<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchTextureAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_texture_analyses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // General Information
            $table->string('consecutive_no')->nullable();
            $table->date('analysis_date')->nullable();
            $table->string('analyst_name')->nullable();
            $table->string('methodology_used')->nullable();
            $table->string('thermometer_code')->nullable();
            $table->string('hydrometer_code')->nullable();
            $table->string('equipment_used')->nullable();
            $table->string('method_interval')->nullable();
            $table->string('user_id')->nullable();
            $table->string('process_id')->nullable();
            $table->string('service_id')->nullable();

            // Samples (flattened, for batch)
            $table->json('samples')->nullable(); // Store all sample rows as JSON

            // Analytical Controls
            $table->json('analytical_controls')->nullable(); // Store all controls as JSON

            // Precision (Duplicated A & B, DPR, etc.)
            $table->string('duplicate_a_code')->nullable();
            $table->decimal('duplicate_a_avg_sand', 8, 2)->nullable();
            $table->decimal('duplicate_a_avg_clay', 8, 2)->nullable();
            $table->decimal('duplicate_a_avg_silt', 8, 2)->nullable();
            $table->decimal('duplicate_a_dpr_sand', 8, 2)->nullable();
            $table->decimal('duplicate_a_dpr_clay', 8, 2)->nullable();
            $table->decimal('duplicate_a_dpr_silt', 8, 2)->nullable();
            $table->string('duplicate_a_acceptability')->nullable();
            $table->text('duplicate_a_observations')->nullable();
            $table->string('duplicate_b_code')->nullable();
            $table->decimal('duplicate_b_avg_sand', 8, 2)->nullable();
            $table->decimal('duplicate_b_avg_clay', 8, 2)->nullable();
            $table->decimal('duplicate_b_avg_silt', 8, 2)->nullable();
            $table->decimal('duplicate_b_dpr_sand', 8, 2)->nullable();
            $table->decimal('duplicate_b_dpr_clay', 8, 2)->nullable();
            $table->decimal('duplicate_b_dpr_silt', 8, 2)->nullable();
            $table->string('duplicate_b_acceptability')->nullable();
            $table->text('duplicate_b_observations')->nullable();

            // Accuracy (Exactitud)
            $table->decimal('reference_material_expected_sand', 8, 2)->nullable();
            $table->decimal('reference_material_expected_clay', 8, 2)->nullable();
            $table->decimal('reference_material_expected_silt', 8, 2)->nullable();
            $table->decimal('reference_material_obtained_sand', 8, 2)->nullable();
            $table->decimal('reference_material_obtained_clay', 8, 2)->nullable();
            $table->decimal('reference_material_obtained_silt', 8, 2)->nullable();
            $table->decimal('reference_material_error_percent', 8, 2)->nullable();
            $table->string('reference_material_acceptability')->nullable();
            $table->text('reference_material_observations')->nullable();

            // General Observations
            $table->text('general_observations')->nullable();

            // Any additional fields for batch process
            $table->json('extra_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_texture_analyses');
    }
}
