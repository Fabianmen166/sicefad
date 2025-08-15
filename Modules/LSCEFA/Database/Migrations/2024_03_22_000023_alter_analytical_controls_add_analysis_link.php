<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            if (!Schema::hasColumn('analytical_controls', 'analysis_type')) {
                $table->string('analysis_type')->nullable()->after('process_id');
            }
            if (!Schema::hasColumn('analytical_controls', 'analysis_id')) {
                $table->unsignedBigInteger('analysis_id')->nullable()->after('analysis_type');
            }
            // No añadimos JSON aquí; el JSON se guardará directo en la tabla de micronutrientes
        });
    }

    public function down(): void
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            if (Schema::hasColumn('analytical_controls', 'analysis_id')) {
                $table->dropColumn('analysis_id');
            }
            if (Schema::hasColumn('analytical_controls', 'analysis_type')) {
                $table->dropColumn('analysis_type');
            }
        });
    }
};


