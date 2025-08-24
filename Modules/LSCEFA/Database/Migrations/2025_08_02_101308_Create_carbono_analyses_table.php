<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('carbono_analyses')) {
            // Tabla pendiente de definir. Se deja protegida para evitar conflictos de clase y duplicados.
            // Schema::create('carbono_analyses', function (Blueprint $table) {
            //     $table->id();
            //     $table->timestamps();
            // });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('carbono_analyses');
    }
};
