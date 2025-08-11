<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotes');
    }
}
