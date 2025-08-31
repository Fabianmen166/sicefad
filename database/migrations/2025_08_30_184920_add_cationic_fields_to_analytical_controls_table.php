<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            // Campos de blanco (intercambio catiónico)
            $table->string('blanco_identificacion')->nullable()->after('curva_error_porcentaje');
            $table->decimal('blanco_lcm', 8, 2)->nullable()->after('blanco_identificacion');
            $table->decimal('blanco_valor_leido', 8, 2)->nullable()->after('blanco_lcm');
            $table->string('blanco_aceptable')->nullable()->after('blanco_valor_leido');
            $table->text('blanco_observaciones')->nullable()->after('blanco_aceptable');

            // Campos de error (intercambio catiónico)
            $table->string('error_identificacion')->nullable()->after('blanco_observaciones');
            $table->decimal('error_valor_teorico', 8, 2)->nullable()->after('error_identificacion');
            $table->decimal('error_valor_leido', 8, 2)->nullable()->after('error_valor_teorico');
            $table->decimal('error_porcentaje', 8, 2)->nullable()->after('error_valor_leido');
            $table->string('error_aceptable')->nullable()->after('error_porcentaje');
            $table->text('error_observaciones')->nullable()->after('error_aceptable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytical_controls', function (Blueprint $table) {
            $table->dropColumn([
                'blanco_identificacion',
                'blanco_lcm',
                'blanco_valor_leido',
                'blanco_aceptable',
                'blanco_observaciones',
                'error_identificacion',
                'error_valor_teorico',
                'error_valor_leido',
                'error_porcentaje',
                'error_aceptable',
                'error_observaciones',
            ]);
        });
    }
};
