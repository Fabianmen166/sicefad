<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\PhAnalysis;
use Modules\LSCEFA\Models\ConductivityAnalysis;
use Modules\LSCEFA\Models\HardnessAnalysis;
use Modules\LSCEFA\Entities\HumidityAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;
use Modules\LSCEFA\Entities\BoronAnalysis;
use Modules\LSCEFA\Entities\BoronAnalysisDetail;

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

            $humidityAnalyses = HumidityAnalysis::with([
                'analysis.process.quote.customer',
                'analysis.service',
                'user'
            ]);

            // Solo filtrar por relación 'analysis' si existe la columna analysis_id
            if (Schema::hasColumn('humidity_analyses', 'analysis_id')) {
                $humidityAnalyses = $humidityAnalyses->whereHas('analysis', function($q) {
                    $q->where('status', 'completed');
                });
            }

            $humidityAnalyses = $humidityAnalyses
                ->where('review_status', '!=', 'approved')
                ->where('review_status', '!=', 'rejected');
                
            // Fósforo: registros por ítem (no items_ensayo), relación directa a process/service
            $phosphorusAnalyses = PhosphorusAnalysis::with([
                    'process.quote.customer',
                    'service',
                ])
                // Solo mostrar si el detalle de proceso está completado
                ->whereExists(function($sub){
                    $sub->selectRaw('1')
                        ->from('service_process_details as spd')
                        ->whereColumn('spd.process_id', 'phosphorus_analyses.process_id')
                        ->whereColumn('spd.service_id', 'phosphorus_analyses.service_id')
                        ->where('spd.status', 'completed');
                });

            // Evitar error si aún no se ha ejecutado la migración que agrega review_status
            if (Schema::hasColumn('phosphorus_analyses', 'review_status')) {
                $phosphorusAnalyses = $phosphorusAnalyses->where(function($q){
                    $q->whereNull('review_status')
                      ->orWhereNotIn('review_status', ['approved','rejected']);
                });
            }

            // Obtener análisis de textura SOLO si el detalle de proceso está completado
            $textureAnalyses = \Modules\LSCEFA\Entities\BatchTextureAnalysis::all()->filter(function($item) {
                if (!$item->process_id || !$item->service_id) return false;
                $spd = \Modules\LSCEFA\Models\ServiceProcessDetail::where('process_id', $item->process_id)
                    ->where('service_id', $item->service_id)
                    ->first();
                return $spd && $spd->status === 'completed';
            });

            // Transformar resultados de textura
            $textureResults = (is_object($textureAnalyses) && method_exists($textureAnalyses, 'map')
                ? $textureAnalyses
                : collect($textureAnalyses))
                ->map(function($item) {
                return $this->transformTextureAnalysis($item, 'texture');
            });

            // Obtener análisis de boro SOLO si el detalle de proceso está completado
            $boronAnalyses = BoronAnalysisDetail::with(['process.quote.customer', 'service'])
                ->whereHas('process.serviceProcessDetails', function($query) {
                    $query->where('status', 'completed');
                });

            // Evitar error si la columna review_status no existe aún
            if (Schema::hasColumn('boron_analysis_details', 'review_status')) {
                $boronAnalyses = $boronAnalyses->where(function($q) {
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
                // Humedad: aplicar búsqueda segura según esquema
                $humidityAnalyses->where(function($q) use ($search) {
                    $q->where('consecutivo_no', 'like', "%{$search}%");
                    if (Schema::hasColumn('humidity_analyses', 'analysis_id')) {
                        $q->orWhereHas('analysis.process.quote.customer', function($qq) use ($search) {
                            $qq->where('applicant', 'like', "%{$search}%");
                        });
                    }
                });
                // Búsqueda para fósforo usando su relación process->quote->customer
                $phosphorusAnalyses->where(function($q) use ($search){
                    // Buscar por consecutivo en ES/EN
                    $q->where(function($qq) use ($search){
                        $qq->where('consecutive_no', 'like', "%{$search}%")
                           ->orWhere('consecutivo_no', 'like', "%{$search}%");
                    })
                    // Código interno
                    ->orWhere(function($qq) use ($search){
                        $qq->where('codigo_interno', 'like', "%{$search}%")
                           ->orWhere('internal_code', 'like', "%{$search}%");
                    })
                    // Consecutivo desde ServiceProcessDetail
                    ->orWhereExists(function($sub) use ($search){
                        $sub->selectRaw('1')
                            ->from('service_process_details as spd')
                            ->whereColumn('spd.process_id', 'phosphorus_analyses.process_id')
                            ->whereColumn('spd.service_id', 'phosphorus_analyses.service_id')
                            ->where('spd.consecutivo_no', 'like', "%{$search}%");
                    })
                    // Buscar por cliente (applicant/nombre)
                    ->orWhereHas('process.quote.customer', function($qq) use ($search){
                        $qq->where('applicant', 'like', "%{$search}%")
                           ->orWhere('nombre', 'like', "%{$search}%")
                           ->orWhere('name', 'like', "%{$search}%");
                    });
                });
                // Búsqueda para boro
                $boronAnalyses->where(function($q) use ($search){
                    $q->where('consecutive_no', 'like', "%{$search}%")
                      ->orWhereHas('process.quote.customer', function($qq) use ($search){
                          $qq->where('applicant', 'like', "%{$search}%");
                      });
                });
            }

            // Si se especifica un tipo, deshabilitar los demás para facilitar el filtrado/diagnóstico
            $typeFilter = $request->get('type');
            if ($typeFilter) {
                $validTypes = ['ph','conductivity','humidity','phosphorus','texture','boron'];
                if (in_array($typeFilter, $validTypes, true)) {
                    if ($typeFilter !== 'ph') { $phAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'conductivity') { $conductivityAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'humidity') { $humidityAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'phosphorus') { $phosphorusAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'texture') { $textureAnalyses = collect(); }
                    if ($typeFilter !== 'boron') { $boronAnalyses->whereRaw('1=0'); }
                }
            }

            // Obtener resultados de cada tipo
            $phResults = $phAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'ph');
            });

            $conductivityResults = $conductivityAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'conductivity');
            });
            
            $humidityResults = $humidityAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'humidity');
            });

            $phosphorusResults = $phosphorusAnalyses->get()->map(function($item) {
                return $this->transformAnalysis($item, 'phosphorus');
            });

            // Transformar resultados de boro (después de aplicar filtros)
            $boronResults = $boronAnalyses->get()->map(function($item) {
                return $this->transformBoronAnalysis($item, 'boron');
            });

            // Log de depuración de conteos
            \Log::debug('ReviewController@index counts', [
                'search' => $request->get('q'),
                'ph' => $phResults->count(),
                'conductivity' => $conductivityResults->count(),
                'humidity' => $humidityResults->count(),
                'phosphorus' => $phosphorusResults->count(),
                'texture' => $textureResults->count(),
                'boron' => null // se calcula luego
            ]);

            // Combinar todos los resultados
            $allResults = $phResults
                ->concat($conductivityResults)
                ->concat($humidityResults)
                ->concat($phosphorusResults)
                ->concat($textureResults)
                ->concat($boronResults);

            // Agrupar por servicio para humedad, por consecutivo para otros
            $groupedResults = $allResults->groupBy(function($item) {
                // Para humedad, agrupar por service_id + analysis_id (mismo servicio)
                if ($item->type === 'humidity') {
                    return 'humidity_' . $item->service_id . '_' . $item->analysis_id;
                }
                // Para otros tipos, mantener agrupación por consecutivo
                return $item->consecutivo_no;
            })->map(function($group) {
                $first = $group->first();
                
                // Si solo hay un análisis, mantener sus ítems originales
                if ($group->count() === 1) {
                    $first->analysis_count = 1;
                    $first->analysis_types = [$first->type];
                    // Asegurarse de que items_ensayo sea un array
                    $first->items_ensayo = is_array($first->items_ensayo) ? $first->items_ensayo : [];
                    return $first;
                }
                
                // Para múltiples análisis del mismo servicio, combinar
                $first->items_ensayo = $group->flatMap(function($item) {
                    return is_array($item->items_ensayo) ? $item->items_ensayo : [];
                })->toArray();
                
                $first->analysis_count = $group->count();
                $first->analysis_types = $group->pluck('type')->unique()->toArray();
                
                // Para humedad, mostrar el primer consecutivo como representativo
                if ($first->type === 'humidity') {
                    $first->consecutivo_no = $group->first()->consecutivo_no . ' (+' . ($group->count() - 1) . ' más)';
                }
                
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
            return back()->with('error', 'Ocurrió un error al cargar los análisis. Por favor, intente nuevamente. Detalle: ' . $e->getMessage());
        }
    }

    /**
     * Prepara datos para vista de Fósforo (analytical controls, consecutivo, etc.)
     */
    protected function preparePhosphorusData($analysis): array
    {
        $data = [];
        try {
            $processId = $analysis->process_id ?? null;
            $serviceId = $analysis->service_id ?? null;
            if ($processId) {
                // Buscar AnalyticalControl por process y tipo (aceptar variantes de nombre)
                $control = AnalyticalControl::where('process_id', $processId)
                    ->whereIn('analysis_type', ['phosphorus', 'fosforo'])
                    ->orderByDesc('id')
                    ->first();
                // Fallback: si no hay analysis_type (datos antiguos), tomar el más reciente por process
                if (!$control) {
                    $control = AnalyticalControl::where('process_id', $processId)
                        ->orderByDesc('id')
                        ->first();
                }
                if ($control) {
                    $data['analyticalControl'] = $control;
                    try {
                        \Log::debug('preparePhosphorusData: AnalyticalControl found', [
                            'process_id' => $processId,
                            'analysis_type' => $control->analysis_type,
                            'has_controles_analiticos' => !empty($control->controles_analiticos),
                            'has_analytical_controls' => !empty($control->analytical_controls ?? null),
                            'curve_measured' => $control->curve_measured_value ?? $control->curva_valor_leido ?? null,
                            'dpr_a' => $control->dpr_duplicate_a ?? $control->dpr_duplicado_a ?? null,
                            'dpr_b' => $control->dpr_duplicate_b ?? $control->dpr_duplicado_b ?? null,
                        ]);
                    } catch (\Throwable $e) {}
                }
            }

            // Efectivo consecutivo: considerar campos en ES/EN con fallback
            $effective = $analysis->consecutivo_no
                ?? $analysis->consecutive_no
                ?? ($analysis->consecutivo ?? null)
                ?? null;
            if ($effective === null && $processId && $serviceId) {
                try {
                    $spd = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $serviceId)
                        ->first();
                    if ($spd && !empty($spd->consecutivo_no)) {
                        $effective = $spd->consecutivo_no;
                    }
                } catch (\Throwable $e) {
                    \Log::debug('preparePhosphorusData SPD consecutivo fallback error: '.$e->getMessage());
                }
            }
            $data['effectiveConsecutivo'] = $effective;
        } catch (\Throwable $e) {
            \Log::warning('preparePhosphorusData error: '.$e->getMessage());
        }
        return $data;
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
            // Fallback de consecutivo desde ServiceProcessDetail si no existe en la fila
            $spdConsecutivo = null;
            try {
                if (!($analysis->consecutivo_no ?? null) && !($analysis->consecutive_no ?? null)) {
                    $spd = ServiceProcessDetail::where('process_id', $analysis->process_id)
                        ->where('service_id', $analysis->service_id)
                        ->first();
                    $spdConsecutivo = $spd->consecutivo_no ?? null;
                }
            } catch (\Throwable $e) {
                \Log::debug('transformAnalysis phosphorus SPD fallback error: '.$e->getMessage());
            }

            $item = [
                'identificacion' => $analysis->codigo_interno
                    ?? $analysis->internal_code
                    ?? 'N/A',
                'valor_leido' => $analysis->fosforo_disponible_mg_kg
                    ?? $analysis->available_phosphorus_mg_kg
                    ?? $analysis->available_phosphorus_mg_l
                    ?? $analysis->fosforo_disponible_mg_l
                    ?? null,
                'observaciones' => $analysis->observaciones_item
                    ?? $analysis->item_observations
                    ?? '',
            ];

            return (object) [
                'id' => $analysis->id,
                'type' => $type,
                'analysis_id' => null,
                // Fallback entre nombres en ES/EN
                'consecutivo_no' => $analysis->consecutivo_no
                    ?? $analysis->consecutive_no
                    ?? ($analysis->consecutivo ?? null)
                    ?? $spdConsecutivo
                    ?? null,
                'fecha_analisis' => $analysis->fecha_analisis
                    ?? $analysis->analysis_date
                    ?? null,
                'codigo_probeta' => null,
                'codigo_equipo' => $analysis->equipo_utilizado
                    ?? $analysis->equipment_used
                    ?? null,
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
                'service_id' => $analysis->service_id ?? null,
            'consecutivo_no' => $analysis->consecutivo_no,
                'fecha_analisis' => $analysis->fecha_analisis,
                'codigo_probeta' => $analysis->codigo_probeta ?? null,
                'codigo_equipo' => $analysis->codigo_equipo ?? null,
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

    /**
     * Transforma un análisis de textura al formato común
     */
    protected function transformTextureAnalysis($analysis, $type)
    {
        $items = is_array($analysis->samples) ? $analysis->samples : [];
        $firstItem = !empty($items) ? (object)$items[0] : null;
        $process = null;
        $quote = null;
        $customer = null;
        // Servicio: mostrar el tipo de análisis en texto
        $serviceName = 'Textura';
        return (object)[
            'id' => $analysis->id,
            'type' => $type, // Esto se usará para el badge arriba
            'service_name' => $serviceName,
            'analysis_id' => $analysis->id,
            'consecutivo_no' => $analysis->consecutive_no, // Muestra
            'fecha_analisis' => $analysis->analysis_date,
            'codigo_probeta' => $analysis->consecutive_no, // Mostrar consecutivo como muestra
            'codigo_equipo' => null,
            'review_status' => $analysis->review_status ?? 'pending',
            'user' => (object)['name' => $analysis->analyst_name], // Analista
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'first_item' => $firstItem,
            'created_at' => $analysis->created_at,
            'items_ensayo' => $items
        ];
    }

    /**
     * Transforma un análisis de boro al formato común
     */
    protected function transformBoronAnalysis($analysis, $type)
    {
        // Para boro, cada fila es un item individual
        $item = [
            'identificacion' => $analysis->internal_code ?? 'N/A',
            'peso_muestra' => $analysis->sample_weight ?? 'N/A',
            'pw' => $analysis->pw ?? 'N/A',
            'v_extractante' => $analysis->extractant_volume ?? 'N/A',
            'lectura_blanco' => $analysis->blank_reading ?? 'N/A',
            'factor_dilucion' => $analysis->dilution_factor ?? 'N/A',
            'boro_disponible_mg_l' => $analysis->available_boron_mg_l ?? 'N/A',
            'boro_disponible_mg_kg' => $analysis->available_boron_mg_kg ?? 'N/A',
            'observaciones' => $analysis->item_observations ?? '',
        ];

        $process = $analysis->process ?? null;
        $quote = $process->quote ?? null;
        $customer = $quote->customer ?? null;
        
        // Servicio: mostrar el tipo de análisis en texto
        $serviceName = 'Boro';
        
        return (object)[
            'id' => $analysis->id,
            'type' => $type, // Esto se usará para el badge arriba
            'service_name' => $serviceName,
            'analysis_id' => $analysis->id,
            'consecutivo_no' => $analysis->consecutive_no ?? 'N/A',
            'fecha_analisis' => $analysis->analysis_date,
            'codigo_probeta' => $analysis->internal_code ?? 'N/A',
            'codigo_equipo' => $analysis->equipment_used,
            'review_status' => $analysis->review_status ?? 'pending',
            'user' => (object)['name' => $analysis->analyst_name], // Analista
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'first_item' => (object)$item,
            'created_at' => $analysis->created_at,
            'items_ensayo' => [$item]
        ];
    }

    // Ver detalle de un análisis para revisión
    public function show($id)
    {
        try {
            // Permitir forzar el tipo por query param, igual que en accept()/reject()
            $requestedType = request()->get('type');
            $analysis = null;
            $type = null;

            // Si viene el tipo, consultar directamente el modelo correspondiente
            if ($requestedType) {
                switch ($requestedType) {
                    case 'ph':
                        $analysis = PhAnalysis::with([
                            'analysis.process.quote', 'analysis.service', 'analysis.process.customer', 'analysis.process.quote.customer', 'user'
                        ])->find($id);
                        $type = $analysis ? 'ph' : null;
                        break;
                    case 'conductivity':
                        $analysis = ConductivityAnalysis::with([
                            'analysis.process.quote', 'analysis.service', 'analysis.process.customer', 'analysis.process.quote.customer', 'user'
                        ])->find($id);
                        $type = $analysis ? 'conductivity' : null;
                        break;
                    case 'humidity':
                        $analysis = HumidityAnalysis::with([
                            'analysis.process.quote', 'analysis.service', 'analysis.process.customer', 'analysis.process.quote.customer', 'user'
                        ])->find($id);
                        $type = $analysis ? 'humidity' : null;
                        break;
                    case 'phosphorus':
                        $analysis = PhosphorusAnalysis::with(['process.quote.customer','service'])->find($id);
                        $type = $analysis ? 'phosphorus' : null;
                        // Fallback: si no existe un fósforo con ese ID, intentar resolver por process/service
                        if (!$analysis) {
                            // Intentar encontrar un análisis de pH o conductividad con este ID para obtener process/service
                            $phTmp = PhAnalysis::with(['analysis'])->find($id);
                            $condTmp = $phTmp ? null : ConductivityAnalysis::with(['analysis'])->find($id);
                            $procId = $phTmp->analysis->process_id ?? $phTmp->process_id ?? ($condTmp->analysis->process_id ?? $condTmp->process_id ?? null);
                            $servId = $phTmp->analysis->service_id ?? $phTmp->service_id ?? ($condTmp->analysis->service_id ?? $condTmp->service_id ?? null);
                            if ($procId && $servId) {
                                $analysis = PhosphorusAnalysis::with(['process.quote.customer','service'])
                                    ->where('process_id', $procId)
                                    ->where('service_id', $servId)
                                    ->orderByDesc('id')
                                    ->first();
                                if ($analysis) { $type = 'phosphorus'; }
                            }
                        }
                        break;
                    case 'texture':
                        $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with(['process.quote.customer','service'])->find($id);
                        $type = $analysis ? 'texture' : null;
                        break;
                    case 'boron':
                        $analysis = BoronAnalysisDetail::with(['process.quote.customer','service'])->find($id);
                        $type = $analysis ? 'boron' : null;
                        if (!$analysis) {
                            $phTmp = PhAnalysis::with(['analysis'])->find($id);
                            $condTmp = $phTmp ? null : ConductivityAnalysis::with(['analysis'])->find($id);
                            $procId = $phTmp->analysis->process_id ?? $phTmp->process_id ?? ($condTmp->analysis->process_id ?? $condTmp->process_id ?? null);
                            $servId = $phTmp->analysis->service_id ?? $phTmp->service_id ?? ($condTmp->analysis->service_id ?? $condTmp->service_id ?? null);
                            if ($procId && $servId) {
                                $analysis = BoronAnalysisDetail::with(['process.quote.customer','service'])
                                    ->where('process_id', $procId)
                                    ->where('service_id', $servId)
                                    ->orderByDesc('id')
                                    ->first();
                                if ($analysis) { $type = 'boron'; }
                            }
                        }
                        break;
                }
            }

            // Fallback: autodetección por ID si no vino tipo o no se encontró el registro del tipo indicado
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
                }
            }
            if (!$analysis) {
                $conductivityAnalysis = ConductivityAnalysis::with([
                    'analysis.process.quote', 'analysis.service', 'analysis.process.customer', 'analysis.process.quote.customer', 'user'
                ])->find($id);
                if ($conductivityAnalysis) {
                    $analysis = $conductivityAnalysis;
                    $type = 'conductivity';
                }
            }
            if (!$analysis) {
                $humidityAnalysis = HumidityAnalysis::with([
                    'analysis.process.quote', 'analysis.service', 'analysis.process.customer', 'analysis.process.quote.customer', 'user'
                ])->find($id);
                if ($humidityAnalysis) {
                    $analysis = $humidityAnalysis;
                    $type = 'humidity';
                }
            }
            if (!$analysis) {
                $phosphorusAnalysis = PhosphorusAnalysis::with(['process.quote.customer','service'])->find($id);
                if ($phosphorusAnalysis) {
                    $analysis = $phosphorusAnalysis;
                    $type = 'phosphorus';
                }
            }
            if (!$analysis) {
                $textureAnalysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with(['process.quote.customer','service'])->find($id);
                if ($textureAnalysis) {
                    $analysis = $textureAnalysis;
                    $type = 'texture';
                }
            }
            if (!$analysis) {
                $boronAnalysis = BoronAnalysisDetail::with(['process.quote.customer','service'])->find($id);
                if ($boronAnalysis) {
                    $analysis = $boronAnalysis;
                    $type = 'boron';
                }
            }
            
            if (!$analysis) {
                return redirect()->route('lscefa.quality.reviews.index')
                    ->with('error', 'No se encontró el análisis solicitado.');
            }
            
            $detail = $analysis->analysis ?? null;
            $process = $detail->process ?? $analysis->process ?? null;
            $quote = $process->quote ?? null;
            $customer = $process->customer ?? ($quote->customer ?? null);
            
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
            
            // Si es un tipo sin relación directa 'analysis' (p.ej., fósforo, boro), resolver el detail por process_id + service_id
            if (!$detail && in_array($type, ['phosphorus','boron'])) {
                try {
                    if (!empty($analysis->process_id) && !empty($analysis->service_id)) {
                        $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                            ->where('service_id', $analysis->service_id)
                            ->first();
                    }
                } catch (\Throwable $e) {
                    \Log::warning('ReviewController@show: no se pudo resolver ServiceProcessDetail para tipo '.$type.' - '.$e->getMessage());
                }
            }

            // Si logramos resolver $detail posteriormente y faltan datos de proceso/cliente, recomputarlos
            if ($detail && (!$process)) {
                try {
                    $process = $detail->process ?? $process;
                    $quote = $process->quote ?? $quote ?? null;
                    $customer = $process->customer ?? ($quote->customer ?? $customer ?? null);
                } catch (\Throwable $e) {
                    \Log::debug('ReviewController@show recompute process/customer failed: '.$e->getMessage());
                }
            }

            // Determinar qué vista usar según el tipo de análisis
            $view = match($type) {
                'ph' => 'lscefa::reviews.ph_review',
                'conductivity' => 'lscefa::reviews.conductivity_show',
                'humidity' => 'lscefa::reviews.humidity_show',
                'phosphorus' => 'lscefa::reviews.phosphorus_review',
                'texture' => 'lscefa::reviews.ph_review',
                'boron' => 'lscefa::reviews.ph_review',
                default => 'lscefa::reviews.ph_review'
            };
            
            // Preparar datos específicos según el tipo de análisis
            $viewData = [
                'detail' => $detail,
                'analysis' => $analysis,
                'process' => $process,
                'quote' => $quote,
                'customer' => $customer,
                'readonly' => false,
                'technicianName' => $technicianName,
                'type' => $type,
            ];
            
            // Agregar datos específicos según el tipo
            if ($type === 'ph') {
                $viewData = array_merge($viewData, $this->preparePhData($analysis));
            } elseif ($type === 'humidity') {
                $viewData = array_merge($viewData, $this->prepareHumidityData($analysis));
            } elseif ($type === 'phosphorus') {
                $viewData = array_merge($viewData, $this->preparePhosphorusData($analysis));
            }

            try {
                \Log::debug('ReviewController@show general info', [
                    'type' => $type,
                    'analysis_id' => $analysis->id ?? null,
                    'process_id' => $analysis->process_id ?? ($process->process_id ?? null),
                    'service_id' => $analysis->service_id ?? ($detail->service_id ?? null),
                    'analysis_consecutivo_no' => $analysis->consecutivo_no ?? $analysis->consecutive_no ?? ($analysis->consecutivo ?? null),
                    'detail_consecutivo_no' => $detail->consecutivo_no ?? null,
                    'effectiveConsecutivo' => $viewData['effectiveConsecutivo'] ?? null,
                    'customer_name' => $customer->nombre ?? $customer->name ?? null,
                    'technician' => $technicianName,
                ]);
            } catch (\Throwable $e) {}
            
            \Log::info('ReviewController@show selecting view', [
                'requested_type' => $requestedType,
                'resolved_type' => $type,
                'view' => $view,
                'analysis_id' => $analysis->id ?? null,
                'process_id' => $analysis->process_id ?? ($process->process_id ?? null),
                'service_id' => $analysis->service_id ?? ($detail->service_id ?? null),
                'detail_id' => $detail->id ?? null,
            ]);

            return view($view, $viewData);
            
        } catch (\Exception $e) {
            \Log::error('Error en ReviewController@show: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('lscefa.quality.reviews.index')
                ->with('error', 'Ocurrió un error al cargar el análisis. Por favor, intente nuevamente.');
        }
    }

    /**
     * Prepara los datos específicos para análisis de pH
     */
    protected function preparePhData($analysis)
    {
        $items_ensayo = [];
        $controles_analiticos = [];
        $muestra_referencia = [];
        $precision_analitica = [];
        
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

        // Procesar ítems de ensayo del análisis actual
        if (isset($analysis->items_ensayo) && is_array($analysis->items_ensayo)) {
            foreach ($analysis->items_ensayo as $item) {
                $norm = $normalizeItem($item);
                if ($norm !== null) { $items_ensayo[] = $norm; }
            }
        }

        // Procesar controles analíticos
        if (isset($analysis->controles_analiticos)) {
            if (is_string($analysis->controles_analiticos)) {
                $controles_analiticos = json_decode($analysis->controles_analiticos, true) ?? [];
            } elseif (is_array($analysis->controles_analiticos)) {
                $controles_analiticos = $analysis->controles_analiticos;
            } elseif (is_object($analysis->controles_analiticos)) {
                $controles_analiticos = (array)$analysis->controles_analiticos;
            }
            
            $controles_analiticos = array_values($controles_analiticos);
            
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
            
            if (isset($precision_analitica['duplicados'])) {
                $duplicados = $precision_analitica['duplicados'];
                if (is_string($duplicados)) {
                    $duplicados = json_decode($duplicados, true) ?? [];
                }
                $precision_analitica['duplicados'] = $duplicados;
            }
            
            if (isset($precision_analitica['replicas'])) {
                $replicas = $precision_analitica['replicas'];
                if (is_string($replicas)) {
                    $replicas = json_decode($replicas, true) ?? [];
                }
                $precision_analitica['replicas'] = $replicas;
            }
        }
        
        // Procesar muestra de referencia
        if (isset($analysis->muestra_referencia)) {
            if (is_string($analysis->muestra_referencia)) {
                $muestra_referencia = json_decode($analysis->muestra_referencia, true) ?? [];
            } elseif (is_array($analysis->muestra_referencia)) {
                $muestra_referencia = $analysis->muestra_referencia;
            } elseif (is_object($analysis->muestra_referencia)) {
                $muestra_referencia = (array)$analysis->muestra_referencia;
            }
        } elseif (isset($controles_analiticos[3])) {
            $muestra_referencia = is_array($controles_analiticos[3]) 
                ? $controles_analiticos[3] 
                : (array)$controles_analiticos[3];
                
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
        }
        
        // Calcular estadísticas
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
        
        return [
            'items_ensayo' => $items_ensayo,
            'controles_analiticos' => $controles_analiticos,
            'muestra_referencia' => $muestra_referencia,
            'precision_analitica' => $precision_analitica,
            'estadisticas' => $estadisticas,
        ];
    }

    /**
     * Prepara los datos específicos para análisis de humedad
     */
    protected function prepareHumidityData($analysis)
    {
        // Obtener todos los análisis de humedad del mismo servicio
        $allHumidityAnalyses = HumidityAnalysis::where('service_id', $analysis->service_id)
            ->where('analysis_id', $analysis->analysis_id)
            ->orderBy('consecutivo_no')
            ->get();
        
        $estadisticas = [];
        $analisis_completos = [];
        
        // Procesar cada análisis individual
        foreach ($allHumidityAnalyses as $humidityAnalysis) {
            $analisis_completos[] = [
                'id' => $humidityAnalysis->id,
                'consecutivo_no' => $humidityAnalysis->consecutivo_no,
                'fecha_analisis' => $humidityAnalysis->fecha_analisis,
                'hora_ingreso_horno' => $humidityAnalysis->hora_ingreso_horno,
                'hora_salida_horno' => $humidityAnalysis->hora_salida_horno,
                'temperatura_horno' => $humidityAnalysis->temperatura_horno,
                'nombre_metodo' => $humidityAnalysis->nombre_metodo,
                'intervalo_metodo' => $humidityAnalysis->intervalo_metodo,
                'equipo_utilizado' => $humidityAnalysis->equipo_utilizado,
                'peso_capsula' => $humidityAnalysis->peso_capsula,
                'peso_muestra' => $humidityAnalysis->peso_muestra,
                'peso_capsula_muestra_humedad' => $humidityAnalysis->peso_capsula_muestra_humedad,
                'peso_capsula_muestra_seca' => $humidityAnalysis->peso_capsula_muestra_seca,
                'porcentaje_humedad' => $humidityAnalysis->porcentaje_humedad,
                'observaciones' => $humidityAnalysis->observaciones,
                'review_status' => $humidityAnalysis->review_status,
                'user' => $humidityAnalysis->user,
            ];
            
            // Acumular estadísticas
            if (isset($humidityAnalysis->peso_muestra) && is_numeric($humidityAnalysis->peso_muestra)) {
                $estadisticas['pesos_muestra'][] = $humidityAnalysis->peso_muestra;
            }
            
            if (isset($humidityAnalysis->porcentaje_humedad) && is_numeric($humidityAnalysis->porcentaje_humedad)) {
                $estadisticas['porcentajes_humedad'][] = $humidityAnalysis->porcentaje_humedad;
            }
        }
        
        // Calcular estadísticas agregadas
        if (!empty($estadisticas['pesos_muestra'])) {
            $estadisticas['peso_muestra_promedio'] = array_sum($estadisticas['pesos_muestra']) / count($estadisticas['pesos_muestra']);
            $estadisticas['peso_muestra_min'] = min($estadisticas['pesos_muestra']);
            $estadisticas['peso_muestra_max'] = max($estadisticas['pesos_muestra']);
        }
        
        if (!empty($estadisticas['porcentajes_humedad'])) {
            $estadisticas['porcentaje_humedad_promedio'] = array_sum($estadisticas['porcentajes_humedad']) / count($estadisticas['porcentajes_humedad']);
            $estadisticas['porcentaje_humedad_min'] = min($estadisticas['porcentajes_humedad']);
            $estadisticas['porcentaje_humedad_max'] = max($estadisticas['porcentajes_humedad']);
        }
        
        // *** NUEVA LÓGICA PARA OBTENER CONTROLES ANALÍTICOS ***
        // Obtener todos los controles analíticos asociados a este proceso
        $controles_analiticos = \Modules\LSCEFA\Entities\AnalyticalControl::where('process_id', $analysis->process_id)
            ->where(function($query) use ($analysis) {
                $query->where('analysis_id', $analysis->analysis_id) // analysis_id del ServiceProcessDetail
                      ->orWhere('humidity_analysis_id', $analysis->id); // humidity_analysis_id directo
            })
            ->get();
        
        // Debug: Log de la consulta y resultados
        \Log::info('Consulta controles analíticos para humedad:', [
            'process_id' => $analysis->process_id,
            'analysis_id' => $analysis->analysis_id,
            'humidity_analysis_id' => $analysis->id,
            'controles_encontrados' => $controles_analiticos->count(),
            'sql' => \Modules\LSCEFA\Entities\AnalyticalControl::where('process_id', $analysis->process_id)
                ->where(function($query) use ($analysis) {
                    $query->where('analysis_id', $analysis->analysis_id)
                          ->orWhere('humidity_analysis_id', $analysis->id);
                })->toSql()
        ]);
        
        // Si no se encontraron controles, intentar con una consulta más amplia
        if ($controles_analiticos->isEmpty()) {
            $controles_analiticos = \Modules\LSCEFA\Entities\AnalyticalControl::where('process_id', $analysis->process_id)->get();
            \Log::info('Consulta ampliada de controles analíticos:', [
                'total_controles_encontrados' => $controles_analiticos->count(),
                'controles' => $controles_analiticos->toArray()
            ]);
        }
        
        // Si aún no se encontraron controles, buscar por cualquier campo relacionado
        if ($controles_analiticos->isEmpty()) {
            $controles_analiticos = \Modules\LSCEFA\Entities\AnalyticalControl::where(function($query) use ($analysis) {
                $query->where('process_id', $analysis->process_id)
                      ->orWhere('analysis_id', $analysis->analysis_id)
                      ->orWhere('humidity_analysis_id', $analysis->id);
            })->get();
            \Log::info('Consulta final de controles analíticos:', [
                'total_controles_encontrados' => $controles_analiticos->count(),
                'controles' => $controles_analiticos->toArray()
            ]);
        }
        
        return [
            'analisis_completos' => $analisis_completos,
            'total_analisis' => count($analisis_completos),
            'estadisticas' => $estadisticas,
            'controles_analiticos' => $controles_analiticos,
        ];
    }

    // Ver detalle en modo solo lectura
    public function showDetail(ServiceProcessDetail $detail)
    {
        $detail->load(['process.quote', 'service']);
        
        // Determinar el tipo de análisis
        $serviceName = strtolower($detail->service->descripcion ?? '');
        
        if (str_contains($serviceName, 'ph') || str_contains($serviceName, 'potencial')) {
            $model = 'phAnalysis';
            $view = 'lscefa::reviews.ph_review';
        } elseif (str_contains($serviceName, 'conductividad')) {
            $model = 'conductivityAnalysis';
            $view = 'lscefa::reviews.conductivity_show';
        } elseif (str_contains($serviceName, 'humedad')) {
            $model = 'humidityAnalysis';
            $view = 'lscefa::reviews.humidity_show';
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
                    'humidityAnalysis',
                    'phAnalysis.user',
                    'conductivityAnalysis.user',
                    'humidityAnalysis.user'
                ]);
            },
            'customer',
            'quote'
        ]);

        // Agrupar los análisis por tipo
        $analyses = [
            'ph' => [],
            'conductivity' => [],
            'humidity' => []
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
            // Verificar si es un análisis de humedad
            if ($detail->humidityAnalysis) {
                $analyses['humidity'][] = [
                    'detail' => $detail,
                    'analysis' => $detail->humidityAnalysis,
                    'service' => $detail->service
                ];
            }
        }

        // Verificar si hay análisis para mostrar
        if (empty($analyses['ph']) && empty($analyses['conductivity']) && empty($analyses['humidity'])) {
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
            'analysis_type' => ['required', 'in:ph,conductivity,humidity,phosphorus,texture,boron'],
        ]);

        // Buscar el análisis específico según el tipo
        if ($validated['analysis_type'] === 'ph') {
            $analysis = PhAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'conductivity') {
            $analysis = ConductivityAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'humidity') {
            $analysis = HumidityAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'phosphorus') {
            $analysis = PhosphorusAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'texture') {
            $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'boron') {
            $analysis = BoronAnalysisDetail::findOrFail($id);
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
                    // Backfill consecutivo_no en SPD si está vacío y existe la columna
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn($detail->getTable(), 'consecutivo_no')) {
                            $spdConsec = $detail->consecutivo_no;
                            $anaConsec = $analysis->consecutivo_no ?? $analysis->consecutive_no ?? null;
                            if (!$spdConsec && $anaConsec) {
                                $detail->consecutivo_no = $anaConsec;
                                $detail->save();
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::debug('accept() SPD consecutivo_no backfill phosphorus: '.$e->getMessage());
                    }

                    // Aprobar en bloque todos los ítems del mismo process/service y mismo consecutivo
                    try {
                        $cons = $analysis->consecutivo_no ?? $analysis->consecutive_no ?? null;
                        $bulk = PhosphorusAnalysis::where('process_id', $analysis->process_id)
                            ->where('service_id', $analysis->service_id);
                        if ($cons !== null) {
                            $bulk->where(function($q) use ($cons){
                                $q->where('consecutivo_no', $cons)
                                  ->orWhere('consecutive_no', $cons);
                            });
                        }
                        $bulk->update([
                            'review_status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'review_date' => now(),
                            'review_observations' => $validated['observations'] ?? null,
                        ]);
                    } catch (\Throwable $e) {
                        \Log::warning('No se pudo aprobar en bloque fósforo: '.$e->getMessage());
                    }

                    $rows = PhosphorusAnalysis::where('process_id', $analysis->process_id)
                        ->where('service_id', $analysis->service_id)
                        ->where(function($q) use ($analysis) {
                            $consecutivo = $analysis->consecutivo_no ?? null;
                            $consecutive = $analysis->consecutive_no ?? null;
                            if ($consecutivo !== null) {
                                $q->orWhere('consecutivo_no', $consecutivo)
                                  ->orWhere('consecutive_no', $consecutivo);
                            }
                            if ($consecutive !== null) {
                                $q->orWhere('consecutivo_no', $consecutive)
                                  ->orWhere('consecutive_no', $consecutive);
                            }
                        })
                        ->get();
                    $resultsOnly = $rows->map(function($row){
                        $resultado = $row->fosforo_disponible_mg_kg
                            ?? $row->available_phosphorus_mg_kg
                            ?? $row->available_phosphorus_mg_l
                            ?? $row->fosforo_disponible_mg_l
                            ?? null;
                        return [
                            'identificacion' => $row->codigo_interno
                                ?? $row->internal_code
                                ?? null,
                            'resultado' => $resultado,
                        ];
                    })->values()->toArray();
                    $detail->result = json_encode($resultsOnly, JSON_UNESCAPED_UNICODE);
                    $detail->save();
                }
            } else {
                $detail = $analysis->analysis; // ServiceProcessDetail
                // Fallback si no existe relación directa
                if (!$detail) {
                    $processId = $analysis->analysis->process_id ?? $analysis->process_id ?? null;
                    $serviceId = $analysis->analysis->service_id ?? $analysis->service_id ?? null;
                    if ($processId && $serviceId) {
                        $detail = ServiceProcessDetail::where('process_id', $processId)
                            ->where('service_id', $serviceId)
                            ->first();
                    }
                }
                if ($detail) {
                    $resultsOnly = [];
                    $items = is_array($analysis->items_ensayo ?? null) ? $analysis->items_ensayo : [];
                    if ($validated['analysis_type'] === 'ph') {
                        foreach ($items as $item) {
                            if (is_object($item)) { $item = (array)$item; }
                            $resultsOnly[] = [
                                'identificacion' => $item['identificacion']
                                    ?? $item['identificación']
                                    ?? $item['codigo_interno']
                                    ?? $item['internal_code']
                                    ?? null,
                                'resultado' => $item['valor_leido']
                                    ?? $item['resultado']
                                    ?? $item['value']
                                    ?? null,
                            ];
                        }
                    } elseif ($validated['analysis_type'] === 'texture') {
                        // Para textura, extraer datos de las muestras
                        $samples = is_string($analysis->samples) ? json_decode($analysis->samples, true) : $analysis->samples;
                        if (is_array($samples)) {
                            foreach ($samples as $sample) {
                                if (is_array($sample) && isset($sample['codigo_interno']) && $sample['codigo_interno'] !== 'Blanco del proceso') {
                                    $resultsOnly[] = [
                                        'identificacion' => $sample['codigo_interno'] ?? null,
                                        'resultado' => $sample['clase_textural'] ?? null,
                                    ];
                                }
                            }
                        }
                    } elseif ($validated['analysis_type'] === 'boron') {
                        // Para boro, extraer datos del análisis individual
                        $resultsOnly[] = [
                            'identificacion' => $analysis->internal_code ?? null,
                            'resultado' => $analysis->available_boron_mg_kg ?? $analysis->available_boron_mg_l ?? null,
                        ];
                    } else { // conductivity
                        foreach ($items as $item) {
                            if (is_object($item)) { $item = (array)$item; }
                            // Ampliar claves aceptadas para conductividad
                            $resultado = $item['resultado']
                                ?? $item['valor_leido']
                                ?? $item['value']
                                ?? $item['lectura']
                                ?? $item['lectura_uscm']
                                ?? $item['valor_leido_dsm']
                                ?? null;
                            $resultsOnly[] = [
                                'identificacion' => $item['identificacion']
                                    ?? $item['identificación']
                                    ?? $item['codigo_interno']
                                    ?? $item['internal_code']
                                    ?? null,
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
            
            // Verificar análisis de humedad
            if ($detail->humidityAnalysis && $detail->humidityAnalysis->id != $analysis->id) {
                if ($detail->humidityAnalysis->review_status !== 'approved') {
                    $allApproved = false;
                }
            }
            
            // Verificar análisis de textura
            if ($validated['analysis_type'] === 'texture') {
                // Para textura, verificar si hay otros análisis del mismo proceso
                $otherTextureAnalyses = \Modules\LSCEFA\Entities\BatchTextureAnalysis::where('process_id', $analysis->process_id)
                    ->where('id', '!=', $analysis->id)
                    ->get();
                
                foreach ($otherTextureAnalyses as $otherAnalysis) {
                    if ($otherAnalysis->review_status !== 'approved') {
                        $allApproved = false;
                        break;
                    }
                }
            }
            
            // Verificar análisis de boro
            if ($validated['analysis_type'] === 'boron') {
                // Para boro, verificar si hay otros análisis del mismo proceso
                $otherBoronAnalyses = BoronAnalysisDetail::where('process_id', $analysis->process_id)
                    ->where('id', '!=', $analysis->id)
                    ->get();
                
                foreach ($otherBoronAnalyses as $otherAnalysis) {
                    if ($otherAnalysis->review_status !== 'approved') {
                        $allApproved = false;
                        break;
                    }
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
            'analysis_type' => ['required', 'in:ph,conductivity,humidity,phosphorus,texture,boron'],
        ], [
            'observations.required' => 'Debe ingresar observaciones para rechazar el análisis.',
            'observations.min' => 'Las observaciones deben tener al menos :min caracteres.',
        ]);

        // Buscar el análisis específico según el tipo
        if ($validated['analysis_type'] === 'ph') {
            $analysis = PhAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'conductivity') {
            $analysis = ConductivityAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'humidity') {
            $analysis = HumidityAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'phosphorus') {
            $analysis = PhosphorusAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'texture') {
            $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'boron') {
            $analysis = BoronAnalysisDetail::findOrFail($id);
        }

        // Actualizar el estado de revisión
        $analysis->review_status = 'rejected';
        $analysis->review_observations = $validated['observations'];
        $analysis->reviewed_by = auth()->id();
        $analysis->review_date = now();
        $analysis->save();

        // Marcar el detalle según el tipo de análisis
        if ($validated['analysis_type'] === 'phosphorus' || $validated['analysis_type'] === 'boron') {
            $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
        } else {
            $detail = $analysis->analysis;
        }
        if ($detail) {
            if ($validated['analysis_type'] === 'texture' || $validated['analysis_type'] === 'boron') {
                // Para textura y boro, poner en pending para que aparezca en la tabla de análisis devueltos
                $detail->status = 'pending';
            } else {
                // Para otros análisis, mantener como rejected
                $detail->status = 'rejected';
            }
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
                } else {
                    // Verificar si es un análisis de textura
                    $textureAnalysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::where('process_id', $detail->process_id)
                        ->where('service_id', $detail->service_id)
                        ->first();
                    
                    if ($textureAnalysis) {
                        $samples = is_string($textureAnalysis->samples) ? json_decode($textureAnalysis->samples, true) : $textureAnalysis->samples;
                        if (is_array($samples)) {
                            foreach ($samples as $sample) {
                                if (is_array($sample) && isset($sample['codigo_interno']) && $sample['codigo_interno'] !== 'Blanco del proceso') {
                                    $resultsOnly[] = [
                                        'identificacion' => $sample['codigo_interno'] ?? null,
                                        'resultado' => $sample['clase_textural'] ?? null,
                                    ];
                                }
                            }
                        }
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
