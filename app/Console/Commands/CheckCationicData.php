<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Entities\CationicAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;

class CheckCationicData extends Command
{
    protected $signature = 'check:cationic-data';
    protected $description = 'Check cationic analysis data';

    public function handle()
    {
        $this->info('Checking Cationic Analysis Data...');
        
        $analyses = CationicAnalysis::latest()->take(5)->get();
        $controls = AnalyticalControl::latest()->take(5)->get();
        
        $this->info("Total Cationic Analyses: " . CationicAnalysis::count());
        $this->info("Total Analytical Controls: " . AnalyticalControl::count());
        
        $this->info("\nLatest Cationic Analyses:");
        foreach ($analyses as $analysis) {
            $this->line("- Process: {$analysis->process_id}, Consecutivo: {$analysis->consecutivo_no}, CIC: {$analysis->cic_resultado}");
        }
        
        $this->info("\nLatest Analytical Controls:");
        foreach ($controls as $control) {
            $this->line("- Process: {$control->process_id}, Blanco: {$control->blanco_valor_leido}");
        }
    }
} 