<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormativeProjectFieldsToConductivityAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conductivity_analyses', function (Blueprint $table) {
            // Campos adicionales del proyecto formativo
            $table->string('equipo_utilizado')->nullable()->after('serial_sonda_temperatura');
            $table->string('resolucion_instrumental')->nullable()->after('equipo_utilizado');
            $table->string('unidades_reporte')->nullable()->after('resolucion_instrumental');
            $table->string('intervalo_metodo')->nullable()->after('unidades_reporte');
            $table->json('veracidad_analitica')->nullable()->after('precision_analitica');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conductivity_analyses', function (Blueprint $table) {
            $table->dropColumn([
                'equipo_utilizado',
                'resolucion_instrumental', 
                'unidades_reporte',
                'intervalo_metodo',
                'veracidad_analitica'
            ]);
        });
    }
}
