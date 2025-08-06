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
            // Eliminar campos antiguos
            $table->dropColumn([
                'pw',
                'v_extractante',
                'lectura_blanco',
                'factor_dilucion',
                'bases_cambiables_mg_l',
                'bases_cambiables_mg_kg',
                'observaciones_item'
            ]);

            // Agregar campos nuevos
            $table->decimal('humedad', 8, 4)->nullable()->after('peso_muestra');
            $table->decimal('volumen_final', 8, 2)->nullable()->after('humedad');
            
            // Campos para Na
            $table->decimal('na_lectura', 8, 4)->nullable()->after('volumen_final');
            $table->decimal('na_blanco', 8, 4)->nullable()->after('na_lectura');
            $table->decimal('na_factor', 8, 4)->nullable()->after('na_blanco');
            $table->decimal('na_resultado', 8, 4)->nullable()->after('na_factor');
            
            // Campos para K
            $table->decimal('k_lectura', 8, 4)->nullable()->after('na_resultado');
            $table->decimal('k_blanco', 8, 4)->nullable()->after('k_lectura');
            $table->decimal('k_factor', 8, 4)->nullable()->after('k_blanco');
            $table->decimal('k_resultado', 8, 4)->nullable()->after('k_factor');
            
            // Campos para Ca
            $table->decimal('ca_lectura', 8, 4)->nullable()->after('k_resultado');
            $table->decimal('ca_blanco', 8, 4)->nullable()->after('ca_lectura');
            $table->decimal('ca_factor', 8, 4)->nullable()->after('ca_blanco');
            $table->decimal('ca_resultado', 8, 4)->nullable()->after('ca_factor');
            
            // Campos para Mg
            $table->decimal('mg_lectura', 8, 4)->nullable()->after('ca_resultado');
            $table->decimal('mg_blanco', 8, 4)->nullable()->after('mg_lectura');
            $table->decimal('mg_factor', 8, 4)->nullable()->after('mg_blanco');
            $table->decimal('mg_resultado', 8, 4)->nullable()->after('mg_factor');
            
            // Campo de observaciones
            $table->text('observaciones')->nullable()->after('mg_resultado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchangeable_bases_analyses', function (Blueprint $table) {
            // Revertir cambios
            $table->dropColumn([
                'humedad',
                'volumen_final',
                'na_lectura', 'na_blanco', 'na_factor', 'na_resultado',
                'k_lectura', 'k_blanco', 'k_factor', 'k_resultado',
                'ca_lectura', 'ca_blanco', 'ca_factor', 'ca_resultado',
                'mg_lectura', 'mg_blanco', 'mg_factor', 'mg_resultado',
                'observaciones'
            ]);

            // Restaurar campos antiguos
            $table->decimal('pw', 8, 4)->nullable();
            $table->decimal('v_extractante', 8, 2)->nullable();
            $table->decimal('lectura_blanco', 8, 4)->nullable();
            $table->decimal('factor_dilucion', 8, 4)->nullable();
            $table->decimal('bases_cambiables_mg_l', 8, 4)->nullable();
            $table->decimal('bases_cambiables_mg_kg', 8, 4)->nullable();
            $table->text('observaciones_item')->nullable();
        });
    }
}; 