<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('exchangeable_bases_analyses')) {
            return;
        }

        // Eliminar campos antiguos si existen (uno por uno para evitar errores)
        $oldCols = [
            'pw', 'v_extractante', 'lectura_blanco', 'factor_dilucion',
            'bases_cambiables_mg_l', 'bases_cambiables_mg_kg', 'observaciones_item'
        ];
        foreach ($oldCols as $col) {
            if (Schema::hasColumn('exchangeable_bases_analyses', $col)) {
                Schema::table('exchangeable_bases_analyses', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // Agregar campos nuevos si no existen
        $addIfMissing = function (string $col, callable $definition): void {
            if (!Schema::hasColumn('exchangeable_bases_analyses', $col)) {
                Schema::table('exchangeable_bases_analyses', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        };

        $addIfMissing('humedad', fn (Blueprint $t) => $t->decimal('humedad', 8, 4)->nullable()->after('peso_muestra'));
        $addIfMissing('volumen_final', fn (Blueprint $t) => $t->decimal('volumen_final', 8, 2)->nullable()->after('humedad'));
        // Na
        $addIfMissing('na_lectura', fn (Blueprint $t) => $t->decimal('na_lectura', 8, 4)->nullable()->after('volumen_final'));
        $addIfMissing('na_blanco', fn (Blueprint $t) => $t->decimal('na_blanco', 8, 4)->nullable()->after('na_lectura'));
        $addIfMissing('na_factor', fn (Blueprint $t) => $t->decimal('na_factor', 8, 4)->nullable()->after('na_blanco'));
        $addIfMissing('na_resultado', fn (Blueprint $t) => $t->decimal('na_resultado', 8, 4)->nullable()->after('na_factor'));
        // K
        $addIfMissing('k_lectura', fn (Blueprint $t) => $t->decimal('k_lectura', 8, 4)->nullable()->after('na_resultado'));
        $addIfMissing('k_blanco', fn (Blueprint $t) => $t->decimal('k_blanco', 8, 4)->nullable()->after('k_lectura'));
        $addIfMissing('k_factor', fn (Blueprint $t) => $t->decimal('k_factor', 8, 4)->nullable()->after('k_blanco'));
        $addIfMissing('k_resultado', fn (Blueprint $t) => $t->decimal('k_resultado', 8, 4)->nullable()->after('k_factor'));
        // Ca
        $addIfMissing('ca_lectura', fn (Blueprint $t) => $t->decimal('ca_lectura', 8, 4)->nullable()->after('k_resultado'));
        $addIfMissing('ca_blanco', fn (Blueprint $t) => $t->decimal('ca_blanco', 8, 4)->nullable()->after('ca_lectura'));
        $addIfMissing('ca_factor', fn (Blueprint $t) => $t->decimal('ca_factor', 8, 4)->nullable()->after('ca_blanco'));
        $addIfMissing('ca_resultado', fn (Blueprint $t) => $t->decimal('ca_resultado', 8, 4)->nullable()->after('ca_factor'));
        // Mg
        $addIfMissing('mg_lectura', fn (Blueprint $t) => $t->decimal('mg_lectura', 8, 4)->nullable()->after('ca_resultado'));
        $addIfMissing('mg_blanco', fn (Blueprint $t) => $t->decimal('mg_blanco', 8, 4)->nullable()->after('mg_lectura'));
        $addIfMissing('mg_factor', fn (Blueprint $t) => $t->decimal('mg_factor', 8, 4)->nullable()->after('mg_blanco'));
        $addIfMissing('mg_resultado', fn (Blueprint $t) => $t->decimal('mg_resultado', 8, 4)->nullable()->after('mg_factor'));
        // Observaciones
        $addIfMissing('observaciones', fn (Blueprint $t) => $t->text('observaciones')->nullable()->after('mg_resultado'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar/restaurar para evitar afectar instalaciones previas
        // Implementar reversión con las mismas comprobaciones de hasColumn si fuese necesario.
    }
}; 