<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('analytical_controls') && !Schema::hasColumn('analytical_controls', 'curva_valor_leido')) {
            Schema::table('analytical_controls', function (Blueprint $table) {
                $table->decimal('curva_valor_leido', 8, 4)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // if (Schema::hasTable('analytical_controls') && Schema::hasColumn('analytical_controls', 'curva_valor_leido')) {
        //     Schema::table('analytical_controls', function (Blueprint $table) {
        //         $table->dropColumn('curva_valor_leido');
        //     });
        // }
    }
};