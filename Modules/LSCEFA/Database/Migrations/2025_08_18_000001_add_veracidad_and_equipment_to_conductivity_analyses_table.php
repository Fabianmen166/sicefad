<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conductivity_analyses', function (Blueprint $table) {
            // Campos de equipo (nuevos)
            if (!Schema::hasColumn('conductivity_analyses', 'equipo_utilizado')) {
                $table->string('equipo_utilizado')->nullable()->after('user_id');
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

            // Veracidad (controles de calidad)
            if (!Schema::hasColumn('conductivity_analyses', 'veracidad_analitica')) {
                $table->json('veracidad_analitica')->nullable()->after('precision_analitica');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conductivity_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('conductivity_analyses', 'veracidad_analitica')) {
                $table->dropColumn('veracidad_analitica');
            }
            if (Schema::hasColumn('conductivity_analyses', 'intervalo_metodo')) {
                $table->dropColumn('intervalo_metodo');
            }
            if (Schema::hasColumn('conductivity_analyses', 'unidades_reporte')) {
                $table->dropColumn('unidades_reporte');
            }
            if (Schema::hasColumn('conductivity_analyses', 'resolucion_instrumental')) {
                $table->dropColumn('resolucion_instrumental');
            }
            if (Schema::hasColumn('conductivity_analyses', 'equipo_utilizado')) {
                $table->dropColumn('equipo_utilizado');
            }
        });
    }
};
