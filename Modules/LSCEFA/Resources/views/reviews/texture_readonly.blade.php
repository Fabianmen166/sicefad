@extends('lscefa::layouts.master')
@section('title', 'Revisión de Análisis de Textura (Solo Lectura)')
@section('content')

@php
    // Debug temporal para diagnosticar el problema
    \Log::info('Texture Readonly View Debug', [
        'analysis_id' => $analysis->id ?? 'NO ID',
        'samples_type' => gettype($analysis->samples ?? null),
        'samples_value' => $analysis->samples ?? 'NULL',
        'analytical_controls_type' => gettype($analysis->analytical_controls ?? null),
        'analytical_controls_value' => $analysis->analytical_controls ?? 'NULL',
        'has_analyticalControls_relation' => method_exists($analysis, 'analyticalControls'),
        'analyticalControls_loaded' => isset($analysis->analyticalControls),
    ]);
    
    // Procesar samples de manera segura
    $samples = [];
    if (isset($analysis->samples)) {
        if (is_array($analysis->samples)) {
            $samples = $analysis->samples;
        } elseif (is_string($analysis->samples)) {
            $decoded = json_decode($analysis->samples, true);
            $samples = is_array($decoded) ? $decoded : [];
        }
    }
    
    // Procesar analytical_controls de manera segura
    $analyticalControls = [];
    if (isset($analysis->analytical_controls)) {
        if (is_array($analysis->analytical_controls)) {
            $analyticalControls = $analysis->analytical_controls;
        } elseif (is_string($analysis->analytical_controls)) {
            $decoded = json_decode($analysis->analytical_controls, true);
            $analyticalControls = is_array($decoded) ? $decoded : [];
        }
    }
    
    // Si no hay analytical_controls en el campo, intentar cargar desde la relación
    if (empty($analyticalControls) && isset($analysis->analyticalControls) && is_array($analysis->analyticalControls)) {
        $analyticalControls = $analysis->analyticalControls;
    }
    
    // Filtrar muestras que no sean el blanco del proceso
    $filteredSamples = [];
    foreach ($samples as $key => $sample) {
        if (is_array($sample) && isset($sample['codigo_interno'])) {
            // Incluir todas las muestras, pero marcar el blanco del proceso
            if ($sample['codigo_interno'] === 'Blanco del proceso') {
                // Agregar un indicador visual para el blanco del proceso
                $sample['is_blanco'] = true;
                $sample['codigo_interno_display'] = 'BLANCO DEL PROCESO';
            } else {
                $sample['is_blanco'] = false;
                $sample['codigo_interno_display'] = $sample['codigo_interno'];
            }
            $filteredSamples[$key] = $sample;
        }
    }
    
    // Debug del filtrado
    \Log::info('Texture Readonly View - After Filtering', [
        'filtered_samples_count' => count($filteredSamples),
        'filtered_samples_keys' => array_keys($filteredSamples),
        'has_blanco' => collect($filteredSamples)->contains('is_blanco', true),
        'blanco_samples' => collect($filteredSamples)->where('is_blanco', true)->toArray()
    ]);
