<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('processes')) {
            Schema::create('processes', function (Blueprint $table) {
                $table->string('process_id')->primary();
                $table->string('quote_id');
                $table->string('item_code');
                $table->string('status')->default('pending');
                $table->text('client_communication');
                $table->string('communication_file')->nullable();
                $table->integer('processing_days');
                $table->date('reception_date')->nullable();
                $table->text('description')->nullable();
                $table->string('sampling_place')->nullable();
                $table->date('sampling_date')->nullable();
                $table->unsignedBigInteger('reception_responsible')->nullable();
                $table->date('delivery_date')->nullable();
                $table->timestamps();

                $table->foreign('quote_id')->references('quote_id')->on('quotes')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('processes');
    }
};