<?php

namespace Modules\LSCEFA\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lscefa:cleanup-storage {--days=30 : Días de antigüedad para eliminar archivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia archivos antiguos del storage del módulo LSCEFA';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Limpiando archivos más antiguos de {$days} días...");
        
        // Limpiar comprobantes
        $comprobantesPath = config('lscefa.storage.comprobantes_path');
        $this->cleanupDirectory($comprobantesPath, $cutoffDate, 'comprobantes');
        
        // Limpiar comunicaciones
        $comunicacionesPath = config('lscefa.storage.comunicaciones_path');
        $this->cleanupDirectory($comunicacionesPath, $cutoffDate, 'comunicaciones');
        
        $this->info('Limpieza completada.');
        
        return 0;
    }
    
    /**
     * Limpia un directorio eliminando archivos antiguos
     */
    private function cleanupDirectory($path, $cutoffDate, $type)
    {
        if (!File::exists($path)) {
            $this->warn("Directorio {$type} no existe: {$path}");
            return;
        }
        
        $files = File::allFiles($path);
        $deletedCount = 0;
        
        foreach ($files as $file) {
            $fileDate = Carbon::createFromTimestamp($file->getMTime());
            
            if ($fileDate->lt($cutoffDate)) {
                File::delete($file->getPathname());
                $deletedCount++;
                $this->line("Eliminado: {$file->getFilename()}");
            }
        }
        
        $this->info("Eliminados {$deletedCount} archivos de {$type}");
    }
} 