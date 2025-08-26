<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToAcidityAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acidity_analyses', function (Blueprint $table) {
            // Agregar campos necesarios para el sistema de reviews
            if (!Schema::hasColumn('acidity_analyses', 'review_status')) {
                $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending')->after('id');
            }
            if (!Schema::hasColumn('acidity_analyses', 'review_observations')) {
                $table->text('review_observations')->nullable()->after('review_status');
            }
            if (!Schema::hasColumn('acidity_analyses', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_observations');
                $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('acidity_analyses', 'review_date')) {
                $table->timestamp('review_date')->nullable()->after('reviewed_by');
            }
            
            // Agregar campos adicionales que puedan ser necesarios
            if (!Schema::hasColumn('acidity_analyses', 'items_ensayo')) {
                $table->json('items_ensayo')->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('acidity_analyses', 'controles_analiticos')) {
                $table->json('controles_analiticos')->nullable()->after('items_ensayo');
            }
            if (!Schema::hasColumn('acidity_analyses', 'muestra_referencia')) {
                $table->json('muestra_referencia')->nullable()->after('controles_analiticos');
            }
            if (!Schema::hasColumn('acidity_analyses', 'precision_analitica')) {
                $table->json('precision_analitica')->nullable()->after('muestra_referencia');
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
            // Revertir los cambios
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'review_status',
                'review_observations', 
                'reviewed_by',
                'review_date',
                'items_ensayo',
                'controles_analiticos',
                'muestra_referencia',
                'precision_analitica'
            ]);
        });
    }
}
