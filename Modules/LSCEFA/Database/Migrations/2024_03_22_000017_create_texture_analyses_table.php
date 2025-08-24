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
        Schema::create('texture_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('process_id');
            $table->unsignedBigInteger('service_id');
            $table->string('consecutivo_no');
            $table->date('fecha_analisis');
            $table->string('equipo_utilizado')->nullable();
            $table->string('intervalo_metodo')->nullable();
            $table->string('analista')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

                $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
                $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->index('fecha_analisis');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('texture_analyses');
    }
};
