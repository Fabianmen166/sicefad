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
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->unsignedBigInteger('humidity_analysis_id')->nullable()->after('analysis_id');
            $table->foreign('humidity_analysis_id')->references('id')->on('humidity_analyses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->dropForeign(['humidity_analysis_id']);
            $table->dropColumn('humidity_analysis_id');
        });
    }
};
