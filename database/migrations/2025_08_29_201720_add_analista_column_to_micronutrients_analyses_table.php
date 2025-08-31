<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnalistaColumnToMicronutrientsAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('micronutrients_analyses', function (Blueprint $table) {
            $table->string('analista')->nullable()->after('intervalo_metodo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('micronutrients_analyses', function (Blueprint $table) {
            $table->dropColumn('analista');
        });
    }
}
