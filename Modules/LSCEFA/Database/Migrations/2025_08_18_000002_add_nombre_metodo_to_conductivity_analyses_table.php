<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conductivity_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('conductivity_analyses', 'nombre_metodo')) {
                $table->string('nombre_metodo')->nullable()->after('consecutivo_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conductivity_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('conductivity_analyses', 'nombre_metodo')) {
                $table->dropColumn('nombre_metodo');
            }
        });
    }
};
