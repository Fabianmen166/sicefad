<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('service_packages')) {
            Schema::create('service_packages', function (Blueprint $table) {
                $table->id('service_package_id');
                $table->string('name');
                $table->decimal('price', 10, 2);
                $table->boolean('accredited')->default(false);
                $table->json('included_services')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('service_packages');
    }
};