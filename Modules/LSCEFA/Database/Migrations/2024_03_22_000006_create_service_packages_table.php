<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicePackagesTable extends Migration
{
    public function up()
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id('service_package_id');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('accredited')->default(false);
            $table->json('included_services')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_packages');
    }
} 