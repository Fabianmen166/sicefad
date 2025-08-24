<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->string('quote_id')->primary();
                $table->unsignedBigInteger('user_id')->nullable(); // Temporalmente nullable
                $table->unsignedBigInteger('customer_id')->nullable(); // Temporalmente nullable
                $table->decimal('total', 12, 2)->default(0);
                $table->string('file')->nullable();
                $table->timestamps();

                // Comentando temporalmente las foreign keys
                // $table->foreign('user_id')->references('id')->on('users');
                // $table->foreign('customer_id')->references('customer_id')->on('customers');
            });
        }
    }

    public function down()
    {
        // No eliminar para evitar afectar instalaciones previas
        // Schema::dropIfExists('quotes');
    }
};