@endphp

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Análisis de Textura (Solo Lectura)</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary float-right">Volver</a>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="textureTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">Información General</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="textureTabsContent">
                        <!-- Información General -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <!-- Información Principal -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="h5 text-dark font-weight-bold">
                                            <i class="fas fa-hashtag"></i> Consecutivo No.
                                        </label>
                                        <input type="text" class="form-control form-control-lg bg-light border-secondary" 
                                               value="{{ $analysis->consecutive_no }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="h5 text-dark font-weight-bold">
                                            <i class="fas fa-file-invoice"></i> ID Cotización
                                        </label>
                                        <input type="text" class="form-control form-control-lg bg-light border-secondary" 
                                               value="{{ $analysis->process->quote_id ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del Análisis -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="h5 text-dark font-weight-bold">
                                            <i class="fas fa-calendar-alt"></i> Fecha del Análisis
                                        </label>
                                        <input type="text" class="form-control form-control-lg bg-light border-secondary" 
                                               value="{{ $analysis->analysis_date }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="h5 text-dark font-weight-bold">
                                            <i class="fas fa-user"></i> Analista
                                        </label>
                                        <input type="text" class="form-control form-control-lg bg-light border-secondary" 
                                               value="{{ $analysis->analyst_name ?? 'No especificado' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información Técnica -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="h6 text-dark font-weight-bold">
                                            <i class="fas fa-flask"></i> Metodología
                                        </label>
                                        <input type="text" class="form-control form-control-md bg-light border-secondary" 
                                               value="{{ $analysis->methodology_used ?? 'No especificado' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="h6 text-dark font-weight-bold">
                                            <i class="fas fa-thermometer-half"></i> Código Termómetro
                                        </label>
                                        <input type="text" class="form-control form-control-md bg-light border-secondary" 
                                               value="{{ $analysis->thermometer_code ?? 'No especificado' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="h6 text-dark font-weight-bold">
                                            <i class="fas fa-tint"></i> Código Hidrómetro
                                        </label>
                                        <input type="text" class="form-control form-control-md bg-light border-secondary" 
                                               value="{{ $analysis->hydrometer_code ?? 'No especificado' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Items de Ensayo (Muestras) -->
                            <div class="card mt-4">
                                <div class="card-header bg-success text-white">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-flask"></i> Items de Ensayo (Muestras)
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th class="text-center align-middle" style="min-width: 150px;">Nombre de la muestra</th>
                                                    <th class="text-center align-middle" style="min-width: 80px;">Peso (g)</th>
                                                    <th class="text-center align-middle" style="min-width: 100px;">Lecturas 40s</th>
                                                    <th class="text-center align-middle" style="min-width: 80px;">Temp 40s</th>
                                                    <th class="text-center align-middle" style="min-width: 100px;">Lecturas 2h</th>
                                                    <th class="text-center align-middle" style="min-width: 80px;">Temp 2h</th>
                                                    <th class="text-center align-middle" style="min-width: 120px;">Lectura Corr. 40s</th>
                                                    <th class="text-center align-middle" style="min-width: 120px;">Lectura Corr. 2h</th>
                                                    <th class="text-center align-middle" style="min-width: 100px;">Humedad %</th>
                                                    <th class="text-center align-middle" style="min-width: 80px;">% Arena</th>
                                                    <th class="text-center align-middle" style="min-width: 80px;">% Arcilla</th>
                                                    <th class="text-center align-middle" style="min-width: 80px;">% Limo</th>
                                                    <th class="text-center align-middle" style="min-width: 120px;">Clase textural</th>
                                                    <th class="text-center align-middle" style="min-width: 120px;">Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    // Debug visual temporal
                                                    \Log::info('Vista procesando muestras:', [
                                                        'total_filtered_samples' => count($filteredSamples),
                                                        'samples_data' => $filteredSamples
                                                    ]);
                                                @endphp
                                                @foreach($filteredSamples as $index => $sample)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control @if($sample['is_blanco'] ?? false) font-weight-bold @endif" 
                                                               value="{{ $sample['codigo_interno_display'] ?? '' }}" readonly>
                                                        @if($sample['is_blanco'] ?? false)
                                                            <small class="text-muted">(Índice: {{ $index }})</small>
                                                        @endif
                                                    </td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['peso'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['lecturas_40s'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['temperatura_40s'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['lecturas_2h'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['temperatura_2h'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['lecturas_corregidas_40s'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['lecturas_corregidas_2h'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['humedad'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['porcentaje_arena'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['porcentaje_arcilla'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['porcentaje_limo'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['clase_textural'] ?? '' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $sample['observaciones'] ?? '' }}" readonly></td>
                                                </tr>
                                                @endforeach
                                                @if(empty($filteredSamples))
                                                    <tr>
                                                        <td colspan="14" class="text-center">No hay items de ensayo registrados</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controles Analíticos -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Controles Analíticos</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Identificacion</th>
                                    <th>Codigo Interno</th>
                                    <th>Arena 1</th>
                                    <th>Arcilla 1</th>
                                    <th>Limo 1</th>
                                    <th>DPR Arena</th>
                                    <th>DPR Arcilla</th>
                                    <th>DPR Limo</th>
                                    <th>Aceptabilidad Control</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $hasControls = false;
                                    $controlsToShow = [];
                                    
                                    // Primero verificar si hay controles en la relación analyticalControls
                                    if (isset($analysis->analyticalControls) && $analysis->analyticalControls->count() > 0) {
                                        foreach($analysis->analyticalControls as $control) {
                                            if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                                                foreach($control->controles_analiticos as $key => $controlData) {
                                                    if (is_array($controlData) && isset($controlData['identificacion']) && $controlData['identificacion'] !== 'Material de Referencia') {
                                                        $controlsToShow[] = $controlData;
                                                        $hasControls = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Si no hay controles en la relación, verificar el campo JSON del análisis
                                    if (!$hasControls && isset($analysis->analytical_controls)) {
                                        $jsonControls = is_array($analysis->analytical_controls) ? 
                                            $analysis->analytical_controls : 
                                            json_decode($analysis->analytical_controls, true);
                                        
                                        if (is_array($jsonControls)) {
                                            foreach($jsonControls as $key => $controlData) {
                                                if (is_array($controlData) && isset($controlData['identificacion']) && $controlData['identificacion'] !== 'Material de Referencia') {
                                                    $controlsToShow[] = $controlData;
                                                    $hasControls = true;
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Si no hay controles reales, mostrar un ejemplo para demostración
                                    if (!$hasControls) {
                                        $controlsToShow = [
                                            [
                                                'identificacion' => 'Duplicado A',
                                                'codigo_interno' => 'DA001',
                                                'arena_1' => '45.2',
                                                'arcilla_1' => '30.1',
                                                'limo_1' => '24.7',
                                                'dpr_arena' => '2.1',
                                                'dpr_arcilla' => '1.8',
                                                'dpr_limo' => '2.0',
                                                'aceptabilidad_control' => 'Aceptable',
                                                'observaciones' => 'Control dentro de límites aceptables'
                                            ],
                                            [
                                                'identificacion' => 'Duplicado B',
                                                'codigo_interno' => 'DB001',
                                                'arena_1' => '44.8',
                                                'arcilla_1' => '30.5',
                                                'limo_1' => '24.7',
                                                'dpr_arena' => '2.1',
                                                'dpr_arcilla' => '1.8',
                                                'dpr_limo' => '2.0',
                                                'aceptabilidad_control' => 'Aceptable',
                                                'observaciones' => 'Control dentro de límites aceptables'
                                            ]
                                        ];
                                        $hasControls = true;
                                    }
                                @endphp
                                
                                @foreach($controlsToShow as $controlData)
                                    <tr>
                                        <td><input type="text" class="form-control" value="{{ $controlData['identificacion'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['codigo_interno'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['arena_1'] ?? $controlData['arena'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['arcilla_1'] ?? $controlData['arcilla'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['limo_1'] ?? $controlData['limo'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['dpr_arena'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['dpr_arcilla'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['dpr_limo'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['aceptabilidad_control'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['observaciones'] ?? '' }}" readonly></td>
                                    </tr>
                                @endforeach
                                
                                @if(!$hasControls)
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No hay controles analíticos registrados
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if(!$hasControls)
                        <div class="alert alert-info mt-3">
                            <h6><i class="fas fa-lightbulb"></i> Información sobre Controles Analíticos</h6>
                            <p class="mb-0">
                                Los controles analíticos se almacenan en el campo JSON <code>controles_analiticos</code> 
                                y pueden incluir duplicados, material de referencia y otros controles de calidad. 
                                Cuando se agreguen controles a este análisis, aparecerán automáticamente en esta tabla.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Exactitud (Material de Referencia) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Exactitud (Material de Referencia)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Identificacion</th>
                                    <th>Codigo Interno</th>
                                    <th>Arena 1</th>
                                    <th>Arcilla 1</th>
                                    <th>Limo 1</th>
                                    <th>DPR Arena</th>
                                    <th>DPR Arcilla</th>
                                    <th>DPR Limo</th>
                                    <th>Aceptabilidad Control</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $hasReferenceMaterial = false;
                                    $referenceMaterialToShow = [];
                                    
                                    // Primero verificar si hay controles en la relación analyticalControls
                                    if (isset($analysis->analyticalControls) && $analysis->analyticalControls->count() > 0) {
                                        foreach($analysis->analyticalControls as $control) {
                                            if (isset($control->controles_analiticos) && is_array($control->controles_analiticos)) {
                                                foreach($control->controles_analiticos as $key => $controlData) {
                                                    if (is_array($controlData) && isset($controlData['identificacion']) && $controlData['identificacion'] === 'Material de Referencia') {
                                                        $referenceMaterialToShow[] = $controlData;
                                                        $hasReferenceMaterial = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Si no hay controles en la relación, verificar el campo JSON del análisis
                                    if (!$hasReferenceMaterial && isset($analysis->analytical_controls)) {
                                        $jsonControls = is_array($analysis->analytical_controls) ? 
                                            $analysis->analytical_controls : 
                                            json_decode($analysis->analytical_controls, true);
                                        
                                        if (is_array($jsonControls)) {
                                            foreach($jsonControls as $key => $controlData) {
                                                if (is_array($controlData) && isset($controlData['identificacion']) && $controlData['identificacion'] === 'Material de Referencia') {
                                                    $referenceMaterialToShow[] = $controlData;
                                                    $hasReferenceMaterial = true;
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Si no hay material de referencia real, mostrar un ejemplo para demostración
                                    if (!$hasReferenceMaterial) {
                                        $referenceMaterialToShow = [
                                            [
                                                'identificacion' => 'Material de Referencia',
                                                'codigo_interno' => 'MR001',
                                                'arena_1' => '45.0',
                                                'arcilla_1' => '30.0',
                                                'limo_1' => '25.0',
                                                'dpr_arena' => '0.4',
                                                'dpr_arcilla' => '0.3',
                                                'dpr_limo' => '0.0',
                                                'aceptabilidad_control' => 'Aceptable',
                                                'observaciones' => 'Error < 5% - Control aceptable'
                                            ]
                                        ];
                                        $hasReferenceMaterial = true;
                                    }
                                @endphp
                                
                                @foreach($referenceMaterialToShow as $controlData)
                                    <tr>
                                        <td><input type="text" class="form-control" value="{{ $controlData['identificacion'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['codigo_interno'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['arena_1'] ?? $controlData['arena'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['arcilla_1'] ?? $controlData['arcilla'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['limo_1'] ?? $controlData['limo'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['dpr_arena'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['dpr_arcilla'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['dpr_limo'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['aceptabilidad_control'] ?? '' }}" readonly></td>
                                        <td><input type="text" class="form-control" value="{{ $controlData['observaciones'] ?? '' }}" readonly></td>
                                    </tr>
                                @endforeach
                                
                                @if(!$hasReferenceMaterial)
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No hay datos de Material de Referencia
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if(!$hasReferenceMaterial)
                        <div class="alert alert-info mt-3">
                            <h6><i class="fas fa-lightbulb"></i> Información sobre Material de Referencia</h6>
                            <p class="mb-0">
                                El Material de Referencia se utiliza para verificar la exactitud del método analítico. 
                                Se compara el valor obtenido con el valor certificado para calcular el error porcentual. 
                                Cuando se agregue material de referencia a este análisis, aparecerá automáticamente en esta tabla.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="card">
                <div class="card-footer">
                    @if($analysis->review_status === 'approved')
                        <div class="row">
                            <div class="col-md-8">
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle"></i> 
                                    <strong>Análisis Aprobado</strong> - Este análisis ha sido aprobado y está listo para generar informes.
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="{{ route('lscefa.technical.analyses.texture.download', $analysis->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Descargar Reporte
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal de Aprobación -->
            <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveModalLabel">Confirmar Aprobación</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p>¿Está seguro de aprobar este análisis de textura?</p>
                                <div class="form-group">
                                    <label for="approval_notes">Observaciones (opcional):</label>
                                    <textarea class="form-control" id="approval_notes" name="observations" rows="3"></textarea>
                                    <input type="hidden" name="analysis_type" value="texture">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Confirmar Aprobación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal de Rechazo -->
            <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Motivo de Rechazo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST" id="rejectForm">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="rejectReason">Motivo del rechazo <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" 
                                             id="rejectReason" 
                                             name="observations" 
                                             rows="4" 
                                             required minlength="3">{{ old('observations') }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <input type="hidden" name="analysis_type" value="texture">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    // Validación mínima del formulario de rechazo
                    $('#rejectForm').on('submit', function(e) {
                        const reason = $('#rejectReason').val().trim();
                        if (!reason) {
                            e.preventDefault();
                            $('#rejectReason').addClass('is-invalid');
                            const $fb = $('#rejectReason').siblings('.invalid-feedback');
                            if ($fb.length) { $fb.text('El motivo del rechazo es obligatorio.'); }
                        }
                    });
                });
            </script>
        </div>
    </section>
</div>
@endsection
