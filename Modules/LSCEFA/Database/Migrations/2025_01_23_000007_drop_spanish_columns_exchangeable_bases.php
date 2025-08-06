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
            // Eliminar columnas españolas solo si existen
            $columnsToDrop = [
                'codigo_interno',
                'peso_muestra',
                'pw',
                'v_extractante',
                'lectura_blanco',
                'factor_dilucion',
                'bases_cambiables_mg_l',
                'bases_cambiables_mg_kg',
                'observaciones_item',
                'humedad',
                'volumen_final',
                'na_lectura',
                'na_blanco',
                'na_factor',
                'na_resultado',
                'k_lectura',
                'k_blanco',
                'k_factor',
                'k_resultado',
                'ca_lectura',
                'ca_blanco',
                'ca_factor',
                'ca_resultado',
                'mg_lectura',
                'mg_blanco',
                'mg_factor',
                'mg_resultado',
                'observaciones'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('exchangeable_bases_analyses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchangeable_bases_analyses', function (Blueprint $table) {
            // Recrear columnas españolas si es necesario
            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('pw', 8, 4)->nullable();
            $table->decimal('v_extractante', 8, 2)->nullable();
            $table->decimal('lectura_blanco', 8, 4)->nullable();
            $table->decimal('factor_dilucion', 8, 4)->nullable();
            $table->decimal('bases_cambiables_mg_l', 8, 4)->nullable();
            $table->decimal('bases_cambiables_mg_kg', 8, 4)->nullable();
            $table->text('observaciones_item')->nullable();
            $table->decimal('humedad', 8, 4)->nullable();
            $table->decimal('volumen_final', 8, 2)->nullable();
            $table->decimal('na_lectura', 8, 4)->nullable();
            $table->decimal('na_blanco', 8, 4)->nullable();
            $table->decimal('na_factor', 8, 4)->nullable();
            $table->decimal('na_resultado', 8, 4)->nullable();
            $table->decimal('k_lectura', 8, 4)->nullable();
            $table->decimal('k_blanco', 8, 4)->nullable();
            $table->decimal('k_factor', 8, 4)->nullable();
            $table->decimal('k_resultado', 8, 4)->nullable();
            $table->decimal('ca_lectura', 8, 4)->nullable();
            $table->decimal('ca_blanco', 8, 4)->nullable();
            $table->decimal('ca_factor', 8, 4)->nullable();
            $table->decimal('ca_resultado', 8, 4)->nullable();
            $table->decimal('mg_lectura', 8, 4)->nullable();
            $table->decimal('mg_blanco', 8, 4)->nullable();
            $table->decimal('mg_factor', 8, 4)->nullable();
            $table->decimal('mg_resultado', 8, 4)->nullable();
            $table->text('observaciones')->nullable();
        });
    }
}; 