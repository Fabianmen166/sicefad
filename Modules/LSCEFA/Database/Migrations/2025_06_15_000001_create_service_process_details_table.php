<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('service_process_details')) {
            Schema::create('service_process_details', function (Blueprint $table) {
                $table->id();
                $table->string('process_id');
                $table->unsignedBigInteger('service_id');
                $table->string('status')->default('pending');
                $table->text('result')->nullable();
                $table->string('file')->nullable();
                $table->text('observations')->nullable();
                $table->timestamps();

                $table->foreign('process_id')->references('process_id')->on('processes')->onDelete('cascade');
                $table->foreign('service_id')->references('services_id')->on('services')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('service_process_details');
    }
};