<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('conductivity_analyses')) {
            Schema::table('conductivity_analyses', function (Blueprint $table) {
                // Campos adicionales del proyecto formativo con guardas por columna
                if (!Schema::hasColumn('conductivity_analyses', 'equipo_utilizado')) {
                    $table->string('equipo_utilizado')->nullable()->after('serial_sonda_temperatura');
                }
                if (!Schema::hasColumn('conductivity_analyses', 'resolucion_instrumental')) {
                    $table->string('resolucion_instrumental')->nullable()->after('equipo_utilizado');
                }
                if (!Schema::hasColumn('conductivity_analyses', 'unidades_reporte')) {
                    $table->string('unidades_reporte')->nullable()->after('resolucion_instrumental');
                }
                if (!Schema::hasColumn('conductivity_analyses', 'intervalo_metodo')) {
                    $table->string('intervalo_metodo')->nullable()->after('unidades_reporte');
                }
                if (!Schema::hasColumn('conductivity_analyses', 'veracidad_analitica')) {
                    $table->json('veracidad_analitica')->nullable()->after('precision_analitica');
                }
            });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // if (Schema::hasTable('conductivity_analyses')) {
        //     Schema::table('conductivity_analyses', function (Blueprint $table) {
        //         $drop = [];
        //         foreach (['equipo_utilizado','resolucion_instrumental','unidades_reporte','intervalo_metodo','veracidad_analitica'] as $col) {
        //             if (Schema::hasColumn('conductivity_analyses', $col)) { $drop[] = $col; }
        //         }
        //         if (!empty($drop)) { $table->dropColumn($drop); }
        //     });
        // }
    }
};
