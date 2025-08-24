<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id('customer_id');
                $table->string('applicant');
                $table->string('contact');
                $table->string('phone');
                $table->string('tax_id');
                $table->string('email');
                $table->unsignedBigInteger('customer_type_id');
                $table->timestamps();

                $table->foreign('customer_type_id')
                      ->references('customer_type_id')
                      ->on('customer_types')
                      ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // No borramos la tabla en esta migración duplicada para evitar pérdida de datos
        // Schema::dropIfExists('customers');
    }
};