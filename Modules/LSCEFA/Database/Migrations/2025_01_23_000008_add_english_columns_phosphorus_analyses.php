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
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            // Verificar y agregar columnas en inglés solo si no existen
            if (!Schema::hasColumn('phosphorus_analyses', 'consecutive_no')) {
                $table->string('consecutive_no')->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'analysis_date')) {
                $table->date('analysis_date')->nullable()->after('consecutive_no');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'equipment_used')) {
                $table->string('equipment_used')->nullable()->after('analysis_date');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'method_interval')) {
                $table->string('method_interval')->nullable()->after('equipment_used');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'analyst_name')) {
                $table->string('analyst_name')->nullable()->after('method_interval');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'observations')) {
                $table->text('observations')->nullable()->after('analyst_name');
            }
            
            // Campos específicos de cada item de ensayo
            if (!Schema::hasColumn('phosphorus_analyses', 'internal_code')) {
                $table->string('internal_code')->nullable()->after('observations');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'sample_weight')) {
                $table->decimal('sample_weight', 8, 4)->nullable()->after('internal_code');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'extractant_volume')) {
                $table->decimal('extractant_volume', 8, 2)->nullable()->after('sample_weight');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'blank_reading')) {
                $table->decimal('blank_reading', 8, 4)->nullable()->after('extractant_volume');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'dilution_factor')) {
                $table->decimal('dilution_factor', 8, 4)->nullable()->after('blank_reading');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'available_phosphorus_mg_l')) {
                $table->decimal('available_phosphorus_mg_l', 8, 4)->nullable()->after('dilution_factor');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'available_phosphorus_mg_kg')) {
                $table->decimal('available_phosphorus_mg_kg', 8, 4)->nullable()->after('available_phosphorus_mg_l');
            }
            if (!Schema::hasColumn('phosphorus_analyses', 'item_observations')) {
                $table->text('item_observations')->nullable()->after('available_phosphorus_mg_kg');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            // Eliminar columnas en inglés
            $table->dropColumn([
                'consecutive_no', 'analysis_date', 'equipment_used', 'method_interval',
                'analyst_name', 'observations', 'internal_code', 'sample_weight',
                'extractant_volume', 'blank_reading', 'dilution_factor',
                'available_phosphorus_mg_l', 'available_phosphorus_mg_kg', 'item_observations'
            ]);
        });
    }
}; 