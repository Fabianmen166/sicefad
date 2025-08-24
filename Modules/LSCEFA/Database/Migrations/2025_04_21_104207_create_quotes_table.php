<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->string('quote_id')->primary();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('customer_id');
                $table->decimal('total', 12, 2)->default(0);
                $table->string('file')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('customer_id')->references('customer_id')->on('customers');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // No hacer drop aquí para evitar afectar instalaciones previas
        // Schema::dropIfExists('quotes');
    }
};
