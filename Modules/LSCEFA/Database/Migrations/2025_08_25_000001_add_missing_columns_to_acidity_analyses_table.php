<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acidity_analyses', function (Blueprint $table) {
            // Agregar columna process_id si no existe
            if (!Schema::hasColumn('acidity_analyses', 'process_id')) {
                $table->string('process_id')->after('id');
            }
            
            // Agregar otras columnas faltantes
            if (!Schema::hasColumn('acidity_analyses', 'consecutivo_no')) {
                $table->string('consecutivo_no')->nullable()->after('process_id');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'fecha_analisis')) {
                $table->date('fecha_analisis')->nullable()->after('consecutivo_no');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'unidades_reporte_equipo')) {
                $table->string('unidades_reporte_equipo')->nullable()->after('fecha_analisis');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'nombre_metodo')) {
                $table->string('nombre_metodo')->nullable()->after('unidades_reporte_equipo');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'equipo_utilizado')) {
                $table->string('equipo_utilizado')->nullable()->after('nombre_metodo');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'intervalo_metodo')) {
                $table->string('intervalo_metodo')->nullable()->after('equipo_utilizado');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'resolucion_instrumental')) {
                $table->string('resolucion_instrumental')->nullable()->after('intervalo_metodo');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'codigo_interno')) {
                $table->string('codigo_interno')->nullable()->after('resolucion_instrumental');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'peso_muestra')) {
                $table->decimal('peso_muestra', 8, 4)->nullable()->after('codigo_interno');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'consumido_blanco')) {
                $table->decimal('consumido_blanco', 6, 2)->nullable()->after('peso_muestra');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'molaridad')) {
                $table->decimal('molaridad', 5, 3)->nullable()->after('consumido_blanco');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'porcentaje_humedad')) {
                $table->decimal('porcentaje_humedad', 6, 2)->nullable()->after('molaridad');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'consumido_muestra')) {
                $table->decimal('consumido_muestra', 6, 2)->nullable()->after('porcentaje_humedad');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'acidez')) {
                $table->decimal('acidez', 6, 2)->nullable()->after('consumido_muestra');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'valor_obtenido')) {
                $table->decimal('valor_obtenido', 6, 2)->nullable()->after('acidez');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'valor_referencia')) {
                $table->decimal('valor_referencia', 6, 2)->nullable()->after('valor_obtenido');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'error_analitico')) {
                $table->decimal('error_analitico', 6, 2)->nullable()->after('valor_referencia');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'recuperacion')) {
                $table->decimal('recuperacion', 6, 2)->nullable()->after('error_analitico');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'status')) {
                $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending')->after('recuperacion');
            }
            
            if (!Schema::hasColumn('acidity_analyses', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acidity_analyses', function (Blueprint $table) {
            $table->dropColumn([
                'process_id',
                'consecutivo_no',
                'fecha_analisis',
                'unidades_reporte_equipo',
                'nombre_metodo',
                'equipo_utilizado',
                'intervalo_metodo',
                'resolucion_instrumental',
                'codigo_interno',
                'peso_muestra',
                'consumido_blanco',
                'molaridad',
                'porcentaje_humedad',
                'consumido_muestra',
                'acidez',
                'valor_obtenido',
                'valor_referencia',
                'error_analitico',
                'recuperacion',
                'status',
                'observaciones'
            ]);
        });
    }
};
