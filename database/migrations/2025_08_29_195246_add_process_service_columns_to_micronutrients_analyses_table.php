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
        Schema::table('micronutrients_analyses', function (Blueprint $table) {
            $table->string('process_id')->nullable()->after('analysis_id');
            $table->unsignedBigInteger('service_id')->nullable()->after('process_id');
            
            // Add foreign keys
            $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
            $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('micronutrients_analyses', function (Blueprint $table) {
            $table->dropForeign(['process_id']);
            $table->dropForeign(['service_id']);
            $table->dropColumn(['process_id', 'service_id']);
        });
    }
};
