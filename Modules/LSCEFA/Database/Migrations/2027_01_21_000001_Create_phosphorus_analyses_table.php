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
        // Solo crea la tabla si no existe
        if (!Schema::hasTable('phosphorus_analyses')) {
            Schema::create('phosphorus_analyses', function (Blueprint $table) {
                $table->id();

                // Claves foráneas
                $table->string('process_id'); // Asegúrate de que 'process_id' en 'processes' también sea string
                $table->unsignedBigInteger('service_id');

                // Información general del análisis
                $table->string('consecutivo_no')->nullable();
                $table->date('fecha_analisis');
                $table->string('equipo_utilizado')->nullable();
                $table->string('intervalo_metodo')->nullable();
                $table->string('nombre_analista')->nullable();
                $table->text('observaciones')->nullable();

                // Detalles del ítem de ensayo
                $table->string('codigo_interno')->nullable();
                $table->decimal('peso_muestra', 8, 4)->nullable();
                $table->decimal('pw', 8, 4)->nullable();
                $table->decimal('v_extractante', 8, 2)->nullable();
                $table->decimal('lectura_blanco', 8, 4)->nullable();
                $table->decimal('factor_dilucion', 8, 4)->nullable();
                $table->decimal('fosforo_disponible_mg_l', 8, 4)->nullable();
                $table->decimal('fosforo_disponible_mg_kg', 8, 4)->nullable();
                $table->text('observaciones_item')->nullable();

                $table->timestamps();

                // Relaciones
                $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
                $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phosphorus_analyses');
    }
};
