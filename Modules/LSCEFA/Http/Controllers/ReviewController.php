<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\PhAnalysis;
use Modules\LSCEFA\Models\ConductivityAnalysis;
use Modules\LSCEFA\Models\TurbidityAnalysis;
use Modules\LSCEFA\Models\HardnessAnalysis;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;

class ReviewController extends Controller
{
    // Lista de pendientes de revisión
    public function index(Request $request)
    {
        try {
            // Obtener análisis pendientes de revisión de todos los tipos
            $phAnalyses = PhAnalysis::with([
                    'analysis.process.quote.customer',
                    'analysis.service',
                    'user'
                ])
                ->whereHas('analysis', function($q) {
                    $q->where('status', 'completed');
                })
                ->where('review_status', '!=', 'approved')
                ->where('review_status', '!=', 'rejected');

            $conductivityAnalyses = ConductivityAnalysis::with([
                    'analysis.process.quote.customer',
                    'analysis.service',
                    'user'
                ])
                ->whereHas('analysis', function($q) {
                    $q->where('status', 'completed');
                })
                ->where('review_status', '!=', 'approved')
                ->where('review_status', '!=', 'rejected');

            // Fósforo: registros por ítem (no items_ensayo), relación directa a process/service
            $phosphorusAnalyses = PhosphorusAnalysis::with([
                    'process.quote.customer',
                    'service',
                ]);

            // Evitar error si aún no se ha ejecutado la migración que agrega review_status
            if (Schema::hasColumn('phosphorus_analyses', 'review_status')) {
                $phosphorusAnalyses = $phosphorusAnalyses->where(function($q){
                    $q->whereNull('review_status')
                      ->orWhereNotIn('review_status', ['approved','rejected']);
                });
            }

            // Aplicar filtro de búsqueda si existe
            if ($request->filled('q')) {
                $search = trim($request->get('q'));
                $searchCallback = function($query) use ($search) {
                    $query->where('consecutivo_no', 'like', "%{$search}%")
                          ->orWhereHas('analysis.process.quote.customer', function($q) use ($search) {
                              $q->where('applicant', 'like', "%{$search}%");
                          });
                };

                $phAnalyses->where($searchCallback);
                $conductivityAnalyses->where($searchCallback);
                // Búsqueda para fósforo usando su relación process->quote->customer
                $phosphorusAnalyses->where(function($q) use ($search){
                    $q->where('consecutivo_no', 'like', "%{$search}%")
                      ->orWhereHas('process.quote.customer', function($qq) use ($search){
                          $qq->where('applicant', 'like', "%{$search}%");
                      });
                });
            }

            // Obtener resultados de cada tipo
            $phResults = $phAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'ph');
            });

            $conductivityResults = $conductivityAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'conductivity');
            });

            $phosphorusResults = $phosphorusAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'phosphorus');
            });

            // Combinar todos los resultados
            $allResults = $phResults->concat($conductivityResults)->concat($phosphorusResults);

            // Agrupar por consecutivo_no
            $groupedResults = $allResults->groupBy('consecutivo_no')->map(function($group) {
                $first = $group->first();
                
                // Si solo hay un análisis, mantener sus ítems originales
                if ($group->count() === 1) {
                    $first->analysis_count = 1;
                    $first->analysis_types = [$first->type];
                    // Asegurarse de que items_ensayo sea un array
                    $first->items_ensayo = is_array($first->items_ensayo) ? $first->items_ensayo : [];
                    return $first;
                }
                
                // Para múltiples análisis, combinar solo si son del mismo tipo
                $first->items_ensayo = $group->flatMap(function($item) {
                    return is_array($item->items_ensayo) ? $item->items_ensayo : [];
                })->toArray();
                
                $first->analysis_count = $group->count();
                $first->analysis_types = $group->pluck('type')->unique()->toArray();
                
                return $first;
            });

            // Ordenar por fecha de creación (más reciente primero)
            $sortedResults = $groupedResults->sortByDesc(function($item) {
                return $item->created_at;
            });

            // Paginación manual
            $perPage = 10;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
            $currentPageResults = $sortedResults->slice(($currentPage - 1) * $perPage, $perPage)->values();
            
            $analyses = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageResults,
                $sortedResults->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );

            return view('lscefa::reviews.index', ['allAnalyses' => $analyses]);
            
        } catch (\Exception $e) {
            \Log::error('Error en ReviewController@index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Ocurrió un error al cargar los análisis. Por favor, intente nuevamente.');
        }
    }

    /**
     * Transforma un análisis al formato común
     */
    protected function transformAnalysis($analysis, $type)
    {
        if ($type === 'phosphorus') {
            // Fósforo: cada fila es un item. Construimos items_ensayo sintético
            $process = $analysis->process ?? null;
            $quote = $process->quote ?? null;
            $customer = $quote->customer ?? null;

            $item = [
                'identificacion' => $analysis->codigo_interno ?? 'N/A',
                'valor_leido' => $analysis->fosforo_disponible_mg_kg
                    ?? $analysis->available_phosphorus_mg_kg
                    ?? $analysis->available_phosphorus_mg_l
                    ?? $analysis->fosforo_disponible_mg_l
                    ?? null,
                'observaciones' => $analysis->observaciones_item ?? '',
            ];

            return (object) [
                'id' => $analysis->id,
                'type' => $type,
                'analysis_id' => null,
                'consecutivo_no' => $analysis->consecutivo_no,
                'fecha_analisis' => $analysis->fecha_analisis,
                'codigo_probeta' => null,
                'codigo_equipo' => $analysis->equipo_utilizado ?? null,
                'review_status' => $analysis->review_status ?? 'pending',
                'user' => null,
                'process' => $process,
                'quote' => $quote,
                'customer' => $customer,
                'first_item' => (object)$item,
                'created_at' => $analysis->created_at,
                'items_ensayo' => [$item],
            ];
        } else {
            $items = is_array($analysis->items_ensayo) ? $analysis->items_ensayo : [];
            $firstItem = !empty($items) ? (object)$items[0] : null;
            $process = $analysis->analysis->process ?? null;
            $quote = $process->quote ?? null;
            $customer = $quote->customer ?? null;

            return (object)[
                'id' => $analysis->id,
                'type' => $type,
                'analysis_id' => $analysis->analysis_id,
                'consecutivo_no' => $analysis->consecutivo_no,
                'fecha_analisis' => $analysis->fecha_analisis,
                'codigo_probeta' => $analysis->codigo_probeta,
                'codigo_equipo' => $analysis->codigo_equipo,
                'review_status' => $analysis->review_status ?? 'pending',
                'user' => $analysis->user,
                'process' => $process,
                'quote' => $quote,
                'customer' => $customer,
                'first_item' => $firstItem,
                'created_at' => $analysis->created_at,
                'items_ensayo' => $items
            ];
        }
    }

    // Ver detalle de un análisis para revisión
    public function show($id)
    {
        // Primero intentamos encontrar el análisis considerando el tipo forzado por query string
        $analysis = null;
        $type = null;
        $forcedType = request('type');

        if ($forcedType === 'ph') {
            $phAnalysis = PhAnalysis::with([
                'analysis.process.quote', 
                'analysis.service',
                'analysis.process.customer',
                'analysis.process.quote.customer',
                'user'
            ])->find($id);
            if ($phAnalysis) {
                $analysis = $phAnalysis;
                $type = 'ph';
            }
        } elseif ($forcedType === 'conductivity') {
            $conductivityAnalysis = ConductivityAnalysis::with([
                'analysis.process.quote',
                'analysis.service',
                'analysis.process.customer',
                'analysis.process.quote.customer',
                'user'
            ])->find($id);
            if ($conductivityAnalysis) {
                $analysis = $conductivityAnalysis;
                $type = 'conductivity';
            }
        } elseif ($forcedType === 'phosphorus') {
            $pAnalysis = PhosphorusAnalysis::with([
                'process.quote',
                'process.customer',
                'service',
            ])->find($id);
            if ($pAnalysis) {
                $analysis = $pAnalysis;
                $type = 'phosphorus';
            }
        } elseif ($type === 'conductivity') {
            // Normalizar estructuras para conductividad
            if (isset($analysis->controles_analiticos)) {
                if (is_string($analysis->controles_analiticos)) {
                    $controles_analiticos = json_decode($analysis->controles_analiticos, true) ?? [];
                } elseif (is_array($analysis->controles_analiticos)) {
                    $controles_analiticos = $analysis->controles_analiticos;
                } elseif (is_object($analysis->controles_analiticos)) {
                    $controles_analiticos = (array)$analysis->controles_analiticos;
                }
                // Asegurar array indexado y elementos como arrays
                $controles_analiticos = array_values($controles_analiticos);
                foreach ($controles_analiticos as $k => $ctrl) {
                    if (is_object($ctrl)) { $controles_analiticos[$k] = (array)$ctrl; }
                }
            }

            if (isset($analysis->precision_analitica)) {
                if (is_string($analysis->precision_analitica)) {
                    $precision_analitica = json_decode($analysis->precision_analitica, true) ?? [];
                } elseif (is_array($analysis->precision_analitica)) {
                    $precision_analitica = $analysis->precision_analitica;
                } elseif (is_object($analysis->precision_analitica)) {
                    $precision_analitica = (array)$analysis->precision_analitica;
                }
            }

            // Fallback: si no hay veracidad_analitica pero existe campo legado 'veracidad', usarlo
            if ((empty($analysis->veracidad_analitica) || (is_array($analysis->veracidad_analitica) && count($analysis->veracidad_analitica) === 0))
                && isset($analysis->veracidad)) {
                $legacy = $analysis->veracidad;
                if (is_string($legacy)) { $legacy = json_decode($legacy, true) ?? []; }
                if (is_object($legacy)) { $legacy = (array)$legacy; }
                $legacy = array_values($legacy);
                foreach ($legacy as $k => $v) { if (is_object($v)) { $legacy[$k] = (array)$v; } }
                $analysis->veracidad_analitica = $legacy;
            }

            if (isset($analysis->veracidad_analitica)) {
                if (is_string($analysis->veracidad_analitica)) {
                    $veracidad = json_decode($analysis->veracidad_analitica, true) ?? [];
                } elseif (is_array($analysis->veracidad_analitica)) {
                    $veracidad = $analysis->veracidad_analitica;
                } elseif (is_object($analysis->veracidad_analitica)) {
                    $veracidad = (array)$analysis->veracidad_analitica;
                }
                // Forzar índices numéricos y normalizar elementos
                $veracidad = array_values($veracidad);
                foreach ($veracidad as $k => $v) {
                    if (is_object($v)) { $veracidad[$k] = (array)$v; }
                }
                $analysis->veracidad_analitica = $veracidad;
            }
        }

        // Fallback: si no se pasó type o no se encontró en el tipo forzado, usar detección automática
        if (!$analysis) {
            // Buscar en análisis de pH
            $phAnalysis = PhAnalysis::with([
                'analysis.process.quote', 
                'analysis.service',
                'analysis.process.customer',
                'analysis.process.quote.customer',
                'user'
            ])->find($id);
            
            if ($phAnalysis) {
                $analysis = $phAnalysis;
                $type = 'ph';
            } else {
                // Buscar en análisis de conductividad
                $conductivityAnalysis = ConductivityAnalysis::with([
                    'analysis.process.quote',
                    'analysis.service',
                    'analysis.process.customer',
                    'analysis.process.quote.customer',
                    'user'
                ])->find($id);
                
                if ($conductivityAnalysis) {
                    $analysis = $conductivityAnalysis;
                    $type = 'conductivity';
                } else {
                    $pAnalysis = PhosphorusAnalysis::with([
                        'process.quote',
                        'process.customer',
                        'service',
                    ])->find($id);
                    if ($pAnalysis) {
                        $analysis = $pAnalysis;
                        $type = 'phosphorus';
                    }
                }
            }
        }
        
        if (!$analysis) {
            return redirect()->route('lscefa.quality.reviews.index')
                ->with('error', 'No se encontró el análisis solicitado.');
        }
        
        if ($type === 'phosphorus') {
            $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
            $process = $analysis->process ?? ($detail->process ?? null);
            $quote = $process->quote ?? null;
            $customer = $process->customer ?? ($quote->customer ?? null);
            // Cargar controles analíticos ligados al proceso (si existen)
            try {
                $analyticalControl = AnalyticalControl::where('process_id', $analysis->process_id)->first();
            } catch (\Throwable $e) {
                \Log::warning('No se pudo cargar AnalyticalControl en ReviewController@show (phosphorus): ' . $e->getMessage());
                $analyticalControl = null;
            }
        } else {
            $detail = $analysis->analysis;
            $process = $detail->process ?? null;
            $quote = $process->quote ?? null;
            $customer = $process->customer ?? ($quote->customer ?? null);
        }
        
        // Resolver nombre del técnico responsable con fallback
        $technicianName = null;
        try {
            if (isset($analysis->user) && $analysis->user) {
                $technicianName = $analysis->user->nickname ?? $analysis->user->name ?? null;
            }
            if (!$technicianName && !empty($analysis->user_id)) {
                $user = \App\Models\User::find($analysis->user_id);
                $technicianName = $user->nickname ?? $user->name ?? null;
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo resolver el técnico responsable en ReviewController@show: ' . $e->getMessage());
        }
        
        // Determinar qué vista usar según el tipo de análisis
        $view = match($type) {
            'ph' => 'lscefa::reviews.ph_review',
            'conductivity' => 'lscefa::reviews.conductivity_show',
            'phosphorus' => 'lscefa::reviews.phosphorus_review',
            default => 'lscefa::reviews.ph_review'
        };
        
        // Asegurarse de que los ítems de ensayo estén disponibles en la vista
        $items_ensayo = [];
        
        // Función auxiliar para normalizar un item a estructura común
        $normalizeItem = function($item) {
            if (is_object($item)) { $item = (array)$item; }
            if (!is_array($item)) { return null; }
            return [
                'identificacion' => $item['identificacion'] ?? 'N/A',
                'peso' => $item['peso'] ?? 'N/A',
                'volumen_agua' => $item['volumen_agua'] ?? 'N/A',
                'temperatura' => $item['temperatura'] ?? 'N/A',
                'valor_leido' => $item['valor_leido'] ?? 'N/A',
                'observaciones' => $item['observaciones'] ?? ''
            ];
        };

        // 1) Agregar los items del análisis actual (si existen)
        if ($type === 'phosphorus') {
            // Para fósforo, construir ítem a partir de la fila
            $norm = $normalizeItem([
                'identificacion' => $analysis->codigo_interno ?? 'N/A',
                'valor_leido' => $analysis->fosforo_disponible_mg_kg
                    ?? $analysis->available_phosphorus_mg_kg
                    ?? $analysis->available_phosphorus_mg_l
                    ?? $analysis->fosforo_disponible_mg_l
                    ?? null,
                'observaciones' => $analysis->observaciones_item ?? '',
            ]);
            if ($norm !== null) { $items_ensayo[] = $norm; }
        } elseif (isset($analysis->items_ensayo) && is_array($analysis->items_ensayo)) {
            foreach ($analysis->items_ensayo as $item) {
                $norm = $normalizeItem($item);
                if ($norm !== null) { $items_ensayo[] = $norm; }
            }
        }

        // Determinar consecutivo efectivo (permite filtrar por query string en la vista de pH)
        $effectiveConsecutivo = null;
        try {
            $effectiveConsecutivo = request('consecutivo') ?: ($analysis->consecutivo_no ?? null);
        } catch (\Throwable $e) {
            $effectiveConsecutivo = $analysis->consecutivo_no ?? null;
        }

        // 2) Si es pH, agregar también los items de ensayo de todos los análisis con el mismo consecutivo (efectivo)
        if ($type === 'ph' && !empty($effectiveConsecutivo)) {
            try {
                $siblings = PhAnalysis::where('consecutivo_no', $effectiveConsecutivo)
                    ->where('id', '!=', $analysis->id)
                    ->get();
                foreach ($siblings as $sib) {
                    if (isset($sib->items_ensayo) && is_array($sib->items_ensayo)) {
                        foreach ($sib->items_ensayo as $item) {
                            $norm = $normalizeItem($item);
                            if ($norm !== null) { $items_ensayo[] = $norm; }
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('No se pudieron cargar items_ensayo hermanos por consecutivo en ReviewController@show: ' . $e->getMessage());
            }
        }
        // Para fósforo, agregar hermanos por mismo consecutivo_no
        if ($type === 'phosphorus' && !empty($analysis->consecutivo_no)) {
            try {
                $siblings = PhosphorusAnalysis::where('consecutivo_no', $analysis->consecutivo_no)
                    ->where('id', '!=', $analysis->id)
                    ->get();
                foreach ($siblings as $sib) {
                    $norm = $normalizeItem([
                        'identificacion' => $sib->codigo_interno ?? 'N/A',
                        'valor_leido' => $sib->fosforo_disponible_mg_kg
                            ?? $sib->available_phosphorus_mg_kg
                            ?? $sib->available_phosphorus_mg_l
                            ?? $sib->fosforo_disponible_mg_l
                            ?? null,
                        'observaciones' => $sib->observaciones_item ?? '',
                    ]);
                    if ($norm !== null) { $items_ensayo[] = $norm; }
                }
            } catch (\Throwable $e) {
                \Log::warning('No se pudieron cargar items fósforo hermanos por consecutivo en ReviewController@show: ' . $e->getMessage());
            }
        }
        
        // Si es un análisis de pH, asegurarse de que los controles analíticos y muestra de referencia estén en el formato correcto
        $controles_analiticos = [];
        $muestra_referencia = [];
        $precision_analitica = [];
        
        if ($type === 'ph') {
            // Procesar controles analíticos
            if (isset($analysis->controles_analiticos)) {
                if (is_string($analysis->controles_analiticos)) {
                    // Si es un string JSON, decodificarlo
                    $controles_analiticos = json_decode($analysis->controles_analiticos, true) ?? [];
                } elseif (is_array($analysis->controles_analiticos)) {
                    $controles_analiticos = $analysis->controles_analiticos;
                } elseif (is_object($analysis->controles_analiticos)) {
                    $controles_analiticos = (array)$analysis->controles_analiticos;
                }
                
                // Asegurar que sea un array indexado numéricamente
                $controles_analiticos = array_values($controles_analiticos);

                // Normalizar cada elemento a array (por si viniera como objeto)
                foreach ($controles_analiticos as $k => $ctrl) {
                    if (is_object($ctrl)) {
                        $controles_analiticos[$k] = (array) $ctrl;
                    }
                }
            }
            
            // Procesar precisión analítica
            if (isset($analysis->precision_analitica)) {
                if (is_string($analysis->precision_analitica)) {
                    $precision_analitica = json_decode($analysis->precision_analitica, true) ?? [];
                } elseif (is_array($analysis->precision_analitica)) {
                    $precision_analitica = $analysis->precision_analitica;
                } elseif (is_object($analysis->precision_analitica)) {
                    $precision_analitica = (array)$analysis->precision_analitica;
                }
                
                // Asegurar que los duplicados estén en el formato correcto
                if (isset($precision_analitica['duplicados'])) {
                    $duplicados = $precision_analitica['duplicados'];
                    if (is_string($duplicados)) {
                        $duplicados = json_decode($duplicados, true) ?? [];
                    }
                    $precision_analitica['duplicados'] = $duplicados;
                }
                
                // Asegurar que las réplicas estén en el formato correcto
                if (isset($precision_analitica['replicas'])) {
                    $replicas = $precision_analitica['replicas'];
                    if (is_string($replicas)) {
                        $replicas = json_decode($replicas, true) ?? [];
                    }
                    $precision_analitica['replicas'] = $replicas;
                }
            }
            
            // Procesar muestra de referencia (normalmente es el cuarto elemento de controles_analiticos)
            if (isset($analysis->muestra_referencia)) {
                if (is_string($analysis->muestra_referencia)) {
                    $muestra_referencia = json_decode($analysis->muestra_referencia, true) ?? [];
                } elseif (is_array($analysis->muestra_referencia)) {
                    $muestra_referencia = $analysis->muestra_referencia;
                } elseif (is_object($analysis->muestra_referencia)) {
                    $muestra_referencia = (array)$analysis->muestra_referencia;
                }
            } elseif (isset($controles_analiticos[3])) { 
                // Si no existe muestra_referencia pero sí existe el cuarto control analítico
                $muestra_referencia = is_array($controles_analiticos[3]) 
                    ? $controles_analiticos[3] 
                    : (array)$controles_analiticos[3];
                    
                // Asegurarse de que tenga los campos necesarios
                $muestra_referencia = array_merge([
                    'identificacion' => 'Muestra de referencia o MRC',
                    'lote' => '',
                    'peso' => '',
                    'volumen_agua' => '',
                    'temperatura' => '',
                    'valor_leido' => '',
                    'valor_esperado' => '',
                    'aceptable' => false,
                    'observaciones' => ''
                ], $muestra_referencia);
                
                // Agregar a los logs para depuración
                \Log::info('Muestra de referencia obtenida del control analítico 3:', $muestra_referencia);
            }
        }
        
        // Add debug logging
        \Log::info('Analysis Data Structure:', [
            'has_muestra_referencia' => isset($analysis->muestra_referencia) ? 'Yes' : 'No',
            'muestra_referencia_type' => gettype($analysis->muestra_referencia ?? 'null'),
            'muestra_referencia_data' => $analysis->muestra_referencia ?? 'Not set',
            'has_controles_analiticos' => isset($analysis->controles_analiticos) ? 'Yes' : 'No',
            'controles_analiticos_type' => gettype($analysis->controles_analiticos ?? 'null'),
            'controles_analiticos_count' => is_array($analysis->controles_analiticos ?? null) 
                ? count($analysis->controles_analiticos) 
                : (is_object($analysis->controles_analiticos ?? null) 
                    ? count((array)$analysis->controles_analiticos) 
                    : 'N/A'),
            'has_veracidad_analitica' => isset($analysis->veracidad_analitica) ? 'Yes' : 'No',
            'veracidad_analitica_type' => gettype($analysis->veracidad_analitica ?? 'null'),
            'veracidad_analitica_count' => is_array($analysis->veracidad_analitica ?? null)
                ? count($analysis->veracidad_analitica)
                : (is_object($analysis->veracidad_analitica ?? null)
                    ? count((array)$analysis->veracidad_analitica)
                    : 'N/A'),
        ]);
        
        // Calcular promedios y estadísticas si es necesario
        $estadisticas = [];
        if (!empty($items_ensayo)) {
            $totalPh = 0;
            $countPh = 0;
            
            foreach ($items_ensayo as $item) {
                if (isset($item['valor_leido']) && is_numeric($item['valor_leido'])) {
                    $totalPh += $item['valor_leido'];
                    $countPh++;
                }
            }
            
            if ($countPh > 0) {
                $estadisticas['promedio_ph'] = $totalPh / $countPh;
            }
        }
        
        return view($view, [
            'detail' => $detail,
            'analysis' => $analysis,
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'readonly' => false,
            'items_ensayo' => $items_ensayo,
            'controles_analiticos' => $controles_analiticos,
            'muestra_referencia' => $muestra_referencia, // Asegurarse de pasar muestra_referencia
            'precision_analitica' => $precision_analitica, // Asegurarse de pasar precision_analitica
            'estadisticas' => $estadisticas,
            'technicianName' => $technicianName,
            'effectiveConsecutivo' => $effectiveConsecutivo,
            'type' => $type,
            // Solo para fósforo: pasar controles analíticos a la vista si se cargaron
            'analyticalControl' => isset($analyticalControl) ? $analyticalControl : null,
        ]);
    }

    // Ver detalle en modo solo lectura
    public function showDetail(ServiceProcessDetail $detail)
    {
        $detail->load(['process.quote', 'service']);
        
        // Determinar el tipo de análisis
        $serviceName = strtolower($detail->service->descripcion ?? '');
        
        if (str_contains($serviceName, 'ph') || str_contains($serviceName, 'potencial')) {
            $model = 'PhAnalysis';
            $view = 'lscefa::ph_analyses.review';
        } elseif (str_contains($serviceName, 'conductividad')) {
            $model = 'ConductivityAnalysis';
            $view = 'lscefa::reviews.conductivity_show';
        } else {
            return view('lscefa::reviews.generic_show', compact('detail'));
        }
        
        // Cargar el análisis específico
        $analysis = $detail->{$model};
        
        if (!$analysis) {
            return redirect()->back()->with('error', 'No se encontraron datos del análisis.');
        }
        
        return view($view, [
            'detail' => $detail,
            'analysis' => $analysis,
            'readonly' => true // Para indicar que es modo solo lectura
        ]);
    }

    // Ver detalle de un proceso con todos sus servicios completados
    public function showProcess(Process $process)
    {
        // Cargar todos los detalles del proceso con sus análisis relacionados
        $process->load([
            'serviceProcessDetails' => function($q) {
                $q->with([
                    'service', 
                    'phAnalysis', 
                    'conductivityAnalysis',
                    'phAnalysis.user', // Asegurar que se cargue el usuario que realizó el análisis
                    'conductivityAnalysis.user' // Asegurar que se cargue el usuario que realizó el análisis
                ]);
            },
            'customer',
            'quote'
        ]);

        // Agrupar los análisis por tipo
        $analyses = [
            'ph' => [],
            'conductivity' => []
        ];

        // Recorrer todos los detalles del proceso
        foreach ($process->serviceProcessDetails as $detail) {
            // Verificar si es un análisis de pH
            if ($detail->phAnalysis) {
                $analyses['ph'][] = [
                    'detail' => $detail,
                    'analysis' => $detail->phAnalysis,
                    'service' => $detail->service
                ];
            } 
            // Verificar si es un análisis de conductividad
            if ($detail->conductivityAnalysis) {
                $analyses['conductivity'][] = [
                    'detail' => $detail,
                    'analysis' => $detail->conductivityAnalysis,
                    'service' => $detail->service
                ];
            }
        }

        // Verificar si hay análisis para mostrar
        if (empty($analyses['ph']) && empty($analyses['conductivity'])) {
            return redirect()->route('lscefa.quality.reviews.index')
                ->with('error', 'No se encontraron análisis para revisar en este proceso.');
        }

        return view('lscefa::reviews.process_show', compact('process', 'analyses'));
    }

    // Aceptar reporte
    public function accept(Request $request, $id)
    {
        $validated = $request->validate([
            'observations' => ['nullable', 'string', 'max:5000'],
            'analysis_type' => ['required', 'in:ph,conductivity,phosphorus'],
        ]);

        // Buscar el análisis específico según el tipo
        if ($validated['analysis_type'] === 'ph') {
            $analysis = PhAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'conductivity') {
            $analysis = ConductivityAnalysis::findOrFail($id);
        } else { // phosphorus
            $analysis = PhosphorusAnalysis::findOrFail($id);
        }

        // Actualizar el estado de revisión
        $analysis->review_status = 'approved';
        if (array_key_exists('observations', $validated)) {
            $analysis->review_observations = $validated['observations'];
        }
        $analysis->reviewed_by = auth()->id();
        $analysis->review_date = now();
        $analysis->save();

        // Persistir solo los resultados por ítem en service_process_details.result
        try {
            if ($validated['analysis_type'] === 'phosphorus') {
                // Buscar detail por process_id + service_id
                $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                    ->where('service_id', $analysis->service_id)
                    ->first();
                if ($detail) {
                    $rows = PhosphorusAnalysis::where('process_id', $analysis->process_id)
                        ->where('service_id', $analysis->service_id)
                        ->where('consecutivo_no', $analysis->consecutivo_no)
                        ->get();
                    $resultsOnly = $rows->map(function($row){
                        $resultado = $row->fosforo_disponible_mg_kg
                            ?? $row->available_phosphorus_mg_kg
                            ?? $row->available_phosphorus_mg_l
                            ?? $row->fosforo_disponible_mg_l
                            ?? null;
                        return [
                            'identificacion' => $row->codigo_interno ?? null,
                            'resultado' => $resultado,
                        ];
                    })->values()->toArray();
                    $detail->result = json_encode($resultsOnly, JSON_UNESCAPED_UNICODE);
                    $detail->save();
                }
            } else {
                $detail = $analysis->analysis; // ServiceProcessDetail
                if ($detail) {
                    $resultsOnly = [];
                    $items = is_array($analysis->items_ensayo ?? null) ? $analysis->items_ensayo : [];
                    if ($validated['analysis_type'] === 'ph') {
                        foreach ($items as $item) {
                            if (is_object($item)) { $item = (array)$item; }
                            $resultsOnly[] = [
                                'identificacion' => $item['identificacion'] ?? null,
                                'resultado' => $item['valor_leido'] ?? null,
                            ];
                        }
                    } else { // conductivity
                        foreach ($items as $item) {
                            if (is_object($item)) { $item = (array)$item; }
                            $resultado = $item['valor_leido'] ?? ($item['lectura_uscm'] ?? ($item['valor_leido_dsm'] ?? null));
                            $resultsOnly[] = [
                                'identificacion' => $item['identificacion'] ?? null,
                                'resultado' => $resultado,
                            ];
                        }
                    }
                    $detail->result = json_encode($resultsOnly, JSON_UNESCAPED_UNICODE);
                    $detail->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo persistir result en ServiceProcessDetail al aprobar análisis: ' . $e->getMessage());
        }

        // Verificar si todos los análisis del detalle han sido aprobados
        if ($validated['analysis_type'] === 'phosphorus') {
            $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
        } else {
            $detail = $analysis->analysis;
        }
        $allApproved = true;
        
        if ($detail) {
            // Verificar análisis de pH
            if ($detail->phAnalysis && $detail->phAnalysis->id != $analysis->id) {
                if ($detail->phAnalysis->review_status !== 'approved') {
                    $allApproved = false;
                }
            }
            
            // Verificar análisis de conductividad
            if ($detail->conductivityAnalysis && $detail->conductivityAnalysis->id != $analysis->id) {
                if ($detail->conductivityAnalysis->review_status !== 'approved') {
                    $allApproved = false;
                }
            }
            
            // Si todos los análisis están aprobados, marcar el detalle como aprobado
            if ($allApproved) {
                $detail->status = 'approved';
                $detail->save();
            }
        }

        return redirect()
            ->route('lscefa.quality.reviews.index')
            ->with('success', 'Análisis aprobado correctamente.');
    }

    // Rechazar reporte (requiere observaciones)
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'observations' => ['required', 'string', 'min:3', 'max:5000'],
            'analysis_type' => ['required', 'in:ph,conductivity,phosphorus'],
        ], [
            'observations.required' => 'Debe ingresar observaciones para rechazar el análisis.',
            'observations.min' => 'Las observaciones deben tener al menos :min caracteres.',
        ]);

        // Buscar el análisis específico según el tipo
        if ($validated['analysis_type'] === 'ph') {
            $analysis = PhAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'conductivity') {
            $analysis = ConductivityAnalysis::findOrFail($id);
        } else { // phosphorus
            $analysis = PhosphorusAnalysis::findOrFail($id);
        }

        // Actualizar el estado de revisión
        $analysis->review_status = 'rejected';
        $analysis->review_observations = $validated['observations'];
        $analysis->reviewed_by = auth()->id();
        $analysis->review_date = now();
        $analysis->save();

        // Marcar el detalle como rechazado
        if ($validated['analysis_type'] === 'phosphorus') {
            $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
        } else {
            $detail = $analysis->analysis;
        }
        if ($detail) {
            $detail->status = 'rejected';
            $detail->save();
        }

        return redirect()
            ->route('lscefa.quality.reviews.index')
            ->with('success', 'Análisis rechazado y observaciones registradas.');
    }

    /**
     * Accept a complete process
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\LSCEFA\Models\Process  $process
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptProcess(Request $request, Process $process)
    {
        $validated = $request->validate([
            'observations' => ['nullable', 'string', 'max:5000'],
        ]);

        // Para cada detalle completado, poblar SOLO resultados desde su análisis y luego aprobar
        $details = $process->serviceProcessDetails()->where('status', 'completed')->get();
        foreach ($details as $detail) {
            try {
                $resultsOnly = [];
                if ($detail->phAnalysis) {
                    $a = $detail->phAnalysis;
                    $items = is_array($a->items_ensayo ?? null) ? $a->items_ensayo : [];
                    foreach ($items as $item) {
                        if (is_object($item)) { $item = (array)$item; }
                        $resultsOnly[] = [
                            'identificacion' => $item['identificacion'] ?? null,
                            'resultado' => $item['valor_leido'] ?? null,
                        ];
                    }
                } elseif ($detail->conductivityAnalysis) {
                    $a = $detail->conductivityAnalysis;
                    $items = is_array($a->items_ensayo ?? null) ? $a->items_ensayo : [];
                    foreach ($items as $item) {
                        if (is_object($item)) { $item = (array)$item; }
                        $resultado = $item['valor_leido'] ?? ($item['lectura_uscm'] ?? ($item['valor_leido_dsm'] ?? null));
                        $resultsOnly[] = [
                            'identificacion' => $item['identificacion'] ?? null,
                            'resultado' => $resultado,
                        ];
                    }
                }

                if (!empty($resultsOnly)) {
                    $detail->result = json_encode($resultsOnly, JSON_UNESCAPED_UNICODE);
                }
                $detail->status = 'approved';
                $detail->observations = $validated['observations'] ?? $detail->observations;
                $detail->save();
            } catch (\Throwable $e) {
                \Log::warning('acceptProcess: No se pudo poblar result para detail ID '.$detail->id.' - '.$e->getMessage());
                // Aún así aprobar para no bloquear el flujo
                $detail->status = 'approved';
                $detail->observations = $validated['observations'] ?? $detail->observations;
                $detail->save();
            }
        }

        return redirect()
            ->route('lscefa.quality.reviews.index')
            ->with('success', 'Proceso aprobado correctamente.');
    }

    /**
     * Reject a complete process
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\LSCEFA\Models\Process  $process
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectProcess(Request $request, Process $process)
    {
        $validated = $request->validate([
            'observations' => ['required', 'string', 'min:3', 'max:5000'],
        ], [
            'observations.required' => 'Debe ingresar el motivo del rechazo.',
        ]);

        // Update all completed service process details
        $process->serviceProcessDetails()
            ->where('status', 'completed')
            ->update([
                'status' => 'rejected',
                'observations' => $validated['observations']
            ]);

        return redirect()
            ->route('lscefa.quality.reviews.index')
            ->with('success', 'Proceso rechazado correctamente.');
    }
}
