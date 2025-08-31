<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodigoInternoToCationicAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cationic_analyses', function (Blueprint $table) {
            $table->string('codigo_interno')->nullable()->after('nombre_analista');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cationic_analyses', function (Blueprint $table) {
            $table->dropColumn('codigo_interno');
        });
    }
}
