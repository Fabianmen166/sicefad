<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Entities\ExchangeableBasesAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;

class CheckExchangeableBasesData extends Command
{
    protected $signature = 'check:exchangeable-bases-data';
    protected $description = 'Check exchangeable bases analysis data';

    public function handle()
    {
        $this->info('Checking Exchangeable Bases Analysis Data...');
        
        $analyses = ExchangeableBasesAnalysis::latest()->take(5)->get();
        $controls = AnalyticalControl::latest()->take(5)->get();
        
        $this->info("Total Exchangeable Bases Analyses: " . ExchangeableBasesAnalysis::count());
        $this->info("Total Analytical Controls: " . AnalyticalControl::count());
        
        $this->info("\nLatest Exchangeable Bases Analyses:");
        foreach ($analyses as $analysis) {
            $this->line("- Process: {$analysis->process_id}, Internal Code: {$analysis->internal_code}");
            $this->line("  Na: {$analysis->na_result}, K: {$analysis->k_result}, Ca: {$analysis->ca_result}, Mg: {$analysis->mg_result}");
        }
        
        $this->info("\nLatest Analytical Controls:");
        foreach ($controls as $control) {
            $this->line("- Process: {$control->process_id}");
            if ($control->controles_analiticos) {
                $controles = json_decode($control->controles_analiticos, true);
                if (is_array($controles)) {
                    $this->line("  Controles analíticos: " . count($controles) . " items");
                }
            }
        }
    }
} 