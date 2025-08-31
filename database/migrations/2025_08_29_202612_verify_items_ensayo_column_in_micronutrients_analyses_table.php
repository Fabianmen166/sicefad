<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VerifyItemsEnsayoColumnInMicronutrientsAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('micronutrients_analyses', function (Blueprint $table) {
            // Verificar que la columna items_ensayo existe y es de tipo JSON
            if (!Schema::hasColumn('micronutrients_analyses', 'items_ensayo')) {
                $table->json('items_ensayo')->nullable();
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
        Schema::table('micronutrients_analyses', function (Blueprint $table) {
            $table->dropColumn('items_ensayo');
        });
    }
}
