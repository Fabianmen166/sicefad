<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class FileDownloadController extends Controller
{
    public function downloadComprobante($quote_id, $filename, $type = null)
    {
        try {
            // Forzar la escritura del log inmediatamente
            $logMessage = '=== PETICIÓN RECIBIDA EN downloadComprobante ===' . PHP_EOL;
            $logMessage .= 'Hora: ' . now() . PHP_EOL;
            $logMessage .= 'Parámetros: ' . json_encode([
                'quote_id' => $quote_id,
                'filename' => $filename,
                'type' => $type,
                'user_id' => Auth::check() ? Auth::id() : 'guest',
                'url_completa' => request()->fullUrl(),
                'metodo' => request()->method(),
                'ip' => request()->ip()
            ], JSON_PRETTY_PRINT) . PHP_EOL;
            
            // Escribir en un archivo de log
            $logPath = storage_path('logs/download_debug.log');
            file_put_contents($logPath, $logMessage, FILE_APPEND);
            
            // Candidatos de búsqueda (priorizar storage del módulo)
            $candidates = [
                // Nuevo esquema (dentro del módulo)
                module_path('LSCEFA') . '/storage/app/comprobantes/' . $quote_id . '/' . $filename,
                module_path('LSCEFA') . '/storage/app/comprobantes/' . $filename,
                // Antiguo esquema (storage de la app)
                storage_path('app/comprobantes/' . $quote_id . '/' . $filename),
                storage_path('app/comprobantes/' . $filename),
            ];

            $directPath = null;
            foreach ($candidates as $candidate) {
                if ($candidate && file_exists($candidate)) {
                    $directPath = $candidate;
                    break;
                }
            }

            // Búsqueda recursiva como último recurso en ambos lugares
            if (!$directPath) {
                $bases = [
                    module_path('LSCEFA') . '/storage/app/comprobantes',
                    storage_path('app/comprobantes'),
                ];

                $searchFile = function ($dir) use ($filename, &$searchFile) {
                    if (!is_dir($dir)) return null;
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        $path = $dir . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($path)) {
                            $result = $searchFile($path);
                            if ($result) return $result;
                        } elseif ($file === $filename) {
                            return $path;
                        }
                    }
                    return null;
                };

                foreach ($bases as $basePath) {
                    $foundPath = $searchFile($basePath);
                    if ($foundPath) {
                        $directPath = $foundPath;
                        break;
                    }
                }

                if (!$directPath) {
                    $logMessage = 'Archivo no encontrado en ninguna ubicación (comprobantes).\n';
                    file_put_contents($logPath, $logMessage, FILE_APPEND);
                    abort(404, 'El archivo no se encontró en el sistema.');
                }
            }
            
            // Verificar si el archivo existe
            if (!file_exists($directPath)) {
                $logMessage = 'Archivo no encontrado en: ' . $directPath . PHP_EOL;
                file_put_contents($logPath, $logMessage, FILE_APPEND);
                
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no existe en el sistema.',
                    'ruta_buscada' => $directPath
                ], 404);
            }
            
            // Obtener el tipo MIME del archivo
            $mimeType = mime_content_type($directPath);
            
            // Configurar las cabeceras para la descarga forzada
            $headers = [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Expires' => '0',
                'Content-Transfer-Encoding' => 'binary',
            ];
            
            $logMessage = 'Sirviendo archivo desde: ' . $directPath . PHP_EOL;
            file_put_contents($logPath, $logMessage, FILE_APPEND);
            
            // Retornar la respuesta como descarga forzada (conserva extensión original)
            return response()->download($directPath, basename($directPath), $headers);
            
        } catch (\Exception $e) {
            $logMessage = 'Error al descargar el comprobante: ' . $e->getMessage() . PHP_EOL;
            $logMessage .= $e->getTraceAsString() . PHP_EOL;
            file_put_contents($logPath ?? storage_path('logs/download_error.log'), $logMessage, FILE_APPEND);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la descarga: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadCommunication($filename = null)
    {
        try {
            $logPath = storage_path('logs/download_debug.log');
            $log = '=== PETICIÓN RECIBIDA EN downloadCommunication ===' . PHP_EOL;
            $log .= 'Hora: ' . now() . PHP_EOL;
            $log .= 'Filename param (path variable): ' . ($filename ?? 'NULL') . PHP_EOL;
            // Permitir también filename por query string como fallback
            if ($filename === null) {
                $filename = request()->query('filename');
            }
            $log .= 'Filename efectivo: ' . ($filename ?? 'NULL') . PHP_EOL;
            $log .= 'URL: ' . (request()->fullUrl() ?? '-') . PHP_EOL;
            file_put_contents($logPath, $log, FILE_APPEND);

            if (!$filename) {
                abort(400, 'Falta el parámetro filename');
            }

            // Candidatos de búsqueda para archivos de comunicación
            $candidates = [
                module_path('LSCEFA') . '/storage/app/comunicaciones/' . $filename,
                storage_path('app/comunicaciones/' . $filename),
            ];

            $path = null;
            foreach ($candidates as $candidate) {
                if ($candidate && file_exists($candidate)) {
                    $path = $candidate;
                    break;
                }
            }

            if (!$path) {
                // Intentar encontrar por el mismo nombre sin extensión (por si cambió la extensión)
                $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                $tryExtensions = ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg'];
                foreach ([module_path('LSCEFA') . '/storage/app/comunicaciones', storage_path('app/comunicaciones')] as $baseDir) {
                    foreach ($tryExtensions as $ext) {
                        $candidate = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $nameWithoutExt . '.' . $ext;
                        if (file_exists($candidate)) { $path = $candidate; break 2; }
                    }
                }
            }

            if (!$path) {
                // Búsqueda recursiva en ambos lugares
                $bases = [
                    module_path('LSCEFA') . '/storage/app/comunicaciones',
                    storage_path('app/comunicaciones'),
                ];

                $searchFile = function ($dir) use ($filename, &$searchFile) {
                    if (!is_dir($dir)) return null;
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        $p = $dir . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($p)) {
                            $r = $searchFile($p);
                            if ($r) return $r;
                        } elseif ($file === $filename) {
                            return $p;
                        }
                    }
                    return null;
                };

                foreach ($bases as $base) {
                    $found = $searchFile($base);
                    if ($found) { $path = $found; break; }
                }

                if (!$path) {
                    file_put_contents($logPath, 'Archivo de comunicación no encontrado: ' . $filename . PHP_EOL, FILE_APPEND);
                    abort(404, 'El archivo de comunicación no se encontró en el sistema.');
                }
            }

            $mimeType = @mime_content_type($path) ?: 'application/octet-stream';

            file_put_contents($logPath, 'Sirviendo comunicación desde: ' . $path . ' con mime ' . $mimeType . PHP_EOL, FILE_APPEND);

            $headers = [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Expires' => '0',
                'Content-Transfer-Encoding' => 'binary',
            ];

            return response()->download($path, basename($path), $headers);
        } catch (\Exception $e) {
            $logPath = storage_path('logs/download_error.log');
            file_put_contents($logPath, 'Error en downloadCommunication: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            abort(500, 'Error al procesar la descarga.');
        }
    }

    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'txt' => 'text/plain',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}
