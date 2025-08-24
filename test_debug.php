<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Entities\HumidityAnalysis;

echo "=== DEBUG CONTROLES ANALÍTICOS ===\n";

// Contar registros
echo "AnalyticalControls: " . AnalyticalControl::count() . "\n";
echo "HumidityAnalyses: " . HumidityAnalysis::count() . "\n";

// Ver el primer análisis de humedad
$humidity = HumidityAnalysis::first();
if ($humidity) {
    echo "\nPrimer análisis de humedad:\n";
    echo "ID: " . $humidity->id . "\n";
    echo "process_id: " . $humidity->process_id . "\n";
    echo "analysis_id: " . $humidity->analysis_id . "\n";
    echo "service_id: " . $humidity->service_id . "\n";
    
    // Buscar controles analíticos
    $controles = AnalyticalControl::where('process_id', $humidity->process_id)
        ->where('analysis_id', $humidity->analysis_id)
        ->where('analysis_type', 'humidity')
        ->get();
    
    echo "\nControles analíticos encontrados: " . $controles->count() . "\n";
    
    foreach ($controles as $control) {
        echo "Control ID: " . $control->id . "\n";
        echo "  - process_id: " . $control->process_id . "\n";
        echo "  - analysis_id: " . $control->analysis_id . "\n";
        echo "  - analysis_type: " . $control->analysis_type . "\n";
        echo "  - masa_suelo: " . ($control->masa_suelo ?? 'NULL') . "\n";
    }
} else {
    echo "No hay análisis de humedad\n";
}

echo "\n=== FIN DEBUG ===\n";

