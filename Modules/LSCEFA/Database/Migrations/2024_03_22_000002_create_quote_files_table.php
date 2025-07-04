<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('quote_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_id');
            $table->string('filename');
            $table->string('path');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->timestamps();
            $table->foreign('quote_id')->references('quote_id')->on('quotes')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('quote_files');
    }
}; 