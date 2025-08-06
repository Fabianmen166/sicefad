<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exchangeable_bases_analyses', function (Blueprint $table) {
            // Agregar campos faltantes para Na
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'na_factor')) {
                $table->decimal('na_factor', 8, 4)->nullable()->after('na_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'na_result')) {
                $table->decimal('na_result', 8, 4)->nullable()->after('na_factor');
            }
            
            // Agregar campos faltantes para K
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'k_factor')) {
                $table->decimal('k_factor', 8, 4)->nullable()->after('k_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'k_result')) {
                $table->decimal('k_result', 8, 4)->nullable()->after('k_factor');
            }
            
            // Agregar campos faltantes para Ca
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'ca_factor')) {
                $table->decimal('ca_factor', 8, 4)->nullable()->after('ca_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'ca_result')) {
                $table->decimal('ca_result', 8, 4)->nullable()->after('ca_factor');
            }
            
            // Agregar campos faltantes para Mg
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'mg_factor')) {
                $table->decimal('mg_factor', 8, 4)->nullable()->after('mg_blank');
            }
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'mg_result')) {
                $table->decimal('mg_result', 8, 4)->nullable()->after('mg_factor');
            }
            
            // Agregar campo observations si no existe
            if (!Schema::hasColumn('exchangeable_bases_analyses', 'observations')) {
                $table->text('observations')->nullable()->after('mg_result');
            }
        });
    }

    public function down()
    {
        Schema::table('exchangeable_bases_analyses', function (Blueprint $table) {
            $table->dropColumn([
                'na_factor', 'na_result',
                'k_factor', 'k_result',
                'ca_factor', 'ca_result',
                'mg_factor', 'mg_result',
                'observations'
            ]);
        });
    }
}; 