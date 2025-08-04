<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurvaValorLeidoToAnalyticalControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->decimal('curva_valor_leido', 8, 4)->nullable();
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
            $table->dropColumn('curva_valor_leido');
        });
    }
} 