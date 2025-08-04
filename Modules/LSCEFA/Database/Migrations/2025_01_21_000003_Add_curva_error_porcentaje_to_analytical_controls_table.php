<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurvaErrorPorcentajeToAnalyticalControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->decimal('curva_error_porcentaje', 8, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->dropColumn('curva_error_porcentaje');
        });
    }
} 