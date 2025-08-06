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
            // Eliminar columnas españolas solo si existen
            $columnsToDrop = [
                'consecutivo_no',
                'fecha_analisis',
                'equipo_utilizado',
                'intervalo_metodo',
                'nombre_analista',
                'observaciones',
                'codigo_interno',
                'peso_muestra',
                'v_extractante',
                'lectura_blanco',
                'factor_dilucion',
                'fosforo_disponible_mg_l',
                'fosforo_disponible_mg_kg',
                'observaciones_item'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('phosphorus_analyses', $column)) {
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
        Schema::table('phosphorus_analyses', function (Blueprint $table) {
            // Recrear columnas españolas si es necesario
            $table->string('consecutivo_no')->nullable();
            $table->date('fecha_analisis')->nullable();
            $table->string('equipo_utilizado')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('nombre_analista')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('codigo_interno')->nullable();
            $table->decimal('peso_muestra', 8, 4)->nullable();
            $table->decimal('v_extractante', 8, 2)->nullable();
            $table->decimal('lectura_blanco', 8, 4)->nullable();
            $table->decimal('factor_dilucion', 8, 4)->nullable();
            $table->decimal('fosforo_disponible_mg_l', 8, 4)->nullable();
            $table->decimal('fosforo_disponible_mg_kg', 8, 4)->nullable();
            $table->text('observaciones_item')->nullable();
        });
    }
}; 