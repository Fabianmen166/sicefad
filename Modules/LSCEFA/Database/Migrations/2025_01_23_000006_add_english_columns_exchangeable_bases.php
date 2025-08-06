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
        Schema::table('exchangeable_bases_analyses', function (Blueprint $table) {
            // Verificar y agregar columnas en inglés solo si no existen
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'internal_code')) {
                $table->string('internal_code')->nullable()->after('analytical_control_id');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'sample_weight')) {
                $table->decimal('sample_weight', 8, 4)->nullable()->after('internal_code');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'moisture')) {
                $table->decimal('moisture', 8, 4)->nullable()->after('sample_weight');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'final_volume')) {
                $table->decimal('final_volume', 8, 2)->nullable()->after('moisture');
            }
            
            // Campos para Na
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'na_reading')) {
                $table->decimal('na_reading', 8, 4)->nullable()->after('final_volume');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'na_blank')) {
                $table->decimal('na_blank', 8, 4)->nullable()->after('na_reading');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'na_factor')) {
                $table->decimal('na_factor', 8, 4)->nullable()->after('na_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'na_result')) {
                $table->decimal('na_result', 8, 4)->nullable()->after('na_factor');
            }
            
            // Campos para K
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'k_reading')) {
                $table->decimal('k_reading', 8, 4)->nullable()->after('na_result');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'k_blank')) {
                $table->decimal('k_blank', 8, 4)->nullable()->after('k_reading');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'k_factor')) {
                $table->decimal('k_factor', 8, 4)->nullable()->after('k_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'k_result')) {
                $table->decimal('k_result', 8, 4)->nullable()->after('k_factor');
            }
            
            // Campos para Ca
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'ca_reading')) {
                $table->decimal('ca_reading', 8, 4)->nullable()->after('k_result');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'ca_blank')) {
                $table->decimal('ca_blank', 8, 4)->nullable()->after('ca_reading');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'ca_factor')) {
                $table->decimal('ca_factor', 8, 4)->nullable()->after('ca_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'ca_result')) {
                $table->decimal('ca_result', 8, 4)->nullable()->after('ca_factor');
            }
            
            // Campos para Mg
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'mg_reading')) {
                $table->decimal('mg_reading', 8, 4)->nullable()->after('ca_result');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'mg_blank')) {
                $table->decimal('mg_blank', 8, 4)->nullable()->after('mg_reading');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'mg_factor')) {
                $table->decimal('mg_factor', 8, 4)->nullable()->after('mg_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'mg_result')) {
                $table->decimal('mg_result', 8, 4)->nullable()->after('mg_factor');
            }
            
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'observations')) {
                $table->text('observations')->nullable()->after('mg_result');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchangeable_bases_analyses', function (Blueprint $table) {
            // Eliminar columnas en inglés
            $table->dropColumn([
                'internal_code', 'sample_weight', 'moisture', 'final_volume',
                'na_reading', 'na_blank', 'na_factor', 'na_result',
                'k_reading', 'k_blank', 'k_factor', 'k_result',
                'ca_reading', 'ca_blank', 'ca_factor', 'ca_result',
                'mg_reading', 'mg_blank', 'mg_factor', 'mg_result',
                'observations'
            ]);
        });
    }
}; 