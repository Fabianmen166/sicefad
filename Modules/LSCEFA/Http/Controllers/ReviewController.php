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

use Modules\LSCEFA\Entities\BoronAnalysisDetail;
use Modules\LSCEFA\Entities\MicronutrientsAnalysis;
use Modules\LSCEFA\Entities\CationicAnalysis;
use Modules\LSCEFA\Entities\SulfurAnalysis;

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
                'process.quote.customer',
                'user'
            ]);

            // Para humedad, verificar que el detalle de proceso esté completado
            $humidityAnalyses = $humidityAnalyses->whereExists(function($sub){
                $sub->selectRaw('1')
                    ->from('service_process_details as spd')
                    ->whereColumn('spd.process_id', 'humidity_analyses.process_id')
                    ->where('spd.status', 'completed');
            });

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
            $textureAnalyses = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with(['analyticalControls'])->where(function($q) {
                $q->whereNotNull('process_id')
                  ->whereNotNull('service_id');
            })->get()->filter(function($item) {
                $spd = \Modules\LSCEFA\Models\ServiceProcessDetail::where('process_id', $item->process_id)
                    ->where('service_id', $item->service_id)
                    ->first();
                return $spd && $spd->status === 'completed';
            });

            // Filtrar por review_status si existe la columna
            if (Schema::hasColumn('batch_texture_analyses', 'review_status')) {
                $textureAnalyses = $textureAnalyses->filter(function($item) {
                    return !in_array($item->review_status, ['approved', 'rejected']);
                });
            }

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

            // Obtener análisis de micronutrientes SOLO si el detalle de proceso está completado
            $micronutrientsAnalyses = MicronutrientsAnalysis::with(['analysis.process.quote.customer', 'analysis.service', 'user'])
                ->whereHas('analysis', function($query) {
                    $query->where('status', 'completed');
                });

            // Evitar error si la columna review_status no existe aún
            if (Schema::hasColumn('micronutrients_analyses', 'review_status')) {
                $micronutrientsAnalyses = $micronutrientsAnalyses->where(function($q) {
                    $q->whereNull('review_status')
                      ->orWhereNotIn('review_status', ['approved','rejected']);
                });
            }

            // Obtener análisis de intercambio catiónico si el detalle de proceso está completado o pendiente
            $cationicAnalyses = CationicAnalysis::with(['process.quote.customer', 'analyticalControl'])
                ->whereHas('process.serviceProcessDetails', function($query) {
                    $query->where('service_id', 11) // ID del servicio de intercambio catiónico
                          ->whereIn('status', ['completed', 'pending']);
                });

            // Evitar error si la columna review_status no existe aún
            if (Schema::hasColumn('cationic_analyses', 'review_status')) {
                $cationicAnalyses = $cationicAnalyses->where(function($q) {
                    $q->whereNull('review_status')
                      ->orWhereNotIn('review_status', ['approved','rejected']);
                });
            }

            // Obtener análisis de azufre si el detalle de proceso está completado
            $sulfurAnalyses = SulfurAnalysis::with(['process.quote.customer', 'analyticalControl'])
                ->whereHas('process.serviceProcessDetails', function($query) {
                    $query->where('service_id', 6) // ID del servicio de azufre
                          ->where('status', 'completed');
                });

            // Evitar error si la columna review_status no existe aún
            if (Schema::hasColumn('sulfur_analyses', 'review_status')) {
                $sulfurAnalyses = $sulfurAnalyses->where(function($q) {
                    $q->whereNull('review_status')
                      ->orWhereNotIn('review_status', ['approved','rejected']);
                });
            }

            // Aplicar filtros específicos para análisis de textura
            if ($request->filled('texture_filter')) {
                $textureFilter = $request->get('texture_filter');
                
                $textureAnalyses = $this->applyTextureFilters($textureAnalyses, $textureFilter);
            }

            // Aplicar filtros específicos para análisis de boro
            if ($request->filled('boron_filter')) {
                $boronFilter = $request->get('boron_filter');
                
                $boronAnalyses = $this->applyBoronFilters($boronAnalyses, $boronFilter);
            }

            // Transformar resultados de textura
            $textureResults = $textureAnalyses->map(function($item) {
                return $this->transformTextureAnalysis($item, 'texture');
            });

            // Obtener estadísticas de textura si se aplicaron filtros
            $textureStats = null;
            if ($request->filled('texture_filter')) {
                $textureStats = $this->getTextureAnalysisStats($textureAnalyses);
            }

            // Obtener estadísticas de boro si se aplicaron filtros
            $boronStats = null;
            if ($request->filled('boron_filter')) {
                $boronStats = $this->getBoronAnalysisStats($boronAnalyses);
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
                // Humedad: aplicar búsqueda segura
                $humidityAnalyses->where(function($q) use ($search) {
                    $q->where('consecutivo_no', 'like', "%{$search}%")
                      ->orWhereHas('process.quote.customer', function($qq) use ($search) {
                          $qq->where('applicant', 'like', "%{$search}%");
                      });
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
                // Búsqueda para micronutrientes
                $micronutrientsAnalyses->where(function($q) use ($search){
                    $q->where('consecutivo_no', 'like', "%{$search}%")
                      ->orWhereHas('analysis.process.quote.customer', function($qq) use ($search){
                          $qq->where('applicant', 'like', "%{$search}%");
                      });
                });
                // Búsqueda para intercambio catiónico
                $cationicAnalyses->where(function($q) use ($search){
                    $q->where('consecutivo_no', 'like', "%{$search}%")
                      ->orWhereHas('process.quote.customer', function($qq) use ($search){
                          $qq->where('applicant', 'like', "%{$search}%")
                             ->orWhere('nombre', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
                      });
                });
                // Búsqueda para azufre
                $sulfurAnalyses->where(function($q) use ($search){
                    $q->where('consecutive_no', 'like', "%{$search}%")
                      ->orWhere('internal_code', 'like', "%{$search}%")
                      ->orWhere('process_id', 'like', "%{$search}%")
                      ->orWhere('analyst_name', 'like', "%{$search}%")
                      ->orWhereHas('process.quote.customer', function($qq) use ($search){
                          $qq->where('applicant', 'like', "%{$search}%");
                      });
                });
            }

            // Aplicar filtro por muestra (codigo_probeta)
            if ($request->filled('muestra') && trim($request->get('muestra')) !== '') {
                $muestra = trim($request->get('muestra'));
                $muestraCallback = function($query) use ($muestra) {
                    $query->where('codigo_probeta', 'like', "%{$muestra}%");
                };

                $phAnalyses->where($muestraCallback);
                $conductivityAnalyses->where($muestraCallback);
                // Para humedad, buscar en codigo_interno si existe
                if (Schema::hasColumn('humidity_analyses', 'codigo_interno')) {
                    $humidityAnalyses->where('codigo_interno', 'like', "%{$muestra}%");
                }
                // Para fósforo, buscar en codigo_interno
                $phosphorusAnalyses->where('codigo_interno', 'like', "%{$muestra}%");
                // Para boro, buscar en codigo_interno
                $boronAnalyses->where('codigo_interno', 'like', "%{$muestra}%");
                // Para micronutrientes, buscar en items_ensayo
                $micronutrientsAnalyses->whereRaw('JSON_SEARCH(items_ensayo, "one", ?, null, "$[*].codigo_interno")', ["%{$muestra}%"]);
                // Para intercambio catiónico, buscar en consecutivo_no
                $cationicAnalyses->where('consecutivo_no', 'like', "%{$muestra}%");
                // Para azufre, buscar en internal_code y otros campos relevantes
                $sulfurAnalyses->where(function($q) use ($muestra) {
                    $q->where('internal_code', 'like', "%{$muestra}%")
                      ->orWhere('consecutive_no', 'like', "%{$muestra}%")
                      ->orWhere('process_id', 'like', "%{$muestra}%");
                });
            }



            // Si se especifica un tipo específico, deshabilitar los demás para facilitar el filtrado/diagnóstico
            $typeFilter = $request->get('type');
            if ($typeFilter && $typeFilter !== 'all' && trim($typeFilter) !== '') {
                $validTypes = ['ph','conductivity','humidity','phosphorus','texture','boron','micronutrients','cationic','sulfur'];
                if (in_array($typeFilter, $validTypes, true)) {
                    if ($typeFilter !== 'ph') { $phAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'conductivity') { $conductivityAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'humidity') { $humidityAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'phosphorus') { $phosphorusAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'texture') { $textureAnalyses = collect(); }
                    if ($typeFilter !== 'boron') { $boronAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'micronutrients') { $micronutrientsAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'cationic') { $cationicAnalyses->whereRaw('1=0'); }
                    if ($typeFilter !== 'sulfur') { $sulfurAnalyses->whereRaw('1=0'); }
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
            \Log::debug('ReviewController: Iniciando transformación de boro...');
            $boronResults = $boronAnalyses->get()->map(function($item) {
                return $this->transformBoronAnalysis($item, 'boron');
            });
            \Log::debug('ReviewController: Transformación de boro completada. Total: ' . $boronResults->count());

            // Transformar resultados de micronutrientes
            \Log::debug('ReviewController: Iniciando transformación de micronutrientes...');
            $micronutrientsResults = $micronutrientsAnalyses->get()->map(function($item) {
                return $this->transformMicronutrientsAnalysis($item, 'micronutrients');
            });
            \Log::debug('ReviewController: Transformación de micronutrientes completada. Total: ' . $micronutrientsResults->count());

            // Transformar resultados de intercambio catiónico
            \Log::debug('ReviewController: Iniciando transformación de intercambio catiónico...');
            $cationicResults = $cationicAnalyses->get()->map(function($item) {
                return $this->transformCationicAnalysis($item, 'cationic');
            });
            \Log::debug('ReviewController: Transformación de intercambio catiónico completada. Total: ' . $cationicResults->count());

            // Transformar resultados de azufre
            \Log::debug('ReviewController: Iniciando transformación de azufre...');
            $sulfurResults = $sulfurAnalyses->get()->map(function($item) {
                return $this->transformSulfurAnalysis($item, 'sulfur');
            });
            \Log::debug('ReviewController: Transformación de azufre completada. Total: ' . $sulfurResults->count());

            // Log de depuración de conteos
            \Log::debug('ReviewController@index counts', [
                'search' => $request->get('q'),
                'ph' => $phResults->count(),
                'conductivity' => $conductivityResults->count(),
                'humidity' => $humidityResults->count(),
                'phosphorus' => $phosphorusResults->count(),
                'texture' => $textureResults->count(),
                'boron' => $boronResults->count(),
                'micronutrients' => $micronutrientsResults->count(),
                'cationic' => $cationicResults->count(),
                'sulfur' => $sulfurResults->count()
            ]);

            \Log::debug('ReviewController: Iniciando combinación de resultados...');
            // Combinar todos los resultados
            $allResults = $phResults
                ->concat($conductivityResults)
                ->concat($humidityResults)
                ->concat($phosphorusResults)
                ->concat($textureResults)
                ->concat($boronResults)
                ->concat($micronutrientsResults)
                ->concat($cationicResults)
                ->concat($sulfurResults);
            \Log::debug('ReviewController: Combinación completada. Total: ' . $allResults->count());

            \Log::debug('ReviewController: Iniciando agrupación de resultados...');
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
            \Log::debug('ReviewController: Agrupación completada. Total grupos: ' . $groupedResults->count());

            \Log::debug('ReviewController: Iniciando ordenamiento...');
            // Ordenar por fecha de creación (más reciente primero)
            $sortedResults = $groupedResults->sortByDesc(function($item) {
                return $item->created_at;
            });
            \Log::debug('ReviewController: Ordenamiento completado');

            \Log::debug('ReviewController: Iniciando paginación...');
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
            \Log::debug('ReviewController: Paginación completada. Total: ' . $analyses->total());

            \Log::debug('ReviewController: Preparando vista...');
            return view('lscefa::reviews.index', [
                'allAnalyses' => $analyses,
                'textureStats' => $textureStats,
                'textureFilters' => $request->get('texture_filter'),
                'textureFiltersSummary' => $this->getTextureFiltersSummary($request->get('texture_filter')),
                'predefinedTextureFilters' => $this->getPredefinedTextureFilters(),
                'boronStats' => $boronStats,
                'boronFilters' => $request->get('boron_filter'),
                'boronFiltersSummary' => $this->getBoronFiltersSummary($request->get('boron_filter')),
                'predefinedBoronFilters' => $this->getPredefinedBoronFilters()
            ]);
            
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
     * Prepara datos específicos para análisis de textura (controles analíticos, etc.)
     */
    protected function prepareTextureData($analysis): array
    {
        $data = [];
        try {
            $processId = $analysis->process_id ?? null;
            if ($processId) {
                // Buscar AnalyticalControl por process y tipo textura
                $controls = AnalyticalControl::where('process_id', $processId)
                    ->whereIn('analysis_type', ['texture', 'textura'])
                    ->orderByDesc('id')
                    ->get();
                
                if ($controls->isNotEmpty()) {
                    $data['analyticalControls'] = $controls;
                    \Log::debug('prepareTextureData: AnalyticalControls found', [
                        'process_id' => $processId,
                        'controls_count' => $controls->count(),
                        'analysis_types' => $controls->pluck('analysis_type')->toArray()
                    ]);
                } else {
                    // Si no se encontraron controles específicos de textura, buscar cualquier control del proceso
                    $fallbackControls = AnalyticalControl::where('process_id', $processId)->get();
                    if ($fallbackControls->isNotEmpty()) {
                        $data['analyticalControls'] = $fallbackControls;
                        \Log::debug('prepareTextureData: Fallback controls found', [
                            'process_id' => $processId,
                            'controls_count' => $fallbackControls->count()
                        ]);
                    }
                }
                
                // También buscar en el campo JSON controles_analiticos del propio análisis
                if (isset($analysis->analytical_controls) && !empty($analysis->analytical_controls)) {
                    $jsonControls = is_array($analysis->analytical_controls) ? 
                        $analysis->analytical_controls : 
                        json_decode($analysis->analytical_controls, true);
                    
                    if (is_array($jsonControls)) {
                        $data['jsonAnalyticalControls'] = $jsonControls;
                        \Log::debug('prepareTextureData: JSON controls found', [
                            'process_id' => $processId,
                            'json_controls_count' => count($jsonControls),
                            'json_controls_keys' => array_keys($jsonControls)
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('prepareTextureData error: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Prepara los datos específicos para análisis de Boro
     */
    protected function prepareBoronData($analysis): array
    {
        $data = [];
        try {
            // Preparar datos específicos del análisis de Boro
            $data['boronAnalysis'] = $analysis;
            
            // Si hay test_items, procesarlos
            if (isset($analysis->test_items) && is_array($analysis->test_items)) {
                $data['testItems'] = $analysis->test_items;
            }
            
            // Si hay controles analíticos, procesarlos
            if (isset($analysis->analytical_controls) && !empty($analysis->analytical_controls)) {
                $jsonControls = is_array($analysis->analytical_controls) ? 
                    $analysis->analytical_controls : 
                    json_decode($analysis->analytical_controls, true);
                
                if (is_array($jsonControls)) {
                    $data['analyticalControls'] = $jsonControls;
                }
            }
            
            \Log::debug('prepareBoronData: Datos preparados correctamente', [
                'analysis_id' => $analysis->id ?? null,
                'has_test_items' => isset($data['testItems']),
                'has_analytical_controls' => isset($data['analyticalControls'])
            ]);
            
        } catch (\Throwable $e) {
            \Log::warning('prepareBoronData error: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Prepara los datos específicos para análisis de micronutrientes
     */
    protected function prepareMicronutrientsData($analysis): array
    {
        $data = [];
        try {
            // Preparar datos específicos del análisis de micronutrientes
            $data['micronutrientsAnalysis'] = $analysis;
            
            // Si hay items_ensayo, procesarlos
            if (isset($analysis->items_ensayo) && is_array($analysis->items_ensayo)) {
                $data['itemsEnsayo'] = $analysis->items_ensayo;
            }
            
            // Si hay controles analíticos, procesarlos
            if (isset($analysis->controles_analiticos) && !empty($analysis->controles_analiticos)) {
                $jsonControls = is_array($analysis->controles_analiticos) ? 
                    $analysis->controles_analiticos : 
                    json_decode($analysis->controles_analiticos, true);
                
                if (is_array($jsonControls)) {
                    $data['controlesAnaliticos'] = $jsonControls;
                }
            }
            
            \Log::debug('prepareMicronutrientsData: Datos preparados correctamente', [
                'analysis_id' => $analysis->id ?? null,
                'has_items_ensayo' => isset($data['itemsEnsayo']),
                'has_controles_analiticos' => isset($data['controlesAnaliticos'])
            ]);
            
        } catch (\Throwable $e) {
            \Log::warning('prepareMicronutrientsData error: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Aplica filtros específicos para análisis de textura
     */
    protected function applyTextureFilters($textureAnalyses, $filters)
    {
        return $textureAnalyses->filter(function($item) use ($filters) {
            // Filtro por código de muestra (consecutivo)
            if (isset($filters['codigo_muestra']) && !empty($filters['codigo_muestra'])) {
                $codigoMuestra = strtolower(trim($filters['codigo_muestra']));
                $itemConsecutivo = strtolower($item->consecutive_no ?? '');
                if (strpos($itemConsecutivo, $codigoMuestra) === false) {
                    return false;
                }
            }
            
            // Filtro por analista
            if (isset($filters['analista']) && !empty($filters['analista'])) {
                $analista = strtolower(trim($filters['analista']));
                $itemAnalista = strtolower($item->analyst_name ?? '');
                if (strpos($itemAnalista, $analista) === false) {
                    return false;
                }
            }
            
            // Filtro por fecha de análisis
            if (isset($filters['fecha_desde']) && !empty($filters['fecha_desde'])) {
                try {
                    $fechaDesde = \Carbon\Carbon::parse($filters['fecha_desde']);
                    $itemFecha = \Carbon\Carbon::parse($item->analysis_date);
                    if ($itemFecha->lt($fechaDesde)) {
                        return false;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing fecha_desde: ' . $e->getMessage());
                }
            }
            
            if (isset($filters['fecha_hasta']) && !empty($filters['fecha_hasta'])) {
                try {
                    $fechaHasta = \Carbon\Carbon::parse($filters['fecha_hasta']);
                    $itemFecha = \Carbon\Carbon::parse($item->analysis_date);
                    if ($itemFecha->gt($fechaHasta)) {
                        return false;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing fecha_hasta: ' . $e->getMessage());
                }
            }
            
            // Filtro por metodología
            if (isset($filters['metodologia']) && !empty($filters['metodologia'])) {
                $metodologia = strtolower(trim($filters['metodologia']));
                $itemMetodologia = strtolower($item->methodology_used ?? '');
                if (strpos($itemMetodologia, $metodologia) === false) {
                    return false;
                }
            }
            
            // Filtro por equipo utilizado
            if (isset($filters['equipo']) && !empty($filters['equipo'])) {
                $equipo = strtolower(trim($filters['equipo']));
                $itemEquipo = strtolower($item->equipment_used ?? '');
                if (strpos($itemEquipo, $equipo) === false) {
                    return false;
                }
            }
            
            // Filtro por estado de revisión específico
            if (isset($filters['estado_revision']) && !empty($filters['estado_revision'])) {
                $estado = $filters['estado_revision'];
                $itemEstado = $item->review_status ?? 'pending';
                if ($estado !== $itemEstado) {
                    return false;
                }
            }
            
            // Filtro por código de termómetro
            if (isset($filters['codigo_termometro']) && !empty($filters['codigo_termometro'])) {
                $codigoTerm = strtolower(trim($filters['codigo_termometro']));
                $itemCodigoTerm = strtolower($item->thermometer_code ?? '');
                if (strpos($itemCodigoTerm, $codigoTerm) === false) {
                    return false;
                }
            }
            
            // Filtro por código de hidrómetro
            if (isset($filters['codigo_hidrometro']) && !empty($filters['codigo_hidrometro'])) {
                $codigoHidro = strtolower(trim($filters['codigo_hidrometro']));
                $itemCodigoHidro = strtolower($item->hydrometer_code ?? '');
                if (strpos($itemCodigoHidro, $codigoHidro) === false) {
                    return false;
                }
            }
            
            // Filtro por muestras específicas (buscar en el array de muestras)
            if (isset($filters['muestra_especifica']) && !empty($filters['muestra_especifica'])) {
                $muestraEspecifica = strtolower(trim($filters['muestra_especifica']));
                $samples = is_array($item->samples) ? $item->samples : [];
                $encontrado = false;
                
                foreach ($samples as $sample) {
                    if (is_array($sample) && isset($sample['codigo_interno'])) {
                        $codigoInterno = strtolower($sample['codigo_interno']);
                        if (strpos($codigoInterno, $muestraEspecifica) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }
                }
                
                if (!$encontrado) {
                    return false;
                }
            }
            
            // Filtro por blanco del método (primera muestra del array)
            if (isset($filters['blanco_metodo']) && !empty($filters['blanco_metodo'])) {
                $blancoMetodo = strtolower(trim($filters['blanco_metodo']));
                $samples = is_array($item->samples) ? $item->samples : [];
                
                // El blanco del método es la primera muestra del array
                if (!empty($samples) && isset($samples[0])) {
                    $blanco = $samples[0];
                    $encontrado = false;
                    
                    // Buscar en código interno del blanco
                    if (isset($blanco['codigo_interno'])) {
                        $codigoInterno = strtolower($blanco['codigo_interno']);
                        if (strpos($codigoInterno, $blancoMetodo) !== false) {
                            $encontrado = true;
                        }
                    }
                    
                    // Buscar en identificacion del blanco
                    if (!$encontrado && isset($blanco['identificacion'])) {
                        $identificacion = strtolower($blanco['identificacion']);
                        if (strpos($identificacion, $blancoMetodo) !== false) {
                            $encontrado = true;
                        }
                    }
                    
                    // Buscar en descripción del blanco
                    if (!$encontrado && isset($blanco['descripcion'])) {
                        $descripcion = strtolower($blanco['descripcion']);
                        if (strpos($descripcion, $blancoMetodo) !== false) {
                            $encontrado = true;
                        }
                    }
                    
                    if (!$encontrado) {
                        return false;
                    }
                } else {
                    // Si no hay muestras, no puede cumplir el filtro de blanco
                    return false;
                }
            }
            
            // Filtro por clase textural específica
            if (isset($filters['clase_textural']) && !empty($filters['clase_textural'])) {
                $claseTextural = strtolower(trim($filters['clase_textural']));
                $samples = is_array($item->samples) ? $item->samples : [];
                $encontrado = false;
                
                foreach ($samples as $sample) {
                    if (is_array($sample) && isset($sample['clase_textural'])) {
                        $clase = strtolower($sample['clase_textural']);
                        if (strpos($clase, $claseTextural) !== false) {
                            $encontrado = true;
                            break;
                        }
                    }
                }
                
                if (!$encontrado) {
                    return false;
                }
            }
            
            // Filtro por rango de porcentajes (arena, arcilla, limo)
            if (isset($filters['porcentaje_min']) && is_numeric($filters['porcentaje_min'])) {
                $porcentajeMin = (float)$filters['porcentaje_min'];
                $samples = is_array($item->samples) ? $item->samples : [];
                $cumple = false;
                
                foreach ($samples as $sample) {
                    if (is_array($sample)) {
                        $arena = (float)($sample['arena'] ?? 0);
                        $arcilla = (float)($sample['arcilla'] ?? 0);
                        $limo = (float)($sample['limo'] ?? 0);
                        
                        if ($arena >= $porcentajeMin || $arcilla >= $porcentajeMin || $limo >= $porcentajeMin) {
                            $cumple = true;
                            break;
                        }
                    }
                }
                
                if (!$cumple) {
                    return false;
                }
            }
            
            if (isset($filters['porcentaje_max']) && is_numeric($filters['porcentaje_max'])) {
                $porcentajeMax = (float)$filters['porcentaje_max'];
                $samples = is_array($item->samples) ? $item->samples : [];
                $cumple = false;
                
                foreach ($samples as $sample) {
                    if (is_array($sample)) {
                        $arena = (float)($sample['arena'] ?? 0);
                        $arcilla = (float)($sample['arcilla'] ?? 0);
                        $limo = (float)($sample['limo'] ?? 0);
                        
                        if ($arena <= $porcentajeMax || $arcilla <= $porcentajeMax || $limo <= $porcentajeMax) {
                            $cumple = true;
                            break;
                        }
                    }
                }
                
                if (!$cumple) {
                    return false;
                }
            }
            
            // ===== FILTROS PARA CONTROLES ANALÍTICOS =====
            
            // Filtro por tipo de control analítico
            if (isset($filters['tipo_control']) && !empty($filters['tipo_control'])) {
                $tipoControl = strtolower(trim($filters['tipo_control']));
                $tieneControl = false;
                
                // Verificar en el campo JSON del análisis
                if (isset($item->analytical_controls) && !empty($item->analytical_controls)) {
                    $jsonControls = is_array($item->analytical_controls) ? 
                        $item->analytical_controls : 
                        json_decode($item->analytical_controls, true);
                    
                    if (is_array($jsonControls)) {
                        foreach ($jsonControls as $key => $controlData) {
                            if (is_array($controlData) && isset($controlData['identificacion'])) {
                                $identificacion = strtolower($controlData['identificacion']);
                                if (strpos($identificacion, $tipoControl) !== false) {
                                    $tieneControl = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Verificar en la relación analyticalControls
                if (!$tieneControl && isset($item->analyticalControls) && $item->analyticalControls->count() > 0) {
                    foreach ($item->analyticalControls as $control) {
                        if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                            foreach ($control->controles_analiticos as $key => $controlData) {
                                if (is_array($controlData) && isset($controlData['identificacion'])) {
                                    $identificacion = strtolower($controlData['identificacion']);
                                    if (strpos($identificacion, $tipoControl) !== false) {
                                        $tieneControl = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (!$tieneControl) {
                    return false;
                }
            }
            
            // Filtro por aceptabilidad de controles analíticos
            if (isset($filters['aceptabilidad_control']) && !empty($filters['aceptabilidad_control'])) {
                $aceptabilidad = strtolower(trim($filters['aceptabilidad_control']));
                $tieneAceptabilidad = false;
                
                // Verificar en el campo JSON del análisis
                if (isset($item->analytical_controls) && !empty($item->analytical_controls)) {
                    $jsonControls = is_array($item->analytical_controls) ? 
                        $item->analytical_controls : 
                        json_decode($item->analytical_controls, true);
                    
                    if (is_array($jsonControls)) {
                        foreach ($jsonControls as $key => $controlData) {
                            if (is_array($controlData) && isset($controlData['aceptabilidad_control'])) {
                                $aceptabilidadControl = strtolower($controlData['aceptabilidad_control']);
                                if (strpos($aceptabilidadControl, $aceptabilidad) !== false) {
                                    $tieneAceptabilidad = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Verificar en la relación analyticalControls
                if (!$tieneAceptabilidad && isset($item->analyticalControls) && $item->analyticalControls->count() > 0) {
                    foreach ($item->analyticalControls as $control) {
                        if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                            foreach ($control->controles_analiticos as $key => $controlData) {
                                if (is_array($controlData) && isset($controlData['aceptabilidad_control'])) {
                                    $aceptabilidadControl = strtolower($controlData['aceptabilidad_control']);
                                    if (strpos($aceptabilidadControl, $aceptabilidad) !== false) {
                                        $tieneAceptabilidad = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (!$tieneAceptabilidad) {
                    return false;
                }
            }
            
            // Filtro por rango de DPR (Diferencia Porcentual Relativa)
            if (isset($filters['dpr_min']) && is_numeric($filters['dpr_min'])) {
                $dprMin = (float)$filters['dpr_min'];
                $cumpleDprMin = false;
                
                // Verificar en el campo JSON del análisis
                if (isset($item->analytical_controls) && !empty($item->analytical_controls)) {
                    $jsonControls = is_array($item->analytical_controls) ? 
                        $item->analytical_controls : 
                        json_decode($item->analytical_controls, true);
                    
                    if (is_array($jsonControls)) {
                        foreach ($jsonControls as $key => $controlData) {
                            if (is_array($controlData)) {
                                $dprArena = (float)($controlData['dpr_arena'] ?? 0);
                                $dprArcilla = (float)($controlData['dpr_arcilla'] ?? 0);
                                $dprLimo = (float)($controlData['dpr_limo'] ?? 0);
                                
                                if ($dprArena >= $dprMin || $dprArcilla >= $dprMin || $dprLimo >= $dprMin) {
                                    $cumpleDprMin = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Verificar en la relación analyticalControls
                if (!$cumpleDprMin && isset($item->analyticalControls) && $item->analyticalControls->count() > 0) {
                    foreach ($item->analyticalControls as $control) {
                        if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                            foreach ($control->controles_analiticos as $key => $controlData) {
                                if (is_array($controlData)) {
                                    $dprArena = (float)($controlData['dpr_arena'] ?? 0);
                                    $dprArcilla = (float)($controlData['dpr_arcilla'] ?? 0);
                                    $dprLimo = (float)($controlData['dpr_limo'] ?? 0);
                                    
                                    if ($dprArena >= $dprMin || $dprArcilla >= $dprMin || $dprLimo >= $dprMin) {
                                        $cumpleDprMin = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (!$cumpleDprMin) {
                    return false;
                }
            }
            
            if (isset($filters['dpr_max']) && is_numeric($filters['dpr_max'])) {
                $dprMax = (float)$filters['dpr_max'];
                $cumpleDprMax = false;
                
                // Verificar en el campo JSON del análisis
                if (isset($item->analytical_controls) && !empty($item->analytical_controls)) {
                    $jsonControls = is_array($item->analytical_controls) ? 
                        $item->analytical_controls : 
                        json_decode($item->analytical_controls, true);
                    
                    if (is_array($jsonControls)) {
                        foreach ($jsonControls as $key => $controlData) {
                            if (is_array($controlData)) {
                                $dprArena = (float)($controlData['dpr_arena'] ?? 0);
                                $dprArcilla = (float)($controlData['dpr_arcilla'] ?? 0);
                                $dprLimo = (float)($controlData['dpr_limo'] ?? 0);
                                
                                if ($dprArena <= $dprMax || $dprArcilla <= $dprMax || $dprLimo <= $dprMax) {
                                    $cumpleDprMax = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Verificar en la relación analyticalControls
                if (!$cumpleDprMax && isset($item->analyticalControls) && $item->analyticalControls->count() > 0) {
                    foreach ($item->analyticalControls as $control) {
                        if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                            foreach ($control->controles_analiticos as $key => $controlData) {
                                if (is_array($controlData)) {
                                    $dprArena = (float)($controlData['dpr_arena'] ?? 0);
                                    $dprArcilla = (float)($controlData['dpr_arcilla'] ?? 0);
                                    $dprLimo = (float)($controlData['dpr_limo'] ?? 0);
                                    
                                    if ($dprArena <= $dprMax || $dprArcilla <= $dprMax || $dprLimo <= $dprMax) {
                                        $cumpleDprMax = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (!$cumpleDprMax) {
                    return false;
                }
            }
            
            // Filtro por presencia de controles analíticos
            if (isset($filters['tiene_controles']) && $filters['tiene_controles'] === '1') {
                $tieneControles = false;
                
                // Verificar en el campo JSON del análisis
                if (isset($item->analytical_controls) && !empty($item->analytical_controls)) {
                    $jsonControls = is_array($item->analytical_controls) ? 
                        $item->analytical_controls : 
                        json_decode($item->analytical_controls, true);
                    
                    if (is_array($jsonControls) && count($jsonControls) > 0) {
                        $tieneControles = true;
                    }
                }
                
                // Verificar en la relación analyticalControls
                if (!$tieneControles && isset($item->analyticalControls) && $item->analyticalControls->count() > 0) {
                    $tieneControles = true;
                }
                
                if (!$tieneControles) {
                    return false;
                }
            }
            
            if (isset($filters['tiene_controles']) && $filters['tiene_controles'] === '0') {
                $tieneControles = false;
                
                // Verificar en el campo JSON del análisis
                if (isset($item->analytical_controls) && !empty($item->analytical_controls)) {
                    $jsonControls = is_array($item->analytical_controls) ? 
                        $item->analytical_controls : 
                        json_decode($item->analytical_controls, true);
                    
                    if (is_array($jsonControls) && count($jsonControls) > 0) {
                        $tieneControles = true;
                    }
                }
                
                // Verificar en la relación analyticalControls
                if (!$tieneControles && isset($item->analyticalControls) && $item->analyticalControls->count() > 0) {
                    $tieneControles = true;
                }
                
                if ($tieneControles) {
                    return false;
                }
            }
            
            return true;
        });
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

            // Asegurar que se obtenga el usuario correctamente
            $user = $analysis->user;
            if (!$user && isset($analysis->user_id)) {
                // Si no se cargó la relación, intentar cargar el usuario manualmente
                $user = \App\Models\User::find($analysis->user_id);
            }

            // Para análisis de pH y conductividad, intentar obtener el código de probeta del primer item si no existe
            $codigoProbeta = $analysis->codigo_probeta;
            if (!$codigoProbeta && !empty($items)) {
                $codigoProbeta = $items[0]['codigo_probeta'] ?? $items[0]['identificacion'] ?? null;
            }

            return (object)[
                'id' => $analysis->id,
                'type' => $type,
                'analysis_id' => $analysis->analysis_id,
                'service_id' => $analysis->service_id ?? null,
                'consecutivo_no' => $analysis->consecutivo_no,
                'fecha_analisis' => $analysis->fecha_analisis,
                'codigo_probeta' => $codigoProbeta,
                'codigo_equipo' => $analysis->codigo_equipo ?? null,
                'review_status' => $analysis->review_status ?? 'pending',
                'user' => $user,
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
        // Asegurar que samples sea un array
        $items = [];
        if (is_array($analysis->samples)) {
            $items = $analysis->samples;
        } elseif (is_string($analysis->samples)) {
            $items = json_decode($analysis->samples, true) ?? [];
        }
        
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
            'user' => $analysis->user ?? (object)['name' => $analysis->analyst_name ?? 'N/A'], // Analista
                'process' => $process,
                'quote' => $quote,
                'customer' => $customer,
            'first_item' => (object)$item,
                'created_at' => $analysis->created_at,
            'items_ensayo' => [$item]
            ];
    }

    /**
     * Transforma un análisis de micronutrientes al formato común
     */
    protected function transformMicronutrientsAnalysis($analysis, $type)
    {
        // Para micronutrientes, usar los items_ensayo del análisis
        $items = is_array($analysis->items_ensayo) ? $analysis->items_ensayo : [];
        $firstItem = !empty($items) ? (object)$items[0] : null;
        
        $process = $analysis->analysis->process ?? null;
        $quote = $process->quote ?? null;
        $customer = $quote->customer ?? null;
        
        // Servicio: mostrar el tipo de análisis en texto
        $serviceName = 'Micronutrientes';

        return (object)[
            'id' => $analysis->id,
            'type' => $type, // Esto se usará para el badge arriba
            'service_name' => $serviceName,
            'analysis_id' => $analysis->analysis_id,
            'consecutivo_no' => $analysis->consecutivo_no ?? 'N/A',
            'fecha_analisis' => $analysis->fecha_analisis,
            'codigo_probeta' => $firstItem ? ($firstItem->codigo_interno ?? 'N/A') : 'N/A',
            'codigo_equipo' => $analysis->equipo_utilizado,
            'review_status' => $analysis->review_status ?? 'pending',
            'user' => $analysis->user ?? (object)['name' => 'N/A'], // Analista
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'first_item' => $firstItem,
            'created_at' => $analysis->created_at,
            'items_ensayo' => $items
        ];
    }

    /**
     * Transforma un análisis de intercambio catiónico para la vista de revisiones
     */
    protected function transformCationicAnalysis($analysis, $type)
    {
        $process = $analysis->process ?? null;
        $quote = $process->quote ?? null;
        $customer = $quote->customer ?? null;
        
        // Servicio: mostrar el tipo de análisis en texto
        $serviceName = 'Intercambio Catiónico';

        return (object)[
            'id' => $analysis->id,
            'type' => $type, // Esto se usará para el badge arriba
            'service_name' => $serviceName,
            'analysis_id' => $analysis->id, // Para intercambio catiónico, usar el mismo ID
            'consecutivo_no' => $analysis->consecutivo_no ?? 'N/A',
            'fecha_analisis' => $analysis->fecha_analisis,
            'codigo_probeta' => $analysis->codigo_interno ?? 'N/A',
            'codigo_equipo' => $analysis->equipo_utilizado,
            'review_status' => $analysis->review_status ?? 'pending',
            'user' => (object)['name' => $analysis->nombre_analista ?? 'N/A'], // Analista
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'first_item' => $analysis,
            'created_at' => $analysis->created_at,
            'analytical_control' => $analysis->analyticalControl,
            'items_ensayo' => [] // Para intercambio catiónico, array vacío ya que no usa items_ensayo
        ];
    }

    /**
     * Transforma un análisis de azufre para la vista de revisión
     */
    protected function transformSulfurAnalysis($analysis, $type)
    {
        $process = $analysis->process ?? null;
        $quote = $process->quote ?? null;
        $customer = $quote->customer ?? null;
        
        // Servicio: mostrar el tipo de análisis en texto
        $serviceName = 'Análisis de Azufre';

        return (object)[
            'id' => $analysis->id,
            'type' => $type, // Esto se usará para el badge arriba
            'service_name' => $serviceName,
            'analysis_id' => $analysis->id, // Para azufre, usar el mismo ID
            'consecutivo_no' => $analysis->consecutive_no ?? 'N/A',
            'fecha_analisis' => $analysis->analysis_date,
            'codigo_probeta' => $analysis->internal_code ?? 'N/A',
            'codigo_equipo' => $analysis->equipment_used,
            'review_status' => $analysis->review_status ?? 'pending',
            'user' => (object)['name' => $analysis->analyst_name ?? 'N/A'], // Analista
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'first_item' => $analysis,
            'created_at' => $analysis->created_at,
            'analytical_control' => $analysis->analyticalControl,
            'items_ensayo' => [] // Para azufre, array vacío ya que no usa items_ensayo
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
                        $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with(['process.quote.customer', 'analyticalControls'])->find($id);
                        $type = $analysis ? 'texture' : null;
                        break;
                    case 'boron':
                        $analysis = BoronAnalysisDetail::with(['process.quote.customer','service','user'])->find($id);
                        $type = $analysis ? 'boron' : null;
                        break;
                    case 'micronutrients':
                        $analysis = MicronutrientsAnalysis::with(['analysis.process.quote.customer','analysis.service','user'])->find($id);
                        $type = $analysis ? 'micronutrients' : null;
                        if (!$analysis) {
                            $phTmp = PhAnalysis::with(['analysis'])->find($id);
                            $condTmp = $phTmp ? null : ConductivityAnalysis::with(['analysis'])->find($id);
                            $procId = $phTmp->analysis->process_id ?? $phTmp->process_id ?? ($condTmp->analysis->process_id ?? $condTmp->process_id ?? null);
                            $servId = $phTmp->analysis->service_id ?? $phTmp->service_id ?? ($condTmp->analysis->service_id ?? $condTmp->service_id ?? null);
                            if ($procId && $servId) {
                                $analysis = BoronAnalysisDetail::with(['process.quote.customer','service','user'])
                                    ->where('process_id', $procId)
                                    ->where('service_id', $servId)
                                    ->orderByDesc('id')
                                    ->first();
                                if ($analysis) { $type = 'boron'; }
                            }
                        }
                        break;
                    case 'cationic':
                        $analysis = CationicAnalysis::with(['process.quote.customer','analyticalControl'])->find($id);
                        $type = $analysis ? 'cationic' : null;
                        break;
                    case 'sulfur':
                        $analysis = SulfurAnalysis::with(['process.quote.customer','analyticalControl'])->find($id);
                        $type = $analysis ? 'sulfur' : null;
                        break;
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
                    'process.quote', 'process.customer', 'user'
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
                $textureAnalysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with(['process.quote.customer', 'analyticalControls'])->find($id);
                if ($textureAnalysis) {
                    $analysis = $textureAnalysis;
                    $type = 'texture';
                }
            }
            if (!$analysis) {
                $boronAnalysis = BoronAnalysisDetail::with(['process.quote.customer','service','user'])->find($id);
                if ($boronAnalysis) {
                    $analysis = $boronAnalysis;
                    $type = 'boron';
                }
            }
            if (!$analysis) {
                $micronutrientsAnalysis = MicronutrientsAnalysis::with(['analysis.process.quote.customer','analysis.service','user'])->find($id);
                if ($micronutrientsAnalysis) {
                    $analysis = $micronutrientsAnalysis;
                    $type = 'micronutrients';
                }
            }
            if (!$analysis) {
                $cationicAnalysis = CationicAnalysis::with(['process.quote.customer','analyticalControl'])->find($id);
                if ($cationicAnalysis) {
                    $analysis = $cationicAnalysis;
                    $type = 'cationic';
                }
            }
            if (!$analysis) {
                $sulfurAnalysis = SulfurAnalysis::with(['process.quote.customer','analyticalControl'])->find($id);
                if ($sulfurAnalysis) {
                    $analysis = $sulfurAnalysis;
                    $type = 'sulfur';
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
            
            // Para humedad, resolver el detail solo por process_id
            if (!$detail && $type === 'humidity') {
                try {
                    if (!empty($analysis->process_id)) {
                        $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                            ->where('service_id', 'LIKE', '%humedad%')
                            ->orWhere('service_id', 'LIKE', '%humidity%')
                            ->first();
                    }
                } catch (\Throwable $e) {
                    \Log::warning('ReviewController@show: no se pudo resolver ServiceProcessDetail para humedad - '.$e->getMessage());
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
            'texture' => 'lscefa::reviews.texture_readonly',
            'boron' => 'lscefa::reviews.boron_readonly',
            'micronutrients' => 'lscefa::reviews.micronutrients_readonly',
            'cationic' => 'lscefa::reviews.cationic_readonly',
            'sulfur' => 'lscefa::reviews.sulfur_readonly',
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
            } elseif ($type === 'texture') {
                $viewData = array_merge($viewData, $this->prepareTextureData($analysis));
            } elseif ($type === 'boron') {
                $viewData = array_merge($viewData, $this->prepareBoronData($analysis));
            } elseif ($type === 'micronutrients') {
                $viewData = array_merge($viewData, $this->prepareMicronutrientsData($analysis));
            } elseif ($type === 'cationic') {
                $viewData = array_merge($viewData, $this->prepareCationicData($analysis));
            } elseif ($type === 'sulfur') {
                $viewData = array_merge($viewData, $this->prepareSulfurData($analysis));
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
            
            // Log específico para debugging de Boro
            if ($type === 'boron') {
                \Log::info('ReviewController@show: Análisis de Boro detectado', [
                    'analysis_id' => $analysis->id,
                    'view' => $view,
                    'has_prepareBoronData' => method_exists($this, 'prepareBoronData')
                ]);
            }

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
        // Obtener todos los análisis de humedad del mismo proceso
        $allHumidityAnalyses = HumidityAnalysis::where('process_id', $analysis->process_id)
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
            ->where('humidity_analysis_id', $analysis->id) // humidity_analysis_id directo
            ->get();
        
        // Debug: Log de la consulta y resultados
        \Log::info('Consulta controles analíticos para humedad:', [
            'process_id' => $analysis->process_id,
            'humidity_analysis_id' => $analysis->id,
            'controles_encontrados' => $controles_analiticos->count(),
            'sql' => \Modules\LSCEFA\Entities\AnalyticalControl::where('process_id', $analysis->process_id)
                ->where('humidity_analysis_id', $analysis->id)->toSql()
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
            'analysis_type' => ['required', 'in:ph,conductivity,humidity,phosphorus,texture,boron,micronutrients,cationic,sulfur'],
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
        } elseif ($validated['analysis_type'] === 'micronutrients') {
            $analysis = MicronutrientsAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'cationic') {
            $analysis = CationicAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'sulfur') {
            $analysis = SulfurAnalysis::findOrFail($id);
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
                    } elseif ($validated['analysis_type'] === 'micronutrients') {
                        // Para micronutrientes, extraer datos de los items_ensayo
                        $items = is_array($analysis->items_ensayo) ? $analysis->items_ensayo : [];
                        foreach ($items as $item) {
                            if (is_array($item) || is_object($item)) {
                                $item = is_object($item) ? (array)$item : $item;
                                $resultsOnly[] = [
                                    'identificacion' => $item['codigo_interno'] ?? null,
                                    'resultado' => $item['mn_resultado'] ?? $item['fe_resultado'] ?? $item['zn_resultado'] ?? $item['cu_resultado'] ?? null,
                                ];
                            }
                        }
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
            
            // Verificar análisis de micronutrientes
            if ($validated['analysis_type'] === 'micronutrients') {
                // Para micronutrientes, verificar si hay otros análisis del mismo proceso
                $otherMicronutrientsAnalyses = MicronutrientsAnalysis::where('analysis_id', $analysis->analysis_id)
                    ->where('id', '!=', $analysis->id)
                    ->get();
                
                foreach ($otherMicronutrientsAnalyses as $otherAnalysis) {
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
            'analysis_type' => ['required', 'in:ph,conductivity,humidity,phosphorus,texture,boron,micronutrients,cationic,sulfur'],
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
        } elseif ($validated['analysis_type'] === 'micronutrients') {
            $analysis = MicronutrientsAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'cationic') {
            $analysis = CationicAnalysis::findOrFail($id);
        } elseif ($validated['analysis_type'] === 'sulfur') {
            $analysis = SulfurAnalysis::findOrFail($id);
        }

        // Actualizar el estado de revisión
        $analysis->review_status = 'rejected';
        $analysis->review_observations = $validated['observations'];
        $analysis->reviewed_by = auth()->id();
        $analysis->review_date = now();
        $analysis->save();

        // Marcar el detalle según el tipo de análisis
        if ($validated['analysis_type'] === 'phosphorus' || $validated['analysis_type'] === 'boron' || $validated['analysis_type'] === 'micronutrients' || $validated['analysis_type'] === 'cationic' || $validated['analysis_type'] === 'sulfur') {
            $detail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
        } else {
            $detail = $analysis->analysis;
        }
        if ($detail) {
            if ($validated['analysis_type'] === 'texture' || $validated['analysis_type'] === 'boron' || $validated['analysis_type'] === 'sulfur') {
                // Para textura, boro y azufre, poner en pending para que aparezca en la tabla de análisis devueltos
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

    /**
     * Obtiene estadísticas de los análisis de textura filtrados
     */
    protected function getTextureAnalysisStats($textureAnalyses)
    {
        $stats = [
            'total_analisis' => $textureAnalyses->count(),
            'por_estado' => [],
            'por_analista' => [],
            'por_metodologia' => [],
            'por_equipo' => [],
            'clases_texturales' => [],
            'rangos_fechas' => [],
            'porcentajes_componentes' => [
                'arena' => ['min' => null, 'max' => null, 'promedio' => 0],
                'arcilla' => ['min' => null, 'max' => null, 'promedio' => 0],
                'limo' => ['min' => null, 'max' => null, 'promedio' => 0]
            ],
            'controles_analiticos' => [
                'total_controles' => 0,
                'duplicados' => 0,
                'material_referencia' => 0,
                'aceptables' => 0,
                'no_aceptables' => 0
            ]
        ];

        $totalArena = 0;
        $totalArcilla = 0;
        $totalLimo = 0;
        $countComponentes = 0;

        foreach ($textureAnalyses as $analysis) {
            // Estadísticas por estado de revisión
            $estado = $analysis->review_status ?? 'pending';
            $stats['por_estado'][$estado] = ($stats['por_estado'][$estado] ?? 0) + 1;

            // Estadísticas por analista
            $analista = $analysis->analyst_name ?? 'Sin especificar';
            $stats['por_analista'][$analista] = ($stats['por_analista'][$analista] ?? 0) + 1;

            // Estadísticas por metodología
            $metodologia = $analysis->methodology_used ?? 'Sin especificar';
            $stats['por_metodologia'][$metodologia] = ($stats['por_metodologia'][$metodologia] ?? 0) + 1;

            // Estadísticas por equipo
            $equipo = $analysis->equipment_used ?? 'Sin especificar';
            $stats['por_equipo'][$equipo] = ($stats['por_equipo'][$equipo] ?? 0) + 1;

            // Estadísticas de fechas
            if ($analysis->analysis_date) {
                $fecha = \Carbon\Carbon::parse($analysis->analysis_date);
                $mes = $fecha->format('Y-m');
                $stats['rangos_fechas'][$mes] = ($stats['rangos_fechas'][$mes] ?? 0) + 1;
            }

            // Estadísticas de componentes texturales
            $samples = is_array($analysis->samples) ? $analysis->samples : [];
            foreach ($samples as $sample) {
                if (is_array($sample)) {
                    // Clases texturales
                    if (isset($sample['clase_textural'])) {
                        $clase = $sample['clase_textural'];
                        $stats['clases_texturales'][$clase] = ($stats['clases_texturales'][$clase] ?? 0) + 1;
                    }

                    // Porcentajes de componentes
                    if (isset($sample['arena']) && is_numeric($sample['arena'])) {
                        $arena = (float)$sample['arena'];
                        $stats['porcentajes_componentes']['arena']['min'] = 
                            $stats['porcentajes_componentes']['arena']['min'] === null ? 
                            $arena : min($stats['porcentajes_componentes']['arena']['min'], $arena);
                        $stats['porcentajes_componentes']['arena']['max'] = 
                            $stats['porcentajes_componentes']['arena']['max'] === null ? 
                            $arena : max($stats['porcentajes_componentes']['arena']['max'], $arena);
                        $totalArena += $arena;
                        $countComponentes++;
                    }

                    if (isset($sample['arcilla']) && is_numeric($sample['arcilla'])) {
                        $arcilla = (float)$sample['arcilla'];
                        $stats['porcentajes_componentes']['arcilla']['min'] = 
                            $stats['porcentajes_componentes']['arcilla']['min'] === null ? 
                            $arcilla : min($stats['porcentajes_componentes']['arcilla']['min'], $arcilla);
                        $stats['porcentajes_componentes']['arcilla']['max'] = 
                            $stats['porcentajes_componentes']['arcilla']['max'] === null ? 
                            $arcilla : max($stats['porcentajes_componentes']['arcilla']['max'], $arcilla);
                        $totalArcilla += $arcilla;
                    }

                    if (isset($sample['limo']) && is_numeric($sample['limo'])) {
                        $limo = (float)$sample['limo'];
                        $stats['porcentajes_componentes']['limo']['min'] = 
                            $stats['porcentajes_componentes']['limo']['min'] === null ? 
                            $limo : min($stats['porcentajes_componentes']['limo']['min'], $limo);
                        $stats['porcentajes_componentes']['limo']['max'] = 
                            $stats['porcentajes_componentes']['limo']['max'] === null ? 
                            $limo : max($stats['porcentajes_componentes']['limo']['max'], $limo);
                        $totalLimo += $limo;
                    }
                }
            }

            // Estadísticas de controles analíticos
            if (isset($analysis->analytical_controls) && !empty($analysis->analytical_controls)) {
                $jsonControls = is_array($analysis->analytical_controls) ? 
                    $analysis->analytical_controls : 
                    json_decode($analysis->analytical_controls, true);
                
                if (is_array($jsonControls)) {
                    foreach ($jsonControls as $key => $controlData) {
                        if (is_array($controlData)) {
                            $stats['controles_analiticos']['total_controles']++;
                            
                            // Contar duplicados
                            if (isset($controlData['identificacion']) && 
                                (strpos(strtolower($controlData['identificacion']), 'duplicado') !== false ||
                                 strpos(strtolower($controlData['identificacion']), 'duplicate') !== false)) {
                                $stats['controles_analiticos']['duplicados']++;
                            }
                            
                            // Contar material de referencia
                            if (isset($controlData['identificacion']) && 
                                (strpos(strtolower($controlData['identificacion']), 'material de referencia') !== false ||
                                 strpos(strtolower($controlData['identificacion']), 'reference material') !== false)) {
                                $stats['controles_analiticos']['material_referencia']++;
                            }
                            
                            // Contar por aceptabilidad
                            if (isset($controlData['aceptabilidad_control'])) {
                                $aceptable = strtolower($controlData['aceptabilidad_control']);
                                if (strpos($aceptable, 'aceptable') !== false || strpos($aceptable, 'acceptable') !== false) {
                                    $stats['controles_analiticos']['aceptables']++;
                                } else {
                                    $stats['controles_analiticos']['no_aceptables']++;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Calcular promedios
        if ($countComponentes > 0) {
            $stats['porcentajes_componentes']['arena']['promedio'] = round($totalArena / $countComponentes, 2);
            $stats['porcentajes_componentes']['arcilla']['promedio'] = round($totalArcilla / $countComponentes, 2);
            $stats['porcentajes_componentes']['limo']['promedio'] = round($totalLimo / $countComponentes, 2);
        }

        // Ordenar estadísticas
        arsort($stats['por_estado']);
        arsort($stats['por_analista']);
        arsort($stats['por_metodologia']);
        arsort($stats['por_equipo']);
        arsort($stats['clases_texturales']);
        arsort($stats['rangos_fechas']);

        return $stats;
    }

    /**
     * Exporta los análisis de textura filtrados a Excel
     */
    public function exportTextureAnalyses(Request $request)
    {
        try {
            // Obtener análisis de textura con filtros
            $textureAnalyses = \Modules\LSCEFA\Entities\BatchTextureAnalysis::where(function($q) {
                $q->whereNotNull('process_id')
                  ->whereNotNull('service_id');
            })->get()->filter(function($item) {
                $spd = \Modules\LSCEFA\Models\ServiceProcessDetail::where('process_id', $item->process_id)
                    ->where('service_id', $item->service_id)
                    ->first();
                return $spd && $spd->status === 'completed';
            });

            // Aplicar filtros si existen
            if ($request->filled('texture_filter')) {
                $textureFilter = $request->get('texture_filter');
                $textureAnalyses = $this->applyTextureFilters($textureAnalyses, $textureFilter);
            }

            // Preparar datos para exportación
            $exportData = [];
            foreach ($textureAnalyses as $analysis) {
                $samples = is_array($analysis->samples) ? $analysis->samples : [];
                
                foreach ($samples as $sample) {
                    if (is_array($sample) && isset($sample['codigo_interno']) && $sample['codigo_interno'] !== 'Blanco del proceso') {
                        $exportData[] = [
                            'ID Análisis' => $analysis->id,
                            'Consecutivo' => $analysis->consecutive_no,
                            'Fecha Análisis' => $analysis->analysis_date ? \Carbon\Carbon::parse($analysis->analysis_date)->format('d/m/Y') : 'N/A',
                            'Analista' => $analysis->analyst_name ?? 'N/A',
                            'Metodología' => $analysis->methodology_used ?? 'N/A',
                            'Equipo Utilizado' => $analysis->equipment_used ?? 'N/A',
                            'Código Termómetro' => $analysis->thermometer_code ?? 'N/A',
                            'Código Hidrómetro' => $analysis->hydrometer_code ?? 'N/A',
                            'Código Muestra' => $sample['codigo_interno'] ?? 'N/A',
                            'Clase Textural' => $sample['clase_textural'] ?? 'N/A',
                            'Arena (%)' => $sample['arena'] ?? 'N/A',
                            'Arcilla (%)' => $sample['arcilla'] ?? 'N/A',
                            'Limo (%)' => $sample['limo'] ?? 'N/A',
                                                                'Estado Revisión' => $analysis->review_status ?? 'pending',
                                    'Observaciones' => $analysis->general_observations ?? 'N/A',
                                    'Fecha Creación' => $analysis->created_at ? \Carbon\Carbon::parse($analysis->created_at)->format('d/m/Y H:i:s') : 'N/A',
                                    'Tipo Control' => 'Muestra'
                        ];
                    }
                }
                
                // Agregar controles analíticos si existen
                if (isset($analysis->analytical_controls) && !empty($analysis->analytical_controls)) {
                    $jsonControls = is_array($analysis->analytical_controls) ? 
                        $analysis->analytical_controls : 
                        json_decode($analysis->analytical_controls, true);
                    
                    if (is_array($jsonControls)) {
                        foreach ($jsonControls as $key => $controlData) {
                            if (is_array($controlData)) {
                                $exportData[] = [
                                    'ID Análisis' => $analysis->id . ' (Control)',
                                    'Consecutivo' => $analysis->consecutive_no,
                                    'Fecha Análisis' => $analysis->analysis_date ? \Carbon\Carbon::parse($analysis->analysis_date)->format('d/m/Y') : 'N/A',
                                    'Analista' => $analysis->analyst_name ?? 'N/A',
                                    'Metodología' => $analysis->methodology_used ?? 'N/A',
                                    'Equipo Utilizado' => $analysis->equipment_used ?? 'N/A',
                                    'Código Termómetro' => $analysis->thermometer_code ?? 'N/A',
                                    'Código Hidrómetro' => $analysis->hydrometer_code ?? 'N/A',
                                    'Código Muestra' => $controlData['codigo_interno'] ?? $controlData['identificacion'] ?? 'N/A',
                                    'Clase Textural' => 'Control Analítico',
                                    'Arena (%)' => $controlData['arena_1'] ?? $controlData['arena'] ?? 'N/A',
                                    'Arcilla (%)' => $controlData['arcilla_1'] ?? $controlData['arcilla'] ?? 'N/A',
                                    'Limo (%)' => $controlData['limo_1'] ?? $controlData['limo'] ?? 'N/A',
                                    'Estado Revisión' => $analysis->review_status ?? 'pending',
                                    'Observaciones' => $controlData['observaciones'] ?? 'N/A',
                                    'Fecha Creación' => $analysis->created_at ? \Carbon\Carbon::parse($analysis->created_at)->format('d/m/Y H:i:s') : 'N/A',
                                    'Tipo Control' => 'Control Analítico'
                                ];
                            }
                        }
                    }
                }
            }

            // Generar nombre del archivo
            $filename = 'analisis_textura_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Exportar usando Laravel Excel si está disponible
            if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \Modules\LSCEFA\Exports\TextureAnalysisExport($exportData),
                    $filename
                );
            } else {
                // Fallback: exportar como CSV
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($exportData) {
                    $file = fopen('php://output', 'w');
                    
                    // Encabezados
                    if (!empty($exportData)) {
                        fputcsv($file, array_keys($exportData[0]));
                        
                        // Datos
                        foreach ($exportData as $row) {
                            fputcsv($file, $row);
                        }
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

        } catch (\Exception $e) {
            \Log::error('Error exportando análisis de textura: ' . $e->getMessage());
            return back()->with('error', 'Error al exportar los análisis de textura: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene un resumen de los filtros aplicados a los análisis de textura
     */
    protected function getTextureFiltersSummary($filters)
    {
        if (!$filters || !is_array($filters)) {
            return null;
        }

        $summary = [];
        
        if (isset($filters['codigo_muestra']) && !empty($filters['codigo_muestra'])) {
            $summary[] = "Código de muestra: " . $filters['codigo_muestra'];
        }
        
        if (isset($filters['analista']) && !empty($filters['analista'])) {
            $summary[] = "Analista: " . $filters['analista'];
        }
        
        if (isset($filters['fecha_desde']) && !empty($filters['fecha_desde'])) {
            $summary[] = "Desde: " . $filters['fecha_desde'];
        }
        
        if (isset($filters['fecha_hasta']) && !empty($filters['fecha_hasta'])) {
            $summary[] = "Hasta: " . $filters['fecha_hasta'];
        }
        
        if (isset($filters['metodologia']) && !empty($filters['metodologia'])) {
            $summary[] = "Metodología: " . $filters['metodologia'];
        }
        
        if (isset($filters['equipo']) && !empty($filters['equipo'])) {
            $summary[] = "Equipo: " . $filters['equipo'];
        }
        
        if (isset($filters['estado_revision']) && !empty($filters['estado_revision'])) {
            $summary[] = "Estado: " . $filters['estado_revision'];
        }
        
        if (isset($filters['codigo_termometro']) && !empty($filters['codigo_termometro'])) {
            $summary[] = "Termómetro: " . $filters['codigo_termometro'];
        }
        
        if (isset($filters['codigo_hidrometro']) && !empty($filters['codigo_hidrometro'])) {
            $summary[] = "Hidrómetro: " . $filters['codigo_hidrometro'];
        }
        
        if (isset($filters['muestra_especifica']) && !empty($filters['muestra_especifica'])) {
            $summary[] = "Muestra específica: " . $filters['muestra_especifica'];
        }
        
        if (isset($filters['blanco_metodo']) && !empty($filters['blanco_metodo'])) {
            $summary[] = "Blanco del método: " . $filters['blanco_metodo'];
        }
        
        if (isset($filters['clase_textural']) && !empty($filters['clase_textural'])) {
            $summary[] = "Clase textural: " . $filters['clase_textural'];
        }
        
        if (isset($filters['porcentaje_min']) && is_numeric($filters['porcentaje_min'])) {
            $summary[] = "Porcentaje mínimo: " . $filters['porcentaje_min'] . "%";
        }
        
        if (isset($filters['porcentaje_max']) && is_numeric($filters['porcentaje_max'])) {
            $summary[] = "Porcentaje máximo: " . $filters['porcentaje_max'] . "%";
        }
        
        // Filtros de controles analíticos
        if (isset($filters['tipo_control']) && !empty($filters['tipo_control'])) {
            $summary[] = "Tipo de control: " . $filters['tipo_control'];
        }
        
        if (isset($filters['aceptabilidad_control']) && !empty($filters['aceptabilidad_control'])) {
            $summary[] = "Aceptabilidad: " . $filters['aceptabilidad_control'];
        }
        
        if (isset($filters['dpr_min']) && is_numeric($filters['dpr_min'])) {
            $summary[] = "DPR mínimo: " . $filters['dpr_min'] . "%";
        }
        
        if (isset($filters['dpr_max']) && is_numeric($filters['dpr_max'])) {
            $summary[] = "DPR máximo: " . $filters['dpr_max'] . "%";
        }
        
        if (isset($filters['tiene_controles'])) {
            if ($filters['tiene_controles'] === '1') {
                $summary[] = "Con controles analíticos";
            } else {
                $summary[] = "Sin controles analíticos";
            }
        }

        return $summary;
    }

    /**
     * Obtiene filtros predefinidos comunes para análisis de textura
     */
    protected function getPredefinedTextureFilters()
    {
        return [
            'pendientes_revision' => [
                'name' => 'Pendientes de Revisión',
                'filters' => ['estado_revision' => 'pending']
            ],
            'ultima_semana' => [
                'name' => 'Última Semana',
                'filters' => [
                    'fecha_desde' => now()->subWeek()->format('Y-m-d'),
                    'fecha_hasta' => now()->format('Y-m-d')
                ]
            ],
            'ultimo_mes' => [
                'name' => 'Último Mes',
                'filters' => [
                    'fecha_desde' => now()->subMonth()->format('Y-m-d'),
                    'fecha_hasta' => now()->format('Y-m-d')
                ]
            ],
            'alta_arena' => [
                'name' => 'Alta Arena (>70%)',
                'filters' => ['porcentaje_min' => 70]
            ],
            'alta_arcilla' => [
                'name' => 'Alta Arcilla (>40%)',
                'filters' => ['porcentaje_min' => 40]
            ],
            'alta_limo' => [
                'name' => 'Alto Limo (>50%)',
                'filters' => ['porcentaje_min' => 50]
            ],
            'con_controles' => [
                'name' => 'Con Controles Analíticos',
                'filters' => ['tiene_controles' => '1']
            ],
            'sin_controles' => [
                'name' => 'Sin Controles Analíticos',
                'filters' => ['tiene_controles' => '0']
            ],
            'controles_aceptables' => [
                'name' => 'Controles Aceptables',
                'filters' => ['aceptabilidad_control' => 'aceptable']
            ],
            'controles_no_aceptables' => [
                'name' => 'Controles No Aceptables',
                'filters' => ['aceptabilidad_control' => 'no aceptable']
            ],
            'con_duplicados' => [
                'name' => 'Con Duplicados',
                'filters' => ['tipo_control' => 'duplicado']
            ],
            'con_material_referencia' => [
                'name' => 'Con Material de Referencia',
                'filters' => ['tipo_control' => 'material de referencia']
            ],
            'dpr_alto' => [
                'name' => 'DPR Alto (>5%)',
                'filters' => ['dpr_min' => 5]
            ],
            'dpr_bajo' => [
                'name' => 'DPR Bajo (<2%)',
                'filters' => ['dpr_max' => 2]
            ],
            'con_blanco' => [
                'name' => 'Con Blanco del Método',
                'filters' => ['blanco_metodo' => 'blanco']
            ]
        ];
    }

    /**
     * Limpia los filtros de textura aplicados
     */
    public function clearTextureFilters()
    {
        return redirect()->route('lscefa.quality.reviews.index')
            ->with('success', 'Filtros de textura limpiados correctamente.');
    }

    public function analyticalControls()
    {
        return $this->hasMany(AnalyticalControl::class, 'process_id', 'process_id')
            ->where('analysis_type', 'texture');
    }

    /**
     * Aplica filtros específicos para análisis de boro
     */
    protected function applyBoronFilters($boronAnalyses, $filters)
    {
        return $boronAnalyses->filter(function($item) use ($filters) {
            // Filtro por código de muestra (consecutivo)
            if (isset($filters['codigo_muestra']) && !empty($filters['codigo_muestra'])) {
                $codigoMuestra = strtolower(trim($filters['codigo_muestra']));
                $itemConsecutivo = strtolower($item->consecutive_no ?? '');
                if (strpos($itemConsecutivo, $codigoMuestra) === false) {
                    return false;
                }
            }
            
            // Filtro por analista
            if (isset($filters['analista']) && !empty($filters['analista'])) {
                $analista = strtolower(trim($filters['analista']));
                $itemAnalista = strtolower($item->analyst_name ?? '');
                if (strpos($itemAnalista, $analista) === false) {
                    return false;
                }
            }
            
            // Filtro por fecha de análisis
            if (isset($filters['fecha_desde']) && !empty($filters['fecha_desde'])) {
                try {
                    $fechaDesde = \Carbon\Carbon::parse($filters['fecha_desde']);
                    $itemFecha = \Carbon\Carbon::parse($item->analysis_date);
                    if ($itemFecha->lt($fechaDesde)) {
                        return false;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing fecha_desde: ' . $e->getMessage());
                }
            }
            
            if (isset($filters['fecha_hasta']) && !empty($filters['fecha_hasta'])) {
                try {
                    $fechaHasta = \Carbon\Carbon::parse($filters['fecha_hasta']);
                    $itemFecha = \Carbon\Carbon::parse($item->analysis_date);
                    if ($itemFecha->gt($fechaHasta)) {
                        return false;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing fecha_hasta: ' . $e->getMessage());
                }
            }
            
            // Filtro por metodología
            if (isset($filters['metodologia']) && !empty($filters['metodologia'])) {
                $metodologia = strtolower(trim($filters['metodologia']));
                $itemMetodologia = strtolower($item->methodology_used ?? '');
                if (strpos($itemMetodologia, $metodologia) === false) {
                    return false;
                }
            }
            
            // Filtro por equipo utilizado
            if (isset($filters['equipo']) && !empty($filters['equipo'])) {
                $equipo = strtolower(trim($filters['equipo']));
                $itemEquipo = strtolower($item->equipment_used ?? '');
                if (strpos($itemEquipo, $equipo) === false) {
                    return false;
                }
            }
            
            // Filtro por estado de revisión específico
            if (isset($filters['estado_revision']) && !empty($filters['estado_revision'])) {
                $estado = $filters['estado_revision'];
                $itemEstado = $item->review_status ?? 'pending';
                if ($estado !== $itemEstado) {
                    return false;
                }
            }
            
            // Filtro por código interno
            if (isset($filters['codigo_interno']) && !empty($filters['codigo_interno'])) {
                $codigoInterno = strtolower(trim($filters['codigo_interno']));
                $itemCodigoInterno = strtolower($item->codigo_interno ?? $item->internal_code ?? '');
                if (strpos($itemCodigoInterno, $codigoInterno) === false) {
                    return false;
                }
            }
            
            // Filtro por valor de boro
            if (isset($filters['valor_boro_min']) && is_numeric($filters['valor_boro_min'])) {
                $valorMin = (float)$filters['valor_boro_min'];
                $itemValor = (float)($item->boron_value ?? $item->valor_boro ?? 0);
                if ($itemValor < $valorMin) {
                    return false;
                }
            }
            
            if (isset($filters['valor_boro_max']) && is_numeric($filters['valor_boro_max'])) {
                $valorMax = (float)$filters['valor_boro_max'];
                $itemValor = (float)($item->boron_value ?? $item->valor_boro ?? 0);
                if ($itemValor > $valorMax) {
                    return false;
                }
            }
            
            // Filtro por unidad de medida
            if (isset($filters['unidad_medida']) && !empty($filters['unidad_medida'])) {
                $unidad = strtolower(trim($filters['unidad_medida']));
                $itemUnidad = strtolower($item->unit ?? $item->unidad ?? '');
                if (strpos($itemUnidad, $unidad) === false) {
                    return false;
                }
            }
            
            // Filtro por observaciones
            if (isset($filters['observaciones']) && !empty($filters['observaciones'])) {
                $observaciones = strtolower(trim($filters['observaciones']));
                $itemObservaciones = strtolower($item->observations ?? $item->observaciones ?? '');
                if (strpos($itemObservaciones, $observaciones) === false) {
                    return false;
                }
            }
            
            return true;
        });
    }

    /**
     * Obtiene un resumen de los filtros aplicados para análisis de boro
     */
    protected function getBoronFiltersSummary($filters)
    {
        if (!$filters || !is_array($filters)) {
            return null;
        }

        $summary = [];
        
        if (isset($filters['codigo_muestra']) && !empty($filters['codigo_muestra'])) {
            $summary[] = "Código de muestra: " . $filters['codigo_muestra'];
        }
        
        if (isset($filters['analista']) && !empty($filters['analista'])) {
            $summary[] = "Analista: " . $filters['analista'];
        }
        
        if (isset($filters['fecha_desde']) && !empty($filters['fecha_desde'])) {
            $summary[] = "Desde: " . $filters['fecha_desde'];
        }
        
        if (isset($filters['fecha_hasta']) && !empty($filters['fecha_hasta'])) {
            $summary[] = "Hasta: " . $filters['fecha_hasta'];
        }
        
        if (isset($filters['metodologia']) && !empty($filters['metodologia'])) {
            $summary[] = "Metodología: " . $filters['metodologia'];
        }
        
        if (isset($filters['equipo']) && !empty($filters['equipo'])) {
            $summary[] = "Equipo: " . $filters['equipo'];
        }
        
        if (isset($filters['estado_revision']) && !empty($filters['estado_revision'])) {
            $summary[] = "Estado: " . $filters['estado_revision'];
        }
        
        if (isset($filters['codigo_interno']) && !empty($filters['codigo_interno'])) {
            $summary[] = "Código interno: " . $filters['codigo_interno'];
        }
        
        if (isset($filters['valor_boro_min']) && is_numeric($filters['valor_boro_min'])) {
            $summary[] = "Valor mínimo: " . $filters['valor_boro_min'];
        }
        
        if (isset($filters['valor_boro_max']) && is_numeric($filters['valor_boro_max'])) {
            $summary[] = "Valor máximo: " . $filters['valor_boro_max'];
        }
        
        if (isset($filters['unidad_medida']) && !empty($filters['unidad_medida'])) {
            $summary[] = "Unidad: " . $filters['unidad_medida'];
        }
        
        if (isset($filters['observaciones']) && !empty($filters['observaciones'])) {
            $summary[] = "Observaciones: " . $filters['observaciones'];
        }

        return $summary;
    }

    /**
     * Obtiene filtros predefinidos comunes para análisis de boro
     */
    protected function getPredefinedBoronFilters()
    {
        return [
            'pendientes_revision' => [
                'name' => 'Pendientes de Revisión',
                'filters' => ['estado_revision' => 'pending']
            ],
            'ultima_semana' => [
                'name' => 'Última Semana',
                'filters' => [
                    'fecha_desde' => now()->subWeek()->format('Y-m-d'),
                    'fecha_hasta' => now()->format('Y-m-d')
                ]
            ],
            'ultimo_mes' => [
                'name' => 'Último Mes',
                'filters' => [
                    'fecha_desde' => now()->subMonth()->format('Y-m-d'),
                    'fecha_hasta' => now()->format('Y-m-d')
                ]
            ],
            'valor_alto' => [
                'name' => 'Valor Alto (>2.0)',
                'filters' => ['valor_boro_min' => 2.0]
            ],
            'valor_bajo' => [
                'name' => 'Valor Bajo (<0.5)',
                'filters' => ['valor_boro_max' => 0.5]
            ],
            'valor_medio' => [
                'name' => 'Valor Medio (0.5 - 2.0)',
                'filters' => [
                    'valor_boro_min' => 0.5,
                    'valor_boro_max' => 2.0
                ]
            ],
            'mg_kg' => [
                'name' => 'Unidad mg/kg',
                'filters' => ['unidad_medida' => 'mg/kg']
            ],
            'mg_l' => [
                'name' => 'Unidad mg/L',
                'filters' => ['unidad_medida' => 'mg/l']
            ],
            'ppm' => [
                'name' => 'Unidad ppm',
                'filters' => ['unidad_medida' => 'ppm']
            ]
        ];
    }

    /**
     * Obtiene estadísticas de los análisis de boro filtrados
     */
    protected function getBoronAnalysisStats($boronAnalyses)
    {
        $stats = [
            'total_analisis' => $boronAnalyses->count(),
            'por_estado' => [],
            'por_analista' => [],
            'por_metodologia' => [],
            'por_equipo' => [],
            'rangos_fechas' => [],
            'valores_boro' => [
                'min' => null,
                'max' => null,
                'promedio' => 0,
                'total' => 0
            ],
            'unidades_medida' => [],
            'rangos_valores' => [
                'bajo' => 0,      // < 0.5
                'medio' => 0,     // 0.5 - 2.0
                'alto' => 0       // > 2.0
            ]
        ];

        $totalValor = 0;
        $countValores = 0;

        foreach ($boronAnalyses as $analysis) {
            // Estadísticas por estado de revisión
            $estado = $analysis->review_status ?? 'pending';
            $stats['por_estado'][$estado] = ($stats['por_estado'][$estado] ?? 0) + 1;

            // Estadísticas por analista
            $analista = $analysis->analyst_name ?? 'Sin especificar';
            $stats['por_analista'][$analista] = ($stats['por_analista'][$analista] ?? 0) + 1;

            // Estadísticas por metodología
            $metodologia = $analysis->methodology_used ?? 'Sin especificar';
            $stats['por_metodologia'][$metodologia] = ($stats['por_metodologia'][$metodologia] ?? 0) + 1;

            // Estadísticas por equipo
            $equipo = $analysis->equipment_used ?? 'Sin especificar';
            $stats['por_equipo'][$equipo] = ($stats['por_equipo'][$equipo] ?? 0) + 1;

            // Estadísticas de fechas
            if ($analysis->analysis_date) {
                $fecha = \Carbon\Carbon::parse($analysis->analysis_date);
                $mes = $fecha->format('Y-m');
                $stats['rangos_fechas'][$mes] = ($stats['rangos_fechas'][$mes] ?? 0) + 1;
            }

            // Estadísticas de valores de boro
            $valorBoro = $analysis->boron_value ?? $analysis->valor_boro ?? null;
            if (is_numeric($valorBoro)) {
                $valorBoro = (float)$valorBoro;
                
                // Mínimo y máximo
                $stats['valores_boro']['min'] = 
                    $stats['valores_boro']['min'] === null ? 
                    $valorBoro : min($stats['valores_boro']['min'], $valorBoro);
                $stats['valores_boro']['max'] = 
                    $stats['valores_boro']['max'] === null ? 
                    $valorBoro : max($stats['valores_boro']['max'], $valorBoro);
                
                $totalValor += $valorBoro;
                $countValores++;

                // Rango de valores
                if ($valorBoro < 0.5) {
                    $stats['rangos_valores']['bajo']++;
                } elseif ($valorBoro <= 2.0) {
                    $stats['rangos_valores']['medio']++;
                } else {
                    $stats['rangos_valores']['alto']++;
                }
            }

            // Estadísticas de unidades de medida
            $unidad = $analysis->unit ?? $analysis->unidad ?? 'Sin especificar';
            $stats['unidades_medida'][$unidad] = ($stats['unidades_medida'][$unidad] ?? 0) + 1;
        }

        // Calcular promedio
        if ($countValores > 0) {
            $stats['valores_boro']['promedio'] = round($totalValor / $countValores, 3);
            $stats['valores_boro']['total'] = $countValores;
        }

        // Ordenar estadísticas
        arsort($stats['por_estado']);
        arsort($stats['por_analista']);
        arsort($stats['por_metodologia']);
        arsort($stats['por_equipo']);
        arsort($stats['rangos_fechas']);
        arsort($stats['unidades_medida']);

        return $stats;
    }

    /**
     * Prepara los datos específicos para análisis de intercambio catiónico
     */
    protected function prepareCationicData($analysis)
    {
        $controles_analiticos = [];
        
        // Procesar controles analíticos desde campos individuales de la tabla analytical_controls
        if (isset($analysis->analyticalControl) && $analysis->analyticalControl) {
            $control = $analysis->analyticalControl;
            
            // Agregar datos de blanco si existen
            if ($control->blanco_identificacion) {
                $controles_analiticos[] = [
                    'tipo' => 'blanco',
                    'datos_completos' => [
                        'identificacion' => $control->blanco_identificacion,
                        'lcm' => $control->blanco_lcm,
                        'valor_leido' => $control->blanco_valor_leido,
                        'aceptable' => $control->blanco_aceptable,
                        'observaciones' => $control->blanco_observaciones
                    ]
                ];
            }
            
            // Agregar datos de error si existen
            if ($control->error_identificacion) {
                $controles_analiticos[] = [
                    'tipo' => 'error',
                    'datos_completos' => [
                        'identificacion' => $control->error_identificacion,
                        'valor_teorico' => $control->error_valor_teorico,
                        'valor_leido' => $control->error_valor_leido,
                        'porcentaje' => $control->error_porcentaje,
                        'aceptable' => $control->error_aceptable,
                        'observaciones' => $control->error_observaciones
                    ]
                ];
            }
            
            // Agregar datos de recuperación si existen
            if ($control->recuperacion_identificacion) {
                $controles_analiticos[] = [
                    'tipo' => 'recuperacion',
                    'datos_completos' => [
                        'identificacion' => $control->recuperacion_identificacion,
                        'valor_teorico' => $control->recuperacion_valor_teorico,
                        'valor_leido' => $control->recuperacion_valor_leido,
                        'porcentaje' => $control->recuperacion_porcentaje,
                        'aceptable' => $control->recuperacion_aceptable,
                        'observaciones' => $control->recuperacion_observaciones
                    ]
                ];
            }
            
            // Agregar datos de duplicados si existen
            if ($control->dpr_identificacion) {
                $controles_analiticos[] = [
                    'tipo' => 'duplicados',
                    'datos_completos' => [
                        'identificacion' => $control->dpr_identificacion,
                        'replica1' => $control->dpr_replica1,
                        'replica2' => $control->dpr_replica2,
                        'porcentaje' => $control->dpr_porcentaje,
                        'aceptable' => $control->dpr_aceptable,
                        'observaciones' => $control->dpr_observaciones
                    ]
                ];
            }
        }
        
        return [
            'controles_analiticos' => $controles_analiticos,
            'effectiveConsecutivo' => $analysis->consecutivo_no ?? 'N/A'
        ];
    }

    /**
     * Prepara los datos específicos para análisis de azufre
     */
    protected function prepareSulfurData($analysis)
    {
        $controles_analiticos = [];
        
        // Procesar controles analíticos desde la tabla analytical_controls
        if (isset($analysis->analyticalControl) && $analysis->analyticalControl) {
            $control = $analysis->analyticalControl;
            
            // Si hay controles analíticos en formato JSON
            if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                foreach ($control->controles_analiticos as $controlData) {
                    $controles_analiticos[] = [
                        'tipo' => 'control_analitico',
                        'datos_completos' => $controlData
                    ];
                }
            }
        }
        
        return [
            'controles_analiticos' => $controles_analiticos,
            'effectiveConsecutivo' => $analysis->consecutive_no ?? 'N/A'
        ];
    }
}
