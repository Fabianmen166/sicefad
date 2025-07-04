<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerTypesTable extends Migration
{
    public function up()
    {
        Schema::create('customer_types', function (Blueprint $table) {
            $table->id('customer_type_id');
            $table->string('name')->unique();
            $table->decimal('discount_percentage', 5, 2);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_types');
    }
} 