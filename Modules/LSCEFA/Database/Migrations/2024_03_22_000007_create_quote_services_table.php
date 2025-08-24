<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quote_services')) {
            Schema::create('quote_services', function (Blueprint $table) {
                $table->id();
                $table->string('quote_id');
                $table->unsignedBigInteger('service_id')->nullable();
                $table->unsignedBigInteger('service_package_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->integer('unit_index')->default(0);
                $table->timestamps();

                $table->foreign('quote_id')->references('quote_id')->on('quotes')->onDelete('cascade');
                $table->foreign('service_id')->references('services_id')->on('services')->onDelete('set null');
                $table->foreign('service_package_id')->references('service_package_id')->on('service_packages')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('quote_services');
    }
};
