@extends('lscefa::layouts.technical')

@section('title', 'Procesamiento por Lotes - Análisis de Textura')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ isset($analysis) ? 'Editar Análisis Rechazado - Textura' : 'Procesamiento por Lotes - Análisis de Textura' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.texture.index') }}">Gestión de Textura</a></li>
                        <li class="breadcrumb-item active">Procesamiento por Lotes</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-12">
                    <a href="{{ route('lscefa.technical.analyses.texture.index') }}" class="btn btn-secondary">Regresar</a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($analysis) ? route('lscefa.technical.analyses.texture.update_rejected', $analysis->id) : route('lscefa.technical.analyses.texture.batch_store') }}" method="POST" id="textureBatchForm">
                @csrf
                @if(isset($analysis))
                    <input type="hidden" name="_method" value="POST">
                @endif
                <!-- Barra de Navegación Horizontal (SOLO UNA VEZ) -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-0">
                                <ul class="nav nav-tabs nav-fill" id="analysisTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                                            <i class="fas fa-info-circle mr-2"></i>Información General
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="controls-tab" data-toggle="tab" href="#controls" role="tab" aria-controls="controls" aria-selected="false">
                                            <i class="fas fa-cogs mr-2"></i>Controles Analíticos
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="samples" aria-selected="false">
                                            <i class="fas fa-flask mr-2"></i>Análisis de Muestras
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin barra de navegación -->
                <!-- Tarjeta Única de Información General del Análisis -->
                <div class="row" id="infoGeneralCardRow">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Información General del Análisis - Cotización
                                    @foreach($processes as $process)
                                        {{ $loop->first ? '' : ', ' }}{{ $process->quote_id }}
                                    @endforeach
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Campos generales -->
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="consecutivo_no">Consecutivo No.</label>
                                            <input type="text" class="form-control" id="consecutivo_no" name="consecutivo_no" value="{{ isset($analysis) ? $analysis->consecutive_no : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="fecha_analisis">Fecha del análisis</label>
                                            <input type="date" class="form-control" id="fecha_analisis" name="fecha_analisis" value="{{ isset($analysis) ? $analysis->analysis_date : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="nombre_analista">Nombre del Analista</label>
                                            <input type="text" class="form-control" id="nombre_analista" name="nombre_analista" value="{{ isset($analysis) ? $analysis->analyst_name : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="metodologia_utilizada">Metodología Utilizada</label>
                                            <input type="text" class="form-control" id="metodologia_utilizada" name="metodologia_utilizada" value="{{ isset($analysis) ? $analysis->methodology_used : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="codigo_termometro">Código Termómetro</label>
                                            <input type="text" class="form-control" id="codigo_termometro" name="codigo_termometro" value="{{ isset($analysis) ? $analysis->thermometer_code : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="codigo_hidrometro">Código Hidrómetro</label>
                                            <input type="text" class="form-control" id="codigo_hidrometro" name="codigo_hidrometro" value="{{ isset($analysis) ? $analysis->hydrometer_code : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin tarjeta unificada -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    function toggleInfoGeneralCard() {
                        var activeTab = document.querySelector('.nav-tabs .nav-link.active');
                        var infoGeneralCardRow = document.getElementById('infoGeneralCardRow');
                        if (activeTab && infoGeneralCardRow) {
                            if (activeTab.getAttribute('href') === '#general') {
                                infoGeneralCardRow.style.display = '';
                            } else {
                                infoGeneralCardRow.style.display = 'none';
                            }
                        }
                    }
                    // Inicial
                    toggleInfoGeneralCard();
                    // Al cambiar de pestaña
                    document.querySelectorAll('.nav-tabs .nav-link').forEach(function(tab) {
                        tab.addEventListener('click', function() {
                            setTimeout(toggleInfoGeneralCard, 10);
                        });
                    });
                });
                </script>
                @foreach($processes as $index => $process)
                    @php
                        $textureService = $process->serviceProcessDetails->filter(function($detail) {
                            return str_contains(strtolower($detail->service->descripcion), 'textura') ||
                                   str_contains(strtolower($detail->service->descripcion), 'texture');
                        })->first();
                    @endphp
                    @if($textureService)
                        <input type="hidden" name="analyses[{{ $index }}][process_id]" value="{{ $process->process_id }}">
                        <input type="hidden" name="analyses[{{ $index }}][service_id]" value="{{ $textureService->service_id }}">
                        
                        <!-- Contenido de las Pestañas -->
                        <div class="tab-content" id="analysisTabsContent">
                            <!-- Pestaña Información General -->
                            <!-- TARJETA ELIMINADA: Información General del Análisis por proceso -->
                            <!-- Pestaña Controles Analíticos -->
                            <div class="tab-pane fade" id="controls" role="tabpanel" aria-labelledby="controls-tab">
                                <!-- Controles Analíticos -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Controles Analíticos - Cotización {{ $process->quote_id }}</h3>
                                            </div>
                                            <div class="card-body">
                                                
                                                <!-- Precisión Analítica -->
                                                <div class="row mb-4">
                                                    <div class="col-12">
                                                        <h5 class="text-primary">Precisión Analítica</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Precisión Analítica</th>
                                                                        <th>Código interno</th>
                                                                        <th colspan="3">Promedios</th>
                                                                        <th colspan="3">DPR</th>
                                                                        <th>Aceptabilidad del control</th>
                                                                        <th>Observaciones</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th></th>
                                                                        <th></th>
                                                                        <th>% Arena</th>
                                                                        <th>% Arcilla</th>
                                                                        <th>% Limo</th>
                                                                        <th>% Arena</th>
                                                                        <th>% Arcilla</th>
                                                                        <th>% Limo</th>
                                                                        <th></th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Duplicado A</td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_codigo]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_promedio_arena]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_promedio_arcilla]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_promedio_limo]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm dpr-arena" name="analyses[{{ $index }}][duplicado_a_dpr_arena]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm dpr-arcilla" name="analyses[{{ $index }}][duplicado_a_dpr_arcilla]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm dpr-limo" name="analyses[{{ $index }}][duplicado_a_dpr_limo]" readonly></td>
                                                                        <td>
                                                                            <select class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_aceptabilidad]">
                                                                                <option value="">Seleccionar</option>
                                                                                <option value="Aceptable">Aceptable</option>
                                                                                <option value="No aceptable">No aceptable</option>
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_observaciones]"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Duplicado B</td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_codigo]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_promedio_arena]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_promedio_arcilla]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_promedio_limo]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm dpr-arena" name="analyses[{{ $index }}][duplicado_b_dpr_arena]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm dpr-arcilla" name="analyses[{{ $index }}][duplicado_b_dpr_arcilla]" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm dpr-limo" name="analyses[{{ $index }}][duplicado_b_dpr_limo]" readonly></td>
                                                                        <td>
                                                                            <select class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_aceptabilidad]">
                                                                                <option value="">Seleccionar</option>
                                                                                <option value="Aceptable">Aceptable</option>
                                                                                <option value="No aceptable">No aceptable</option>
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_observaciones]"></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Exactitud -->
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h5 class="text-primary">Exactitud</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Exactitud</th>
                                                                        <th>Identificación (Lote)</th>
                                                                        <th colspan="3">Valor obtenido (%)</th>
                                                                        <th colspan="3">Valor esperado (%)</th>
                                                                        <th colspan="3">%Error</th>
                                                                        <th>Aceptabilidad</th>
                                                                        <th>Observaciones</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th></th>
                                                                        <th></th>
                                                                        <th>% Arena</th>
                                                                        <th>% Arcilla</th>
                                                                        <th>% Limo</th>
                                                                        <th>% Arena</th>
                                                                        <th>% Arcilla</th>
                                                                        <th>% Limo</th>
                                                                        <th>% Arena</th>
                                                                        <th>% Arcilla</th>
                                                                        <th>% Limo</th>
                                                                        <th></th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Material de referencia</td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_lote]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_obtenido_arena]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_obtenido_arcilla]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_obtenido_limo]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_esperado_arena]" value="46"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_esperado_arcilla]" value="31"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_esperado_limo]" value="23"></td>
                                                                        <td colspan="3">
                                                                            <input type="text" class="form-control form-control-sm" id="material_referencia_error_promedio_{{ $index }}" name="analyses[{{ $index }}][material_referencia_error_promedio]" readonly>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" class="form-control form-control-sm" id="material_referencia_aceptabilidad_{{ $index }}" name="analyses[{{ $index }}][material_referencia_aceptabilidad]" readonly>
                                                                        </td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_observaciones]"></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Análisis de Muestras -->
                            <div class="tab-pane fade" id="samples" role="tabpanel" aria-labelledby="samples-tab">
                                <!-- Análisis de Muestras -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Análisis de Muestras</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive" style="overflow-x: auto;">
                                                    <table class="table table-bordered table-sm" id="muestras_table_{{ $index }}" style="min-width: 1800px;">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th rowspan="2">ID Cotización</th>
                                                                <th rowspan="2">Nombre de la muestra</th>
                                                                <th rowspan="2">Peso (g)</th>
                                                                <th colspan="6">Reporte de Resultados Análisis</th>
                                                                <th rowspan="2">Humedad %</th>
                                                                <th colspan="5">Resultados % de Textura</th>
                                                                <th rowspan="2">Observaciones</th>
                                                                <th rowspan="2">Acciones</th>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="2">Lecturas a 40 s</th>
                                                                <th colspan="2">Lecturas a 2 h</th>
                                                                <th colspan="2">Lecturas corregidas</th>
                                                                <th>Arena % (g/100g)</th>
                                                                <th>Arcilla % (g/100g)</th>
                                                                <th>Limo % (g/100g)</th>
                                                                <th>Clase textural</th>
                                                            </tr>
                                                            <tr>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th>Lecturas</th>
                                                                <th>°C</th>
                                                                <th>Lectura</th>
                                                                <th>°C</th>
                                                                <th>40 s</th>
                                                                <th>2 h</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="muestras_container_{{ $index }}">
                                                            <!-- Blanco del proceso -->
                                                            <tr class="muestra-row">
                                                                <td></td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][codigo_interno]" value="Blanco del proceso" readonly>
                                                                </td>
                                                                <td><input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[{{ $index }}][items][0][peso]" value="{{ isset($blancoData) ? ($blancoData['peso'] ?? '') : '' }}" placeholder="0.0000"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-40s" name="analyses[{{ $index }}][items][0][lecturas_40s]" value="{{ isset($blancoData) ? ($blancoData['lecturas_40s'] ?? '') : '' }}" placeholder="0.00"></td>
                                                                <td><input type="number" step="0.1" class="form-control form-control-sm temp-40s" name="analyses[{{ $index }}][items][0][temperatura_40s]" value="{{ isset($blancoData) ? ($blancoData['temperatura_40s'] ?? '') : '' }}" placeholder="0.0"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-2h" name="analyses[{{ $index }}][items][0][lecturas_2h]" value="{{ isset($blancoData) ? ($blancoData['lecturas_2h'] ?? '') : '' }}" placeholder="0.00"></td>
                                                                <td><input type="number" step="0.1" class="form-control form-control-sm temp-2h" name="analyses[{{ $index }}][items][0][temperatura_2h]" value="{{ isset($blancoData) ? ($blancoData['temperatura_2h'] ?? '') : '' }}" placeholder="0.0"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[{{ $index }}][items][0][lecturas_corregidas_40s]" value="{{ isset($blancoData) ? ($blancoData['lecturas_corregidas_40s'] ?? '') : '' }}" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[{{ $index }}][items][0][lecturas_corregidas_2h]" value="{{ isset($blancoData) ? ($blancoData['lecturas_corregidas_2h'] ?? '') : '' }}" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm humedad" name="analyses[{{ $index }}][items][0][humedad]" value="{{ isset($blancoData) ? ($blancoData['humedad'] ?? '') : '' }}" placeholder="0.00"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[{{ $index }}][items][0][porcentaje_arena]" value="{{ isset($blancoData) ? ($blancoData['porcentaje_arena'] ?? '') : '' }}" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[{{ $index }}][items][0][porcentaje_arcilla]" value="{{ isset($blancoData) ? ($blancoData['porcentaje_arcilla'] ?? '') : '' }}" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[{{ $index }}][items][0][porcentaje_limo]" value="{{ isset($blancoData) ? ($blancoData['porcentaje_limo'] ?? '') : '' }}" placeholder="0.00" readonly></td>
                                                                <td>
                                                                    <select class="form-control form-control-sm clase-textural" name="analyses[{{ $index }}][items][0][clase_textural]">
                                                                        <option value="">Seleccionar</option>
                                                                        <option value="Arena" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Arena') ? 'selected' : '' }}>Arena</option>
                                                                        <option value="Arena Limosa" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Arena Limosa') ? 'selected' : '' }}>Arena Limosa</option>
                                                                        <option value="Arena Arcillosa" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Arena Arcillosa') ? 'selected' : '' }}>Arena Arcillosa</option>
                                                                        <option value="Limo" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Limo') ? 'selected' : '' }}>Limo</option>
                                                                        <option value="Limo Arenoso" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Limo Arenoso') ? 'selected' : '' }}>Limo Arenoso</option>
                                                                        <option value="Limo Arcilloso" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Limo Arcilloso') ? 'selected' : '' }}>Limo Arcilloso</option>
                                                                        <option value="Arcilla" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Arcilla') ? 'selected' : '' }}>Arcilla</option>
                                                                        <option value="Arcilla Arenosa" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Arcilla Arenosa') ? 'selected' : '' }}>Arcilla Arenosa</option>
                                                                        <option value="Arcilla Limosa" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Arcilla Limosa') ? 'selected' : '' }}>Arcilla Limosa</option>
                                                                        <option value="Franco Arenoso" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Franco Arenoso') ? 'selected' : '' }}>Franco Arenoso</option>
                                                                        <option value="Franco Limoso" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Franco Limoso') ? 'selected' : '' }}>Franco Limoso</option>
                                                                        <option value="Franco Arcilloso" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Franco Arcilloso') ? 'selected' : '' }}>Franco Arcilloso</option>
                                                                        <option value="Franco" {{ (isset($blancoData) && ($blancoData['clase_textural'] ?? '') === 'Franco') ? 'selected' : '' }}>Franco</option>
                                                                    </select>
                                                                </td>
                                                                <td><textarea class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][observaciones]" rows="2" placeholder="Observaciones">{{ isset($blancoData) ? ($blancoData['observaciones'] ?? '') : '' }}</textarea></td>
                                                                <td></td>
                                                            </tr>
                                                            
                                                            @if(isset($analysis) && $analysis->samples)
                                                                @php
                                                                    $samples = is_string($analysis->samples) ? json_decode($analysis->samples, true) : $analysis->samples;
                                                                    $samples = is_array($samples) ? $samples : [];
                                                                @endphp
                                                                @foreach($samples as $sampleIndex => $sample)
                                                                    @if($sampleIndex > 0) {{-- Saltar el primer elemento (blanco) --}}
                                                                    <tr class="muestra-row">
                                                                        <td>{{ $process->quote_id ?? '' }}</td>
                                                                        <td>
                                                                            <input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][codigo_interno]" value="{{ $sample['codigo_interno'] ?? 'Muestra ' . $sampleIndex }}">
                                                                        </td>
                                                                        <td><input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][peso]" value="{{ $sample['peso'] ?? '' }}" placeholder="0.0000"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm lectura-40s" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][lecturas_40s]" value="{{ $sample['lecturas_40s'] ?? '' }}" placeholder="0.00"></td>
                                                                        <td><input type="number" step="0.1" class="form-control form-control-sm temp-40s" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][temperatura_40s]" value="{{ $sample['temperatura_40s'] ?? '' }}" placeholder="0.0"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm lectura-2h" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][lecturas_2h]" value="{{ $sample['lecturas_2h'] ?? '' }}" placeholder="0.00"></td>
                                                                        <td><input type="number" step="0.1" class="form-control form-control-sm temp-2h" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][temperatura_2h]" value="{{ $sample['temperatura_2h'] ?? '' }}" placeholder="0.0"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][lecturas_corregidas_40s]" value="{{ $sample['lecturas_corregidas_40s'] ?? '' }}" placeholder="0.00" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][lecturas_corregidas_2h]" value="{{ $sample['lecturas_corregidas_2h'] ?? '' }}" placeholder="0.00" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm humedad" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][humedad]" value="{{ $sample['humedad'] ?? '' }}" placeholder="0.00"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][porcentaje_arena]" value="{{ $sample['porcentaje_arena'] ?? '' }}" placeholder="0.00" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][porcentaje_arcilla]" value="{{ $sample['porcentaje_arcilla'] ?? '' }}" placeholder="0.00" readonly></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][porcentaje_limo]" value="{{ $sample['porcentaje_limo'] ?? '' }}" placeholder="0.00" readonly></td>
                                                                        <td>
                                                                            <select class="form-control form-control-sm clase-textural" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][clase_textural]">
                                                                                <option value="">Seleccionar</option>
                                                                                <option value="Arena" {{ ($sample['clase_textural'] ?? '') === 'Arena' ? 'selected' : '' }}>Arena</option>
                                                                                <option value="Arena Limosa" {{ ($sample['clase_textural'] ?? '') === 'Arena Limosa' ? 'selected' : '' }}>Arena Limosa</option>
                                                                                <option value="Arena Arcillosa" {{ ($sample['clase_textural'] ?? '') === 'Arena Arcillosa' ? 'selected' : '' }}>Arena Arcillosa</option>
                                                                                <option value="Limo" {{ ($sample['clase_textural'] ?? '') === 'Limo' ? 'selected' : '' }}>Limo</option>
                                                                                <option value="Limo Arenoso" {{ ($sample['clase_textural'] ?? '') === 'Limo Arenoso' ? 'selected' : '' }}>Limo Arenoso</option>
                                                                                <option value="Limo Arcilloso" {{ ($sample['clase_textural'] ?? '') === 'Limo Arcilloso' ? 'selected' : '' }}>Limo Arcilloso</option>
                                                                                <option value="Arcilla" {{ ($sample['clase_textural'] ?? '') === 'Arcilla' ? 'selected' : '' }}>Arcilla</option>
                                                                                <option value="Arcilla Arenosa" {{ ($sample['clase_textural'] ?? '') === 'Arcilla Arenosa' ? 'selected' : '' }}>Arcilla Arenosa</option>
                                                                                <option value="Arcilla Limosa" {{ ($sample['clase_textural'] ?? '') === 'Arcilla Limosa' ? 'selected' : '' }}>Arcilla Limosa</option>
                                                                                <option value="Franco Arenoso" {{ ($sample['clase_textural'] ?? '') === 'Franco Arenoso' ? 'selected' : '' }}>Franco Arenoso</option>
                                                                                <option value="Franco Limoso" {{ ($sample['clase_textural'] ?? '') === 'Franco Limoso' ? 'selected' : '' }}>Franco Limoso</option>
                                                                                <option value="Franco Arcilloso" {{ ($sample['clase_textural'] ?? '') === 'Franco Arcilloso' ? 'selected' : '' }}>Franco Arcilloso</option>
                                                                                <option value="Franco" {{ ($sample['clase_textural'] ?? '') === 'Franco' ? 'selected' : '' }}>Franco</option>
                                                                            </select>
                                                                        </td>
                                                                        <td><textarea class="form-control form-control-sm" name="analyses[{{ $index }}][items][{{ $sampleIndex }}][observaciones]" rows="2" placeholder="Observaciones">{{ $sample['observaciones'] ?? '' }}</textarea></td>
                                                                        <td>
                                                                            <button type="button" class="btn btn-danger btn-sm remove-muestra" title="Eliminar muestra">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <!-- Duplicado A (solo para análisis nuevos) -->
                                                            <tr class="muestra-row">
                                                                <td></td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][items][1][codigo_interno]" value="Duplicado A">
                                                                </td>
                                                                <td><input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[{{ $index }}][items][1][peso]" placeholder="0.0000"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-40s" name="analyses[{{ $index }}][items][1][lecturas_40s]" placeholder="0.00"></td>
                                                                <td><input type="number" step="0.1" class="form-control form-control-sm temp-40s" name="analyses[{{ $index }}][items][1][temperatura_40s]" placeholder="0.0"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-2h" name="analyses[{{ $index }}][items][1][lecturas_2h]" placeholder="0.00"></td>
                                                                <td><input type="number" step="0.1" class="form-control form-control-sm temp-2h" name="analyses[{{ $index }}][items][1][temperatura_2h]" placeholder="0.0"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[{{ $index }}][items][1][lecturas_corregidas_40s]" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[{{ $index }}][items][1][lecturas_corregidas_2h]" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm humedad" name="analyses[{{ $index }}][items][1][humedad]" placeholder="0.00"></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[{{ $index }}][items][1][porcentaje_arena]" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[{{ $index }}][items][1][porcentaje_arcilla]" placeholder="0.00" readonly></td>
                                                                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[{{ $index }}][items][1][porcentaje_limo]" placeholder="0.00" readonly></td>
                                                                <td>
                                                                    <select class="form-control form-control-sm clase-textural" name="analyses[{{ $index }}][items][1][clase_textural]">
                                                                        <option value="">Seleccionar</option>
                                                                        <option value="Arena">Arena</option>
                                                                        <option value="Arena Limosa">Arena Limosa</option>
                                                                        <option value="Arena Arcillosa">Arena Arcillosa</option>
                                                                        <option value="Limo">Limo</option>
                                                                        <option value="Limo Arenoso">Limo Arenoso</option>
                                                                        <option value="Limo Arcilloso">Limo Arcilloso</option>
                                                                        <option value="Arcilla">Arcilla</option>
                                                                        <option value="Arcilla Arenosa">Arcilla Arenosa</option>
                                                                        <option value="Arcilla Limosa">Arcilla Limosa</option>
                                                                        <option value="Franco Arenoso">Franco Arenoso</option>
                                                                        <option value="Franco Limoso">Franco Limoso</option>
                                                                        <option value="Franco Arcilloso">Franco Arcilloso</option>
                                                                        <option value="Franco">Franco</option>
                                                                    </select>
                                                                </td>
                                                                <td><textarea class="form-control form-control-sm" name="analyses[{{ $index }}][items][1][observaciones]" rows="2" placeholder="Observaciones"></textarea></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-danger btn-sm remove-muestra" title="Eliminar muestra">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                                <!-- Duplicado B (solo para análisis nuevos) -->
                                                            @php $quoteIds = $processes->pluck('quote_id')->toArray(); @endphp
                                                            @foreach($processes as $pIndex => $proc)
                                                                @if($pIndex >= 2)
                                                                <tr class="muestra-row">
                                                                    <td>{{ $proc->quote_id }}</td>
                                                                    <td>
                                                                        <input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][items][{{ $pIndex }}][codigo_interno]" value="Duplicado B">
                                                                    </td>
                                                                    <td><input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[{{ $index }}][items][{{ $pIndex }}][peso]" placeholder="0.0000"></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-40s" name="analyses[{{ $index }}][items][{{ $pIndex }}][lecturas_40s]" placeholder="0.00"></td>
                                                                    <td><input type="number" step="0.1" class="form-control form-control-sm temp-40s" name="analyses[{{ $index }}][items][{{ $pIndex }}][temperatura_40s]" placeholder="0.0"></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-2h" name="analyses[{{ $index }}][items][{{ $pIndex }}][lecturas_2h]" placeholder="0.00"></td>
                                                                    <td><input type="number" step="0.1" class="form-control form-control-sm temp-2h" name="analyses[{{ $index }}][items][{{ $pIndex }}][temperatura_2h]" placeholder="0.0"></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[{{ $index }}][items][{{ $pIndex }}][lecturas_corregidas_40s]" placeholder="0.00" readonly></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[{{ $index }}][items][{{ $pIndex }}][lecturas_corregidas_2h]" placeholder="0.00" readonly></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm humedad" name="analyses[{{ $index }}][items][{{ $pIndex }}][humedad]" placeholder="0.00"></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[{{ $index }}][items][{{ $pIndex }}][porcentaje_arena]" placeholder="0.00" readonly></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[{{ $index }}][items][{{ $pIndex }}][porcentaje_arcilla]" placeholder="0.00" readonly></td>
                                                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[{{ $index }}][items][{{ $pIndex }}][porcentaje_limo]" placeholder="0.00" readonly></td>
                                                                    <td>
                                                                        <select class="form-control form-control-sm clase-textural" name="analyses[{{ $index }}][items][{{ $pIndex }}][clase_textural]">
                                                                            <option value="">Seleccionar</option>
                                                                            <option value="Arena">Arena</option>
                                                                            <option value="Arena Limosa">Arena Limosa</option>
                                                                            <option value="Arena Arcillosa">Arena Arcillosa</option>
                                                                            <option value="Limo">Limo</option>
                                                                            <option value="Limo Arenoso">Limo Arenoso</option>
                                                                            <option value="Limo Arcilloso">Limo Arcilloso</option>
                                                                            <option value="Arcilla">Arcilla</option>
                                                                            <option value="Arcilla Arenosa">Arcilla Arenosa</option>
                                                                            <option value="Arcilla Limosa">Arcilla Limosa</option>
                                                                            <option value="Franco Arenoso">Franco Arenoso</option>
                                                                            <option value="Franco Limoso">Franco Limoso</option>
                                                                            <option value="Franco Arcilloso">Franco Arcilloso</option>
                                                                            <option value="Franco">Franco</option>
                                                                        </select>
                                                                    </td>
                                                                    <td><textarea class="form-control form-control-sm" name="analyses[{{ $index }}][items][{{ $pIndex }}][observaciones]" rows="2" placeholder="Observaciones"></textarea></td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-danger btn-sm remove-muestra" title="Eliminar muestra">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                @endif
                                                            @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div style="width: 100%; overflow-x: auto; margin-top: 4px;">
                                                    <div style="height: 8px; background: linear-gradient(90deg, #e0e0e0 0%, #bdbdbd 100%); border-radius: 4px;"></div>
                                                </div>
                                                
                                                <!-- Eliminar la barra de navegación inferior (div con class="row mt-3" y el div con class="card-body p-0" que contiene las tabs) -->
                                                <!-- Mover el botón de "Agregar Fila" justo después de la tabla de muestras, antes de las instrucciones de uso: -->
                                                <div class="row mt-2">
                                                    <div class="col-12 text-right">
                                                        <button type="button" class="btn btn-secondary" id="addMuestraBtn_{{ $index }}" data-process-index="{{ $index }}">
                                                            <i class="fas fa-plus"></i> Agregar Fila
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Contenido de la Navegación de la Tabla -->
                                                <div class="tab-content mt-3" id="tableNavigationContent_{{ $index }}">
                                                    <!-- Pestaña Datos de Muestras -->
                                                    <div class="tab-pane fade show active" id="data-content-{{ $index }}" role="tabpanel" aria-labelledby="data-tab-{{ $index }}">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label><i class="fas fa-info-circle text-info"></i> Total de Muestras:</label>
                                                                    <span class="badge badge-primary" id="totalMuestras_{{ $index }}">3</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 text-right">
                                                                <!-- The button was moved -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Pestaña Cálculos -->
                                                    <div class="tab-pane fade" id="calculations-content-{{ $index }}" role="tabpanel" aria-labelledby="calculations-tab-{{ $index }}">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="card bg-light">
                                                                    <div class="card-body text-center">
                                                                        <h6 class="card-title">Promedio Arena</h6>
                                                                        <h4 class="text-primary" id="promedioArena_{{ $index }}">0.00%</h4>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="card bg-light">
                                                                    <div class="card-body text-center">
                                                                        <h6 class="card-title">Promedio Arcilla</h6>
                                                                        <h4 class="text-success" id="promedioArcilla_{{ $index }}">0.00%</h4>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="card bg-light">
                                                                    <div class="card-body text-center">
                                                                        <h6 class="card-title">Promedio Limo</h6>
                                                                        <h4 class="text-warning" id="promedioLimo_{{ $index }}">0.00%</h4>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Pestaña Validación -->
                                                    <div class="tab-pane fade" id="validation-content-{{ $index }}" role="tabpanel" aria-labelledby="validation-tab-{{ $index }}">
                                                        <div class="alert alert-info">
                                                            <h6><i class="fas fa-info-circle"></i> Estado de Validación:</h6>
                                                            <div id="validationStatus_{{ $index }}">
                                                                <span class="badge badge-success">✓ Todas las muestras están validadas</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Reglas de Validación:</h6>
                                                                <ul class="list-unstyled">
                                                                    <li><i class="fas fa-check text-success"></i> Porcentajes suman 100% ±0.1%</li>
                                                                    <li><i class="fas fa-check text-success"></i> Códigos internos únicos</li>
                                                                    <li><i class="fas fa-check text-success"></i> Datos numéricos válidos</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Acciones:</h6>
                                                                <button type="button" class="btn btn-outline-primary btn-sm" id="validateAllBtn_{{ $index }}" data-process-index="{{ $index }}">
                                                                    <i class="fas fa-check-double"></i> Validar Todo
                                                                </button>
                                                                <button type="button" class="btn btn-outline-warning btn-sm" id="highlightErrorsBtn_{{ $index }}" data-process-index="{{ $index }}">
                                                                    <i class="fas fa-exclamation-triangle"></i> Resaltar Errores
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Pestaña Exportar -->
                                                    <div class="tab-pane fade" id="export-content-{{ $index }}" role="tabpanel" aria-labelledby="export-tab-{{ $index }}">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Formatos de Exportación:</h6>
                                                                <div class="btn-group-vertical w-100">
                                                                    <button type="button" class="btn btn-outline-success mb-2">
                                                                        <i class="fas fa-file-excel"></i> Exportar a Excel
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-danger mb-2">
                                                                        <i class="fas fa-file-pdf"></i> Exportar a PDF
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-info">
                                                                        <i class="fas fa-file-csv"></i> Exportar a CSV
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Opciones de Exportación:</h6>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="includeCalculations_{{ $index }}" checked>
                                                                    <label class="form-check-label" for="includeCalculations_{{ $index }}">
                                                                        Incluir cálculos automáticos
                                                                    </label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="includeValidation_{{ $index }}" checked>
                                                                    <label class="form-check-label" for="includeValidation_{{ $index }}">
                                                                        Incluir reporte de validación
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-info mt-3">
                                                    <h6><i class="fas fa-info-circle"></i> Instrucciones de uso:</h6>
                                                    <ul class="mb-0">
                                                        <li><strong>Reporte de Resultados Análisis:</strong> Complete las lecturas del hidrómetro a 40 segundos y 2 horas, junto con las temperaturas correspondientes.</li>
                                                        <li><strong>Lecturas corregidas:</strong> Ingrese las lecturas corregidas por temperatura para 40 segundos y 2 horas.</li>
                                                        <li><strong>Resultados % de Textura:</strong> Los porcentajes de Arena, Arcilla y Limo deben sumar aproximadamente 100%.</li>
                                                        <li><strong>Clase textural:</strong> Se determina automáticamente basándose en los porcentajes ingresados.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                <!-- Botones de Acción -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>{{ isset($analysis) ? 'Actualizar Análisis Rechazado' : 'Guardar Todos los Análisis' }}
                                </button>
                                <a href="{{ route('lscefa.technical.analyses.texture.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Regresar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    /* Estilos para las pestañas de navegación */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-radius: 0;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        background-color: #e9ecef;
        color: #495057;
    }
    
    .nav-tabs .nav-link.active {
        color: #007bff;
        background-color: #fff;
        border-bottom: 3px solid #007bff;
        font-weight: 600;
    }
    
    .nav-tabs .nav-link i {
        font-size: 1.1rem;
    }
    
    /* Estilos para el contenido de las pestañas */
    .tab-content {
        padding-top: 1rem;
    }
    
    .tab-pane {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Estilos para la tabla de muestras */
    [id^="muestras_table_"] {
        font-size: 0.9rem;
    }
    
    [id^="muestras_table_"] thead th {
        background-color: #d4edda !important;
        color: #155724;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #c3e6cb;
    }
    
    [id^="muestras_table_"] tbody td {
        vertical-align: middle;
        padding: 0.5rem;
    }
    
    [id^="muestras_table_"] .form-control-sm {
        min-width: 120px;
        width: 100%;
        max-width: 200px;
        font-size: 0.95rem;
        padding: 0.35rem 0.7rem;
        height: auto;
    }
    
    [id^="muestras_table_"] .is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    [id^="muestras_table_"] .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }
    
    .text-primary {
        color: #007bff !important;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    /* Estilos para mejorar el espaciado */
    .card {
        margin-bottom: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Estilos para botones de acción */
    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.15s ease-in-out;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Estilos para alertas */
    .alert {
        border-radius: 0.375rem;
        border: none;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    /* Estilos para la barra de navegación de la tabla */
    [id^="tableNavigation_"] {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 0.5rem;
    }
    
    [id^="tableNavigation_"] .nav-link {
        border-radius: 0.375rem;
        margin: 0 0.25rem;
        padding: 0.75rem 1rem;
        color: #6c757d;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    [id^="tableNavigation_"] .nav-link:hover {
        background-color: #e9ecef;
        color: #495057;
        border-color: #dee2e6;
    }
    
    [id^="tableNavigation_"] .nav-link.active {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.25);
    }
    
    [id^="tableNavigation_"] .nav-link i {
        font-size: 1rem;
        margin-right: 0.5rem;
    }
    
    /* Estilos para el contenido de la navegación de la tabla */
    [id^="tableNavigationContent_"] .tab-pane {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    /* Estilos para las tarjetas de cálculos */
    .card.bg-light {
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .card.bg-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }
    
    .card.bg-light .card-title {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .card.bg-light h4 {
        margin-bottom: 0;
        font-weight: 700;
    }
    
    /* Estilos para botones de exportación */
    .btn-group-vertical .btn {
        text-align: left;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-group-vertical .btn:hover {
        transform: translateX(5px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
    }
    
    /* Estilos para checkboxes */
    .form-check {
        margin-bottom: 0.75rem;
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    /* Estilos para badges */
    .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
    }
    
    .badge-success {
        background-color: #28a745;
        color: #fff;
    }
    
    .badge-primary {
        background-color: #007bff;
        color: #fff;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    /* Estilos para listas de validación */
    .list-unstyled li {
        padding: 0.25rem 0;
        color: #6c757d;
    }
    
    .list-unstyled li i {
        margin-right: 0.5rem;
        width: 1rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        [id^="tableNavigation_"] .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        
        [id^="tableNavigation_"] .nav-link i {
            font-size: 0.875rem;
            margin-right: 0.25rem;
        }
        
        .card.bg-light {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Tabla de factores de corrección por temperatura
const factoresCorreccion = [
    { tempMin: 14.5, tempMax: 15.4, factor: -1.1 },
    { tempMin: 15.5, tempMax: 16.4, factor: -0.9 },
    { tempMin: 16.5, tempMax: 17.4, factor: -0.7 },
    { tempMin: 17.5, tempMax: 18.4, factor: -0.5 },
    { tempMin: 18.5, tempMax: 19.4, factor: -0.3 },
    { tempMin: 19.5, tempMax: 20.4, factor: 0 },
    { tempMin: 20.5, tempMax: 21.4, factor: 0.2 },
    { tempMin: 21.5, tempMax: 22.4, factor: 0.4 },
    { tempMin: 22.5, tempMax: 23.4, factor: 0.7 },
    { tempMin: 23.5, tempMax: 24.4, factor: 1 },
    { tempMin: 24.5, tempMax: 25.4, factor: 1.2 },
    { tempMin: 25.5, tempMax: 26.4, factor: 1.65 },
    { tempMin: 26.5, tempMax: 27.4, factor: 2 },
    { tempMin: 27.5, tempMax: 28.4, factor: 2.5 },
    { tempMin: 28.5, tempMax: 29.4, factor: 3.05 },
    { tempMin: 29.5, tempMax: 30, factor: 3.8 }
];

/**
 * Obtiene el factor de corrección basado en la temperatura
 */
function getFactorCorreccion(temp) {
    if (isNaN(temp)) return 0;
    
    for (let i = 0; i < factoresCorreccion.length; i++) {
        const rango = factoresCorreccion[i];
        if (temp >= rango.tempMin && temp <= rango.tempMax) {
            return rango.factor;
        }
    }
    
    // Si está fuera de los rangos definidos
    if (temp < factoresCorreccion[0].tempMin) {
        return factoresCorreccion[0].factor;
    }
    if (temp > factoresCorreccion[factoresCorreccion.length - 1].tempMax) {
        return factoresCorreccion[factoresCorreccion.length - 1].factor;
    }
    
    return 0;
}

/**
 * Recalcula las lecturas corregidas por temperatura
 */
function recalcularLecturaCorregida(row) {
    // Lecturas a 40 segundos
    const lectura40 = parseFloat(row.find('.lectura-40s').val()) || 0;
    const temp40 = parseFloat(row.find('.temp-40s').val()) || 0;
    const factor40 = getFactorCorreccion(temp40);
    const lecturaCorregida40 = lectura40 + factor40;
    row.find('.lectura-corregida-40s').val(lecturaCorregida40.toFixed(2));
    
    // Lecturas a 2 horas
    const lectura2h = parseFloat(row.find('.lectura-2h').val()) || 0;
    const temp2h = parseFloat(row.find('.temp-2h').val()) || 0;
    const factor2h = getFactorCorreccion(temp2h);
    const lecturaCorregida2h = lectura2h + factor2h;
    row.find('.lectura-corregida-2h').val(lecturaCorregida2h.toFixed(2));
}

/**
 * Función principal para calcular TODOS los porcentajes de textura
 * Esta es la función correcta y única que debe usarse
 */
function calcularPorcentajesTextura(row) {
    // Obtener valores de entrada
    const lecturaCorregida40s = parseFloat(row.find('.lectura-corregida-40s').val()) || 0;
    const lecturaCorregida2h = parseFloat(row.find('.lectura-corregida-2h').val()) || 0;
    const humedad = parseFloat(row.find('.humedad').val()) || 0;
    const peso = parseFloat(row.find('.peso-muestra').val()) || 0;
    
    console.log('Valores de entrada:', {
        lecturaCorregida40s, 
        lecturaCorregida2h, 
        humedad, 
        peso,
        row: row.index()
    });
    
    let arena = 0, arcilla = 0, limo = 0;
    
    if (peso > 0) {
        // FÓRMULAS CORREGIDAS (sin divisiones innecesarias por 100)
        
        // Cálculo de Arena: 100 - ((lectura40s * (humedad + 100)) / peso)
        arena = 100 - ((lecturaCorregida40s * (humedad + 100)) / peso);
        
        // Cálculo de Arcilla: (lectura2h * (humedad + 100)) / peso
        arcilla = (lecturaCorregida2h * (humedad + 100)) / peso;
        
        // Cálculo de Limo: 100 - (arena + arcilla)
        limo = 100 - (arena + arcilla);
    }
    
    // Validar que los valores sean números válidos y positivos
    if (isNaN(arena) || !isFinite(arena)) arena = 0;
    if (isNaN(arcilla) || !isFinite(arcilla)) arcilla = 0;
    if (isNaN(limo) || !isFinite(limo)) limo = 0;
    
    arena = Math.max(0, Math.min(100, arena));
    arcilla = Math.max(0, Math.min(100, arcilla));
    limo = Math.max(0, Math.min(100, limo));
    
    console.log('Valores calculados:', {
        arena: arena.toFixed(2),
        arcilla: arcilla.toFixed(2),
        limo: limo.toFixed(2),
        total: (arena + arcilla + limo).toFixed(2)
    });
    
    // Asignar valores a los campos y forzar eventos
    row.find('.porcentaje-arena').val(arena.toFixed(2)).trigger('input');
    row.find('.porcentaje-arcilla').val(arcilla.toFixed(2)).trigger('input');
    row.find('.porcentaje-limo').val(limo.toFixed(2)).trigger('input');
    // Forzar actualización de promedios y error
    const processIndex = row.closest('[id^="muestras_table_"]').attr('id').replace('muestras_table_', '');
    if (processIndex !== undefined) {
        const idx = processIndex.trim();
        actualizarPromediosDuplicados(idx);
        actualizarErrorArena(idx);
    }
}

/**
 * Determina la clase textural basada en porcentajes
 */
function determinarClaseTextural(arena, arcilla, limo) {
    // USDA Soil Texture Classification
    if (arena >= 85 && limo + arcilla <= 15) {
        return 'Arena';
    } else if (arena >= 70 && limo + arcilla <= 30) {
        if (limo > arcilla) {
            return 'Arena Limosa';
        } else {
            return 'Arena Arcillosa';
        }
    } else if (limo >= 80 && arena + arcilla <= 20) {
        return 'Limo';
    } else if (limo >= 50 && arena + arcilla <= 50) {
        if (arena > arcilla) {
            return 'Limo Arenoso';
        } else {
            return 'Limo Arcilloso';
        }
    } else if (arcilla >= 40 && arena + limo <= 60) {
        if (arena > limo) {
            return 'Arcilla Arenosa';
        } else {
            return 'Arcilla Limosa';
        }
    } else if (arcilla >= 35 && arena + limo <= 65) {
        if (arena > limo) {
            return 'Franco Arenoso';
        } else if (limo > arena) {
            return 'Franco Limoso';
        } else {
            return 'Franco Arcilloso';
        }
    } else {
        return 'Franco';
    }
}

/**
 * Funciones auxiliares para cálculos y validaciones
 */
        function updateSampleCount(processIndex) {
            const count = $(`#muestras_container_${processIndex} tr`).length;
            $(`#totalMuestras_${processIndex}`).text(count);
        }
        
        function calculateAverages(processIndex) {
            let totalArena = 0;
            let totalArcilla = 0;
            let totalLimo = 0;
            let validSamples = 0;
            
            $(`#muestras_container_${processIndex} .muestra-row`).each(function() {
                const arena = parseFloat($(this).find('.porcentaje-arena').val()) || 0;
                const arcilla = parseFloat($(this).find('.porcentaje-arcilla').val()) || 0;
                const limo = parseFloat($(this).find('.porcentaje-limo').val()) || 0;
                
                if (arena > 0 || arcilla > 0 || limo > 0) {
                    totalArena += arena;
                    totalArcilla += arcilla;
                    totalLimo += limo;
                    validSamples++;
                }
            });
            
            if (validSamples > 0) {
                $(`#promedioArena_${processIndex}`).text((totalArena / validSamples).toFixed(2) + '%');
                $(`#promedioArcilla_${processIndex}`).text((totalArcilla / validSamples).toFixed(2) + '%');
                $(`#promedioLimo_${processIndex}`).text((totalLimo / validSamples).toFixed(2) + '%');
            }
        }
        
        function validateAllSamples(processIndex) {
            let isValid = true;
            let errorCount = 0;
            
            $(`#muestras_container_${processIndex} .muestra-row`).each(function() {
                const row = $(this);
                const codigo = row.find('input[name*="codigo_interno"]').val();
                const arena = parseFloat(row.find('.porcentaje-arena').val()) || 0;
                const arcilla = parseFloat(row.find('.porcentaje-arcilla').val()) || 0;
                const limo = parseFloat(row.find('.porcentaje-limo').val()) || 0;
                
                // Reset validation state
                row.removeClass('table-danger table-warning').addClass('table-success');
                
                // Check if code is empty
                if (!codigo.trim()) {
                    row.removeClass('table-success').addClass('table-warning');
                    isValid = false;
                    errorCount++;
                }
                
                // Check if percentages sum to 100%
                const total = arena + arcilla + limo;
                if (total < 99.9 || total > 100.1) {
                    row.removeClass('table-success').addClass('table-danger');
                    isValid = false;
                    errorCount++;
                }
            });
            
            // Update validation status
            if (isValid) {
                $(`#validationStatus_${processIndex}`).html('<span class="badge badge-success">✓ Todas las muestras están validadas</span>');
            } else {
                $(`#validationStatus_${processIndex}`).html(`<span class="badge badge-warning">⚠ ${errorCount} muestra(s) con errores de validación</span>`);
            }
            
            return isValid;
        }
        
function updateProgressIndicator() {
    const activeTab = $('.nav-tabs .nav-link.active');
    const tabIndex = $('.nav-tabs .nav-link').index(activeTab);
    const totalTabs = $('.nav-tabs .nav-link').length;
    const progress = ((tabIndex + 1) / totalTabs) * 100;
    
    // Add progress bar if it doesn't exist
    if ($('.progress-indicator').length === 0) {
        $('.nav-tabs').after(`
            <div class="progress-indicator mt-2">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: ${progress}%" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-1 d-block">Paso ${tabIndex + 1} de ${totalTabs}</small>
            </div>
        `);
    } else {
        $('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
        $('.progress-indicator small').text(`Paso ${tabIndex + 1} de ${totalTabs}`);
    }
}

/**
 * Inicialización y eventos del DOM
 */
$(document).ready(function() {
    // Set default date to today for all date fields
    $('input[type="date"]').val(new Date().toISOString().split('T')[0]);
    
    // Initialize variables for each process
    let controlIndexes = {};
    let muestraIndexes = {};
    
    // Initialize indexes for each process
        @foreach($processes as $index => $process)
        controlIndexes[{{ $index }}] = 1;
        muestraIndexes[{{ $index }}] = 3; // Start with 3 rows for each process
    @endforeach
    
    // EVENTOS PRINCIPALES - Solo se registran UNA vez
    
    // 1. Evento para recalcular lecturas corregidas cuando cambien temperatura o lecturas
    $(document).on('input change', '.lectura-40s, .temp-40s, .lectura-2h, .temp-2h', function() {
        const row = $(this).closest('tr');
        recalcularLecturaCorregida(row);
        // Después de corregir lecturas, recalcular porcentajes
        setTimeout(() => calcularPorcentajesTextura(row), 50);
    });
    
    // 2. Evento para recalcular porcentajes cuando cambien peso o humedad
    $(document).on('input change', '.peso-muestra, .humedad', function() {
        const row = $(this).closest('tr');
        calcularPorcentajesTextura(row);
    });
    
    // 3. Evento para recalcular cuando cambien las lecturas corregidas directamente
    $(document).on('input change', '.lectura-corregida-40s, .lectura-corregida-2h', function() {
        const row = $(this).closest('tr');
        calcularPorcentajesTextura(row);
    });
    
    // 4. Evento para actualizar promedios cuando cambien los porcentajes
    $(document).on('input change', '.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo', function() {
        const processIndex = $(this).closest('[id^="muestras_table_"]').attr('id').replace('muestras_table_', '');
        calculateAverages(processIndex);
    });
    
    // Add new muestra for specific process
    $(document).on('click', '[id^="addMuestraBtn_"]', function() {
        const processIndex = $(this).data('process-index');
        const muestraIndex = muestraIndexes[processIndex];
        
        // En el script de agregar fila, modificar para que solo las primeras N filas tengan ID de cotización
        const quoteIds = @json($processes->pluck('quote_id'));
        // Contar solo las filas de muestra con ID de cotización (excluyendo blanco y filas sin ID)
        const muestraRows = $(`#muestras_container_${processIndex} .muestra-row td:first-child`).filter(function(){ return $(this).text().trim() !== ''; }).length;
        if (muestraRows >= quoteIds.length) {
            alert('No hay más muestras para procesar: ya se han asignado todas las cotizaciones seleccionadas.');
            return;
        }
        let idCell = '';
        if (quoteIds.length > 0) {
            idCell = quoteIds[muestraRows];
        }
        const newMuestra = `
            <tr class="muestra-row">
                <td>${idCell}</td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][codigo_interno]" placeholder="Nombre de la muestra">
                </td>
                <td><input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[${processIndex}][items][${muestraIndex}][peso]" placeholder="0.0000"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-40s" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_40s]" placeholder="0.00"></td>
                <td><input type="number" step="0.1" class="form-control form-control-sm temp-40s" name="analyses[${processIndex}][items][${muestraIndex}][temperatura_40s]" placeholder="0.0"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-2h" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_2h]" placeholder="0.00"></td>
                <td><input type="number" step="0.1" class="form-control form-control-sm temp-2h" name="analyses[${processIndex}][items][${muestraIndex}][temperatura_2h]" placeholder="0.0"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_corregidas_40s]" placeholder="0.00" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_corregidas_2h]" placeholder="0.00" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm humedad" name="analyses[${processIndex}][items][${muestraIndex}][humedad]" placeholder="0.00"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[${processIndex}][items][${muestraIndex}][porcentaje_arena]" placeholder="0.00" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[${processIndex}][items][${muestraIndex}][porcentaje_arcilla]" placeholder="0.00" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[${processIndex}][items][${muestraIndex}][porcentaje_limo]" placeholder="0.00" readonly></td>
                <td>
                    <select class="form-control form-control-sm clase-textural" name="analyses[${processIndex}][items][${muestraIndex}][clase_textural]">
                        <option value="">Seleccionar</option>
                        <option value="Arena">Arena</option>
                        <option value="Arena Limosa">Arena Limosa</option>
                        <option value="Arena Arcillosa">Arena Arcillosa</option>
                        <option value="Limo">Limo</option>
                        <option value="Limo Arenoso">Limo Arenoso</option>
                        <option value="Limo Arcilloso">Limo Arcilloso</option>
                        <option value="Arcilla">Arcilla</option>
                        <option value="Arcilla Arenosa">Arcilla Arenosa</option>
                        <option value="Arcilla Limosa">Arcilla Limosa</option>
                        <option value="Franco Arenoso">Franco Arenoso</option>
                        <option value="Franco Limoso">Franco Limoso</option>
                        <option value="Franco Arcilloso">Franco Arcilloso</option>
                        <option value="Franco">Franco</option>
                    </select>
                </td>
                <td><textarea class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][observaciones]" rows="2" placeholder="Observaciones"></textarea></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-muestra" title="Eliminar muestra">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $(`#muestras_container_${processIndex}`).append(newMuestra);
        muestraIndexes[processIndex]++;
        setTimeout(() => updateSampleCount(processIndex), 100);
    });
    
    // Remove muestra
    $(document).on('click', '.remove-muestra', function() {
        const processIndex = $(this).closest('[id^="muestras_table_"]').attr('id').replace('muestras_table_', '');
        if ($(`#muestras_container_${processIndex} tr`).length > 3) {
            $(this).closest('tr').remove();
            setTimeout(() => updateSampleCount(processIndex), 100);
        } else {
            alert('Debe mantener al menos 3 muestras.');
        }
    });
    
    // Tab navigation enhancement
    $('.nav-tabs .nav-link').on('click', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        
        $('.nav-tabs .nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        $(this).addClass('active');
        $(target).addClass('show active');
        
        updateProgressIndicator();
    });
    
    // Initialize progress indicator
    updateProgressIndicator();
    
    // Table navigation functionality for each process
    @foreach($processes as $index => $process)
        $(`#tableNavigation_{{ $index }} .nav-link`).on('click', function(e) {
            e.preventDefault();
            const target = $(this).attr('href');
            
            $(`#tableNavigation_{{ $index }} .nav-link`).removeClass('active');
            $(`#tableNavigationContent_{{ $index }} .tab-pane`).removeClass('show active');
            
            $(this).addClass('active');
            $(target).addClass('show active');
        });
        
        // Highlight errors button for specific process
            $(`#highlightErrorsBtn_{{ $index }}`).on('click', function() {
                $(`#muestras_container_{{ $index }} .muestra-row`).each(function() {
                    const row = $(this);
                    const arena = parseFloat(row.find('.porcentaje-arena').val()) || 0;
                    const arcilla = parseFloat(row.find('.porcentaje-arcilla').val()) || 0;
                    const limo = parseFloat(row.find('.porcentaje-limo').val()) || 0;
                    const total = arena + arcilla + limo;
                    
                    if (total < 99.9 || total > 100.1) {
                        row.find('.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo').addClass('is-invalid');
                    } else {
                        row.find('.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo').removeClass('is-invalid');
                    }
                });
            });
            
            // Validate all button for specific process
            $(`#validateAllBtn_{{ $index }}`).on('click', function() {
                validateAllSamples({{ $index }});
        });
        
        // Initialize for each process
            updateSampleCount({{ $index }});
            calculateAverages({{ $index }});
        @endforeach
        
        // Form validation
        $('#textureBatchForm').on('submit', function(e) {
            var isValid = true;
            
            $('input[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
                return false;
            }
            
            $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
        });
    
    // Calcular porcentajes iniciales para filas existentes
    $('.muestra-row').each(function() {
        calcularPorcentajesTextura($(this));
    });

    // Si estamos editando un análisis rechazado, cargar los datos existentes
    @if(isset($analysis))
        // Cargar datos del análisis rechazado
        const analysisData = @json($analysis);
        
        console.log('Cargando datos del análisis rechazado:', analysisData);
        
        // Cargar TODOS los campos básicos del análisis
        if (analysisData.consecutive_no) $('#consecutivo_no').val(analysisData.consecutive_no);
        if (analysisData.analysis_date) $('#fecha_analisis').val(analysisData.analysis_date);
        if (analysisData.analyst_name) $('#nombre_analista').val(analysisData.analyst_name);
        if (analysisData.methodology_used) $('#metodologia_utilizada').val(analysisData.methodology_used);
        if (analysisData.thermometer_code) $('#codigo_termometro').val(analysisData.thermometer_code);
        if (analysisData.hydrometer_code) $('#codigo_hidrometro').val(analysisData.hydrometer_code);
        if (analysisData.equipment_used) $('#equipment_used').val(analysisData.equipment_used);
        if (analysisData.method_interval) $('#method_interval').val(analysisData.method_interval);
        
        // Cargar campos de precisión analítica (Duplicados A y B) desde analytical_controls
        @if(isset($precisionData) && count($precisionData) > 0)
            console.log('Cargando datos de precisión analítica:', @json($precisionData));
            
            @foreach($precisionData as $precision)
                @if(($precision['tipo'] ?? '') === 'duplicado_a')
                    // Duplicado A
                    $('input[name="analyses[0][duplicado_a_codigo]"]').val('{{ $precision['codigo_interno'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_promedio_arena]"]').val('{{ $precision['arena_1'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_promedio_arcilla]"]').val('{{ $precision['arcilla_1'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_promedio_limo]"]').val('{{ $precision['limo_1'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_dpr_arena]"]').val('{{ $precision['dpr_arena'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_dpr_arcilla]"]').val('{{ $precision['dpr_arcilla'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_dpr_limo]"]').val('{{ $precision['dpr_limo'] ?? '' }}');
                    $('select[name="analyses[0][duplicado_a_aceptabilidad]"]').val('{{ $precision['aceptabilidad_control'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_a_observaciones]"]').val('{{ $precision['observaciones'] ?? '' }}');
                @elseif(($precision['tipo'] ?? '') === 'duplicado_b')
                    // Duplicado B
                    $('input[name="analyses[0][duplicado_b_codigo]"]').val('{{ $precision['codigo_interno'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_promedio_arena]"]').val('{{ $precision['arena_1'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_promedio_arcilla]"]').val('{{ $precision['arcilla_1'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_promedio_limo]"]').val('{{ $precision['limo_1'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_dpr_arena]"]').val('{{ $precision['dpr_arena'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_dpr_arcilla]"]').val('{{ $precision['dpr_arcilla'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_dpr_limo]"]').val('{{ $precision['dpr_limo'] ?? '' }}');
                    $('select[name="analyses[0][duplicado_b_aceptabilidad]"]').val('{{ $precision['aceptabilidad_control'] ?? '' }}');
                    $('input[name="analyses[0][duplicado_b_observaciones]"]').val('{{ $precision['observaciones'] ?? '' }}');
                @endif
            @endforeach
        @endif
        
        // Cargar campos de material de referencia (Exactitud) desde analytical_controls
        @if(isset($accuracyData) && count($accuracyData) > 0)
            console.log('Cargando datos de exactitud:', @json($accuracyData));
            
            @foreach($accuracyData as $accuracy)
                @if(($accuracy['tipo'] ?? '') === 'material_referencia')
                    $('input[name="analyses[0][material_referencia_lote]"]').val('{{ $accuracy['codigo_interno'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_esperado_arena]"]').val('{{ $accuracy['arena_1'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_esperado_arcilla]"]').val('{{ $accuracy['arcilla_1'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_esperado_limo]"]').val('{{ $accuracy['limo_1'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_obtenido_arena]"]').val('{{ $accuracy['dpr_arena'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_obtenido_arcilla]"]').val('{{ $accuracy['dpr_arcilla'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_obtenido_limo]"]').val('{{ $accuracy['dpr_limo'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_aceptabilidad]"]').val('{{ $accuracy['aceptabilidad_control'] ?? '' }}');
                    $('input[name="analyses[0][material_referencia_observaciones]"]').val('{{ $accuracy['observaciones'] ?? '' }}');
                @endif
            @endforeach
        @endif
        
        // Cargar observaciones generales
        if (analysisData.general_observations) $('textarea[name*="[observaciones_generales]"]').val(analysisData.general_observations);
        
        // Cargar muestras si existen
        if (analysisData.samples) {
            try {
                const samples = JSON.parse(analysisData.samples);
                if (Array.isArray(samples) && samples.length > 0) {
                    // Limpiar filas existentes excepto la primera (blanco)
                    $('#muestras_container_0 tr:not(:first)').remove();
                    
                    // Agregar cada muestra
                    samples.forEach((sample, index) => {
                        if (index === 0) {
                            // Actualizar la primera fila (blanco)
                            $('#muestras_container_0 tr:first').find('input[name*="[peso]"]').val(sample.peso || '');
                            $('#muestras_container_0 tr:first').find('input[name*="[lecturas_40s]"]').val(sample.lecturas_40s || '');
                            $('#muestras_container_0 tr:first').find('input[name*="[temperatura_40s]"]').val(sample.temperatura_40s || '');
                            $('#muestras_container_0 tr:first').find('input[name*="[lecturas_2h]"]').val(sample.lecturas_2h || '');
                            $('#muestras_container_0 tr:first').find('input[name*="[temperatura_2h]"]').val(sample.temperatura_2h || '');
                            $('#muestras_container_0 tr:first').find('input[name*="[humedad]"]').val(sample.humedad || '');
                            $('#muestras_container_0 tr:first').find('input[name*="[observaciones]"]').val(sample.observaciones || '');
                        } else {
                            // Agregar nuevas filas para muestras adicionales
                            const newRow = `
                                <tr class="muestra-row">
                                    <td></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="analyses[0][items][${index}][codigo_interno]" value="${sample.codigo_interno || ''}">
                                    </td>
                                    <td><input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[0][items][${index}][peso]" value="${sample.peso || ''}" placeholder="0.0000"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-40s" name="analyses[0][items][${index}][lecturas_40s]" value="${sample.lecturas_40s || ''}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.1" class="form-control form-control-sm temp-40s" name="analyses[0][items][${index}][temperatura_40s]" value="${sample.temperatura_40s || ''}" placeholder="0.0"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-2h" name="analyses[0][items][${index}][lecturas_2h]" value="${sample.lecturas_2h || ''}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.1" class="form-control form-control-sm temp-2h" name="analyses[0][items][${index}][temperatura_2h]" value="${sample.temperatura_2h || ''}" placeholder="0.0"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[0][items][${index}][lecturas_corregidas_40s]" value="${sample.lecturas_corregidas_40s || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[0][items][${index}][lecturas_corregidas_2h]" value="${sample.lecturas_corregidas_2h || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm humedad" name="analyses[0][items][${index}][humedad]" value="${sample.humedad || ''}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[0][items][${index}][porcentaje_arena]" value="${sample.porcentaje_arena || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[0][items][${index}][porcentaje_arcilla]" value="${sample.porcentaje_arcilla || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[0][items][${index}][porcentaje_limo]" value="${sample.porcentaje_limo || ''}" placeholder="0.00" readonly></td>
                                    <td>
                                        <select class="form-control form-control-sm clase-textural" name="analyses[0][items][${index}][clase_textural]">
                                            <option value="">Seleccionar</option>
                                            <option value="Arena" ${sample.clase_textural === 'Arena' ? 'selected' : ''}>Arena</option>
                                            <option value="Arena Limosa" ${sample.clase_textural === 'Arena Limosa' ? 'selected' : ''}>Arena Limosa</option>
                                            <option value="Arena Arcillosa" ${sample.clase_textural === 'Arena Arcillosa' ? 'selected' : ''}>Arena Arcillosa</option>
                                            <option value="Limo" ${sample.clase_textural === 'Limo' ? 'selected' : ''}>Limo</option>
                                            <option value="Limo Arenoso" ${sample.clase_textural === 'Limo Arenoso' ? 'selected' : ''}>Limo Arenoso</option>
                                            <option value="Limo Arcilloso" ${sample.clase_textural === 'Limo Arcilloso' ? 'selected' : ''}>Limo Arcilloso</option>
                                            <option value="Arcilla" ${sample.clase_textural === 'Arcilla' ? 'selected' : ''}>Arcilla</option>
                                            <option value="Arcilla Arenosa" ${sample.clase_textural === 'Arcilla Arenosa' ? 'selected' : ''}>Arcilla Arenosa</option>
                                            <option value="Arcilla Limosa" ${sample.clase_textural === 'Arcilla Limosa' ? 'selected' : ''}>Arcilla Limosa</option>
                                            <option value="Franco Arenoso" ${sample.clase_textural === 'Franco Arenoso' ? 'selected' : ''}>Franco Arenoso</option>
                                            <option value="Franco Limoso" ${sample.clase_textural === 'Franco Limoso' ? 'selected' : ''}>Franco Limoso</option>
                                            <option value="Franco Arcilloso" ${sample.clase_textural === 'Franco Arcilloso' ? 'selected' : ''}>Franco Arcilloso</option>
                                            <option value="Franco" ${sample.clase_textural === 'Franco' ? 'selected' : ''}>Franco</option>
                                        </select>
                                    </td>
                                    <td><textarea class="form-control form-control-sm" name="analyses[0][items][${index}][observaciones]" rows="2" placeholder="Observaciones">${sample.observaciones || ''}</textarea></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-muestra" title="Eliminar muestra">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            $('#muestras_container_0').append(newRow);
                        }
                    });
                    
                    // Recalcular porcentajes para todas las filas
                    $('#muestras_container_0 .muestra-row').each(function() {
                        calcularPorcentajesTextura($(this));
                    });
                    
                    // Actualizar contador de muestras
                    updateSampleCount(0);
                }
            } catch (e) {
                console.error('Error parsing samples:', e);
            }
        }
        
        // Cargar controles analíticos si existen
        if (analysisData.analytical_controls) {
            try {
                const controls = JSON.parse(analysisData.analytical_controls);
                if (Array.isArray(controls) && controls.length > 0) {
                    console.log('Cargando controles analíticos:', controls);
                    
                    // Limpiar controles existentes
                    $('#controles_container_0 tr:not(:first)').remove();
                    
                    // Agregar cada control analítico
                    controls.forEach((control, index) => {
                        if (index === 0) {
                            // Actualizar la primera fila
                            $('#controles_container_0 tr:first').find('input[name*="[codigo_interno]"]').val(control.codigo_interno || '');
                            $('#controles_container_0 tr:first').find('input[name*="[peso_muestra]"]').val(control.peso_muestra || '');
                            $('#controles_container_0 tr:first').find('input[name*="[lectura_40s]"]').val(control.lectura_40s || '');
                            $('#controles_container_0 tr:first').find('input[name*="[temperatura_40s]"]').val(control.temperatura_40s || '');
                            $('#controles_container_0 tr:first').find('input[name*="[lectura_2h]"]').val(control.lectura_2h || '');
                            $('#controles_container_0 tr:first').find('input[name*="[temperatura_2h]"]').val(control.temperatura_2h || '');
                            $('#controles_container_0 tr:first').find('input[name*="[humedad]"]').val(control.humedad || '');
                            $('#controles_container_0 tr:first').find('textarea[name*="[observaciones]"]').val(control.observaciones || '');
                        } else {
                            // Agregar nuevas filas para controles adicionales
                            const newControlRow = `
                                <tr class="control-row">
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][codigo_interno]" value="${control.codigo_interno || ''}">
                                    </td>
                                    <td><input type="number" step="0.0001" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][peso_muestra]" value="${control.peso_muestra || ''}" placeholder="0.0000"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][lectura_40s]" value="${control.lectura_40s || ''}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.1" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][temperatura_40s]" value="${control.temperatura_40s || ''}" placeholder="0.0"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][lectura_2h]" value="${control.lectura_2h || ''}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.1" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][temperatura_2h]" value="${control.temperatura_2h || ''}" placeholder="0.0"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-40s" name="analyses[0][analytical_controls][${index}][lectura_corregida_40s]" value="${control.lectura_corregida_40s || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm lectura-corregida-2h" name="analyses[0][analytical_controls][${index}][lectura_corregida_2h]" value="${control.lectura_corregida_2h || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][humedad]" value="${control.humedad || ''}" placeholder="0.00"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[0][analytical_controls][${index}][porcentaje_arena]" value="${control.porcentaje_arena || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[0][analytical_controls][${index}][porcentaje_arcilla]" value="${control.porcentaje_arcilla || ''}" placeholder="0.00" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[0][analytical_controls][${index}][porcentaje_limo]" value="${control.porcentaje_limo || ''}" placeholder="0.00" readonly></td>
                                    <td>
                                        <select class="form-control form-control-sm clase-textural" name="analyses[0][analytical_controls][${index}][clase_textural]">
                                            <option value="">Seleccionar</option>
                                            <option value="Arena" ${control.clase_textural === 'Arena' ? 'selected' : ''}>Arena</option>
                                            <option value="Arena Limosa" ${control.clase_textural === 'Arena Limosa' ? 'selected' : ''}>Arena Limosa</option>
                                            <option value="Arena Arcillosa" ${control.clase_textural === 'Arena Arcillosa' ? 'selected' : ''}>Arena Arcillosa</option>
                                            <option value="Limo" ${control.clase_textural === 'Limo' ? 'selected' : ''}>Limo</option>
                                            <option value="Limo Arenoso" ${control.clase_textural === 'Limo Arenoso' ? 'selected' : ''}>Limo Arenoso</option>
                                            <option value="Limo Arcilloso" ${control.clase_textural === 'Limo Arcilloso' ? 'selected' : ''}>Limo Arcilloso</option>
                                            <option value="Arcilla" ${control.clase_textural === 'Arcilla' ? 'selected' : ''}>Arcilla</option>
                                            <option value="Arcilla Arenosa" ${control.clase_textural === 'Arcilla Arenosa' ? 'selected' : ''}>Arcilla Arenosa</option>
                                            <option value="Arcilla Limosa" ${control.clase_textural === 'Arcilla Limosa' ? 'selected' : ''}>Arcilla Limosa</option>
                                            <option value="Franco Arenoso" ${control.clase_textural === 'Franco Arenoso' ? 'selected' : ''}>Franco Arenoso</option>
                                            <option value="Franco Limoso" ${control.clase_textural === 'Franco Limoso' ? 'selected' : ''}>Franco Limoso</option>
                                            <option value="Franco Arcilloso" ${control.clase_textural === 'Franco Arcilloso' ? 'selected' : ''}>Franco Arcilloso</option>
                                            <option value="Franco" ${control.clase_textural === 'Franco' ? 'selected' : ''}>Franco</option>
                                        </select>
                                    </td>
                                    <td><textarea class="form-control form-control-sm" name="analyses[0][analytical_controls][${index}][observaciones]" rows="2" placeholder="Observaciones">${control.observaciones || ''}</textarea></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-control" title="Eliminar control">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            $('#controles_container_0').append(newControlRow);
                        }
                    });
                    
                    // Recalcular porcentajes para todas las filas de controles
                    $('#controles_container_0 .control-row').each(function() {
                        calcularPorcentajesTextura($(this));
                    });
                    
                    // Actualizar contador de controles
                    updateControlCount(0);
                }
            } catch (e) {
                console.error('Error parsing analytical controls:', e);
            }
        }
        
        // Sincronizar campos generales a todos los procesos después de cargar los datos
        setTimeout(() => {
            const fields = ['consecutivo_no', 'fecha_analisis', 'nombre_analista', 'metodologia_utilizada', 'codigo_termometro', 'codigo_hidrometro'];
            fields.forEach(function(field) {
                const value = $('#' + field).val();
                if (value) {
                    $('[id^="' + field + '_"]').each(function() {
                        $(this).val(value);
                    });
                }
            });
        }, 500);
    @endif
    });

    // === PROMEDIOS DE DUPLICADOS EN PRECISIÓN ANALÍTICA ===
    function actualizarPromediosDuplicados(processIndex) {
        // Buscar todas las filas de la tabla de muestras
        const $muestras = $(`#muestras_container_${processIndex} .muestra-row`);
        const total = $muestras.length;
        if (total >= 2) {
            // La primera después de blanco del proceso (índice 1)
            const $dupA = $muestras.eq(1);
            // La última fila
            const $dupB = $muestras.eq(total - 1);

            // Código interno
            const codigoA = $dupA.find('input[name*="[codigo_interno]"]').val() || '';
            const codigoB = $dupB.find('input[name*="[codigo_interno]"]').val() || '';
            $(`input[name='analyses[${processIndex}][duplicado_a_codigo]']`).val(codigoA);
            $(`input[name='analyses[${processIndex}][duplicado_b_codigo]']`).val(codigoB);

            // Arena
            const arenaA = parseFloat($dupA.find('.porcentaje-arena').val());
            const arenaB = parseFloat($dupB.find('.porcentaje-arena').val());
            const promedioArena = (isFinite(arenaA) && isFinite(arenaB)) ? ((arenaA + arenaB) / 2).toFixed(2) : '';
            $(`input[name='analyses[${processIndex}][duplicado_a_promedio_arena]']`).val(promedioArena);
            $(`input[name='analyses[${processIndex}][duplicado_b_promedio_arena]']`).val(promedioArena);
            // Arcilla
            const arcillaA = parseFloat($dupA.find('.porcentaje-arcilla').val());
            const arcillaB = parseFloat($dupB.find('.porcentaje-arcilla').val());
            const promedioArcilla = (isFinite(arcillaA) && isFinite(arcillaB)) ? ((arcillaA + arcillaB) / 2).toFixed(2) : '';
            $(`input[name='analyses[${processIndex}][duplicado_a_promedio_arcilla]']`).val(promedioArcilla);
            $(`input[name='analyses[${processIndex}][duplicado_b_promedio_arcilla]']`).val(promedioArcilla);
            // Limo
            const limoA = parseFloat($dupA.find('.porcentaje-limo').val());
            const limoB = parseFloat($dupB.find('.porcentaje-limo').val());
            const promedioLimo = (isFinite(limoA) && isFinite(limoB)) ? ((limoA + limoB) / 2).toFixed(2) : '';
            $(`input[name='analyses[${processIndex}][duplicado_a_promedio_limo]']`).val(promedioLimo);
            $(`input[name='analyses[${processIndex}][duplicado_b_promedio_limo]']`).val(promedioLimo);

            // Calcular DPR para cada parámetro
            function calcDPR(a, b) {
                if (!isFinite(a) || !isFinite(b)) return '';
                const mean = (a + b) / 2;
                if (Math.abs(mean) < 1e-9) return '';
                return (Math.abs(a - b) / mean * 100).toFixed(2);
            }
            // Arena
            const dprArena = calcDPR(arenaA, arenaB);
            $(`input[name='analyses[${processIndex}][duplicado_a_dpr_arena]']`).val(dprArena);
            $(`input[name='analyses[${processIndex}][duplicado_b_dpr_arena]']`).val(dprArena);
            // Arcilla
            const dprArcilla = calcDPR(arcillaA, arcillaB);
            $(`input[name='analyses[${processIndex}][duplicado_a_dpr_arcilla]']`).val(dprArcilla);
            $(`input[name='analyses[${processIndex}][duplicado_b_dpr_arcilla]']`).val(dprArcilla);
            // Limo
            const dprLimo = calcDPR(limoA, limoB);
            $(`input[name='analyses[${processIndex}][duplicado_a_dpr_limo]']`).val(dprLimo);
            $(`input[name='analyses[${processIndex}][duplicado_b_dpr_limo]']`).val(dprLimo);
        } else if (total === 2) {
            // Solo hay una muestra además de blanco, usarla para ambos duplicados
            const $dup = $muestras.eq(1);
            const codigo = $dup.find('input[name*="[codigo_interno]"]').val() || '';
            $(`input[name='analyses[${processIndex}][duplicado_a_codigo]']`).val(codigo);
            $(`input[name='analyses[${processIndex}][duplicado_b_codigo]']`).val(codigo);
            const arena = parseFloat($dup.find('.porcentaje-arena').val());
            const arcilla = parseFloat($dup.find('.porcentaje-arcilla').val());
            const limo = parseFloat($dup.find('.porcentaje-limo').val());
            const promedioArena = isFinite(arena) ? arena.toFixed(2) : '';
            const promedioArcilla = isFinite(arcilla) ? arcilla.toFixed(2) : '';
            const promedioLimo = isFinite(limo) ? limo.toFixed(2) : '';
            $(`input[name='analyses[${processIndex}][duplicado_a_promedio_arena]']`).val(promedioArena);
            $(`input[name='analyses[${processIndex}][duplicado_b_promedio_arena]']`).val(promedioArena);
            $(`input[name='analyses[${processIndex}][duplicado_a_promedio_arcilla]']`).val(promedioArcilla);
            $(`input[name='analyses[${processIndex}][duplicado_b_promedio_arcilla]']`).val(promedioArcilla);
            $(`input[name='analyses[${processIndex}][duplicado_a_promedio_limo]']`).val(promedioLimo);
            $(`input[name='analyses[${processIndex}][duplicado_b_promedio_limo]']`).val(promedioLimo);
            // DPR será 0 o vacío
            $(`input[name='analyses[${processIndex}][duplicado_a_dpr_arena]']`).val('');
            $(`input[name='analyses[${processIndex}][duplicado_b_dpr_arena]']`).val('');
            $(`input[name='analyses[${processIndex}][duplicado_a_dpr_arcilla]']`).val('');
            $(`input[name='analyses[${processIndex}][duplicado_b_dpr_arcilla]']`).val('');
            $(`input[name='analyses[${processIndex}][duplicado_a_dpr_limo]']`).val('');
            $(`input[name='analyses[${processIndex}][duplicado_b_dpr_limo]']`).val('');
        }
    }

    // Llama a la función cada vez que cambian los porcentajes de duplicados
    $(document).on('input change', '.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo', function() {
        const processIndex = $(this).closest('[id^="muestras_table_"]').attr('id').replace('muestras_table_', '');
        actualizarPromediosDuplicados(processIndex);
    });
    // Inicializa al cargar
    @foreach($processes as $index => $process)
        actualizarPromediosDuplicados({{ $index }});
    @endforeach

    // === CÁLCULO DE ERROR DE ARENA ENTRE PRIMERA Y ÚLTIMA FILA ===
    function actualizarErrorArena(processIndex) {
        const $muestras = $(`#muestras_container_${processIndex} .muestra-row`);
        let mensaje = '';
        if ($muestras.length >= 2) {
            const $primera = $muestras.first();
            const $ultima = $muestras.last();
            const arenaPrimera = parseFloat($primera.find('.porcentaje-arena').val());
            const arenaUltima = parseFloat($ultima.find('.porcentaje-arena').val());
            let error = '';
            if (isFinite(arenaPrimera) && isFinite(arenaUltima) && arenaPrimera !== 0) {
                error = (((arenaPrimera - arenaUltima) / arenaPrimera) * 100).toFixed(2);
                mensaje = `Error de Arena entre primera y última fila: ${error}%`;
            } else {
                mensaje = '';
            }
            $(`input[name='analyses[${processIndex}][material_referencia_error_arena]']`).val(error);
        }
        $(`#errorArenaVisible_${processIndex}`).text(mensaje);
    }
    // Llama a la función cada vez que cambian los porcentajes de arena
    $(document).on('input change', '.porcentaje-arena', function() {
        const processIndex = $(this).closest('[id^="muestras_table_"]').attr('id').replace('muestras_table_', '');
        actualizarErrorArena(processIndex);
    });
    // Inicializa al cargar
    @foreach($processes as $index => $process)
        actualizarErrorArena({{ $index }});
    @endforeach

    // Utilidad para parsear números
    function parseNumberSafe(v) {
      if (v === null || v === undefined) return NaN;
      const s = String(v).trim().replace(/\s+/g, '').replace(',', '.');
      const n = parseFloat(s);
      return Number.isFinite(n) ? n : NaN;
    }

    // Calcula %DPR entre dos valores
    function calcularDPRvalor(aVal, bVal) {
      const a = parseNumberSafe(aVal);
      const b = parseNumberSafe(bVal);
      if (!Number.isFinite(a) || !Number.isFinite(b)) return '';
      const mean = (a + b) / 2;
      if (Math.abs(mean) < 1e-9) return '';
      const dpr = (Math.abs(a - b) / mean) * 100;
      return Number.isFinite(dpr) ? dpr : '';
    }

    // Actualiza los DPR en la tabla de controles analíticos
    function actualizarDPR(processIndex, thresholdPercent = 15) {
      // Busca los inputs de promedios en controles analíticos
      const $arenaA = $(`input[name='analyses[${processIndex}][duplicado_a_promedio_arena]']`);
      const $arenaB = $(`input[name='analyses[${processIndex}][duplicado_b_promedio_arena]']`);
      const $arcillaA = $(`input[name='analyses[${processIndex}][duplicado_a_promedio_arcilla]']`);
      const $arcillaB = $(`input[name='analyses[${processIndex}][duplicado_b_promedio_arcilla]']`);
      const $limoA = $(`input[name='analyses[${processIndex}][duplicado_a_promedio_limo]']`);
      const $limoB = $(`input[name='analyses[${processIndex}][duplicado_b_promedio_limo]']`);
      // Arena
      const dpr_arena = calcularDPRvalor($arenaA.val(), $arenaB.val());
      $(`input[name='analyses[${processIndex}][duplicado_a_dpr_arena]']`).val(dpr_arena === '' ? '' : Number(dpr_arena).toFixed(2));
      $(`input[name='analyses[${processIndex}][duplicado_b_dpr_arena]']`).val(dpr_arena === '' ? '' : Number(dpr_arena).toFixed(2));
      // Arcilla
      const dpr_arcilla = calcularDPRvalor($arcillaA.val(), $arcillaB.val());
      $(`input[name='analyses[${processIndex}][duplicado_a_dpr_arcilla]']`).val(dpr_arcilla === '' ? '' : Number(dpr_arcilla).toFixed(2));
      $(`input[name='analyses[${processIndex}][duplicado_b_dpr_arcilla]']`).val(dpr_arcilla === '' ? '' : Number(dpr_arcilla).toFixed(2));
      // Limo
      const dpr_limo = calcularDPRvalor($limoA.val(), $limoB.val());
      $(`input[name='analyses[${processIndex}][duplicado_a_dpr_limo]']`).val(dpr_limo === '' ? '' : Number(dpr_limo).toFixed(2));
      $(`input[name='analyses[${processIndex}][duplicado_b_dpr_limo]']`).val(dpr_limo === '' ? '' : Number(dpr_limo).toFixed(2));
    }

    // Bind para recalcular DPR cuando cambian promedios
    function bindDPRListeners(processIndex, thresholdPercent = 15) {
      $(document).on('input change', `input[name='analyses[${processIndex}][duplicado_a_promedio_arena]'], input[name='analyses[${processIndex}][duplicado_b_promedio_arena]'], input[name='analyses[${processIndex}][duplicado_a_promedio_arcilla]'], input[name='analyses[${processIndex}][duplicado_b_promedio_arcilla]'], input[name='analyses[${processIndex}][duplicado_a_promedio_limo]'], input[name='analyses[${processIndex}][duplicado_b_promedio_limo]']`, function() {
        actualizarDPR(processIndex, thresholdPercent);
      });
    }

    // Inicializa listeners y cálculo DPR para cada proceso
    @foreach($processes as $index => $process)
      bindDPRListeners({{ $index }});
      actualizarDPR({{ $index }});
    @endforeach

    // En la tabla de exactitud, dejar solo un campo para %Error promedio (colspan=3)
    function calcularErrorExactitudPromedio(processIndex) {
        console.log('Calculando error exactitud para proceso:', processIndex);
        
        // Obtener valores obtenidos y esperados
        var arenaObtenido = parseFloat($(`input[name='analyses[${processIndex}][material_referencia_obtenido_arena]']`).val()) || 0;
        var arcillaObtenido = parseFloat($(`input[name='analyses[${processIndex}][material_referencia_obtenido_arcilla]']`).val()) || 0;
        var limoObtenido = parseFloat($(`input[name='analyses[${processIndex}][material_referencia_obtenido_limo]']`).val()) || 0;
        
        var arenaEsperado = parseFloat($(`input[name='analyses[${processIndex}][material_referencia_esperado_arena]']`).val()) || 0;
        var arcillaEsperado = parseFloat($(`input[name='analyses[${processIndex}][material_referencia_esperado_arcilla]']`).val()) || 0;
        var limoEsperado = parseFloat($(`input[name='analyses[${processIndex}][material_referencia_esperado_limo]']`).val()) || 0;
        
        console.log('Valores obtenidos:', { arenaObtenido, arcillaObtenido, limoObtenido });
        console.log('Valores esperados:', { arenaEsperado, arcillaEsperado, limoEsperado });

        // Función para calcular %Error absoluto
        function calcError(obtenido, esperado) {
            if (!isFinite(obtenido) || !isFinite(esperado) || esperado === 0) return null;
            return Math.abs((obtenido - esperado) / esperado) * 100; // Convertir a porcentaje aquí
        }
        
        var errorArena = calcError(arenaObtenido, arenaEsperado);
        var errorArcilla = calcError(arcillaObtenido, arcillaEsperado);
        var errorLimo = calcError(limoObtenido, limoEsperado);
        
        console.log('Errores calculados:', { errorArena, errorArcilla, errorLimo });
        
        // Calcular promedio (solo si hay al menos un valor válido)
        let suma = 0, cuenta = 0;
        [errorArena, errorArcilla, errorLimo].forEach(e => { 
            if (typeof e === 'number' && isFinite(e)) { 
                suma += e; 
                cuenta++; 
            } 
        });
        
        var promedio = cuenta > 0 ? (suma / cuenta) : '';
        console.log('Promedio calculado:', promedio, 'Cuenta:', cuenta);
        
        // Actualizar el campo con el promedio
        const campo = $(`#material_referencia_error_promedio_${processIndex}`);
        if (campo.length > 0) {
            campo.val(promedio !== '' ? promedio.toFixed(2) + '%' : '');
            console.log('Campo actualizado con:', promedio !== '' ? promedio.toFixed(2) + '%' : '');
        } else {
            console.error('Campo no encontrado:', `#material_referencia_error_promedio_${processIndex}`);
        }

        // En la tabla de exactitud, agregar una columna para 'Aceptabilidad' junto al campo de %Error promedio
        const campoAceptabilidad = $(`#material_referencia_aceptabilidad_${processIndex}`);
        if (campoAceptabilidad.length > 0) {
            if (promedio !== '' && typeof promedio === 'number' && isFinite(promedio)) {
                campoAceptabilidad.val(promedio <= 20 ? 'Aceptable' : 'No aceptable');
            } else {
                campoAceptabilidad.val('');
            }
        }
    }

    // Función mejorada para obtener el processIndex de manera más robusta
    function getProcessIndexFromElement(element) {
        // Primero intentar obtener desde el atributo name
        const name = element.attr('name');
        if (name) {
            const match = name.match(/analyses\[(\d+)\]/);
            if (match) {
                return match[1];
            }
        }
        
        // Buscar en la tabla más cercana
        const table = element.closest('[id^="muestras_table_"]');
        if (table.length > 0) {
            const tableId = table.attr('id');
            return tableId.replace('muestras_table_', '');
        }
        
        // Buscar en el tab-pane más cercano
        const tabPane = element.closest('.tab-pane[id$="controls"]');
        if (tabPane.length > 0) {
            const tabId = tabPane.attr('id');
            return tabId.replace('controls', '').replace('-', '');
        }
        
        // Como último recurso, buscar en cualquier elemento con data-process-index
        const processElement = element.closest('[data-process-index]');
        if (processElement.length > 0) {
            return processElement.data('process-index');
        }
        
        return null;
    }

    // Event listener corregido para los campos de exactitud
    $(document).on('input change', 
        "input[name*='material_referencia_obtenido'], input[name*='material_referencia_esperado']", 
        function() {
            const processIndex = getProcessIndexFromElement($(this));
            console.log('Evento disparado, processIndex encontrado:', processIndex);
            
            if (processIndex !== null) {
                // Usar setTimeout para asegurar que el valor se haya actualizado
                setTimeout(() => {
                    calcularErrorExactitudPromedio(processIndex);
                }, 100);
            } else {
                console.error('No se pudo determinar el processIndex para el elemento:', this);
            }
        }
    );

    // Inicialización al cargar la página
    $(document).ready(function() {
        // Ejecutar para cada proceso definido
        @foreach($processes as $index => $process)
            setTimeout(() => {
                calcularErrorExactitudPromedio({{ $index }});
            }, 500); // Dar tiempo para que se inicialicen todos los elementos
        @endforeach
        
        // También ejecutar cuando cambien los valores por defecto
        setTimeout(() => {
            $("input[name*='material_referencia_esperado']").trigger('change');
        }, 1000);
    });

    // Función adicional para debug - puedes llamarla desde la consola del navegador
    function debugExactitud(processIndex) {
        console.log('=== DEBUG EXACTITUD PROCESO', processIndex, '===');
        
        const inputs = [
            'material_referencia_obtenido_arena',
            'material_referencia_obtenido_arcilla', 
            'material_referencia_obtenido_limo',
            'material_referencia_esperado_arena',
            'material_referencia_esperado_arcilla',
            'material_referencia_esperado_limo'
        ];
        
        inputs.forEach(inputName => {
            const selector = `input[name='analyses[${processIndex}][${inputName}]']`;
            const element = $(selector);
            console.log(`${inputName}:`, {
                selector: selector,
                exists: element.length > 0,
                value: element.val(),
                parsedValue: parseFloat(element.val()) || 0
            });
        });
        
        const outputSelector = `#material_referencia_error_promedio_${processIndex}`;
        const outputElement = $(outputSelector);
        console.log('Campo de salida:', {
            selector: outputSelector,
            exists: outputElement.length > 0,
            currentValue: outputElement.val()
        });
        
        console.log('=== FIN DEBUG ===');
    }

    $(document).ready(function() {
        function syncGeneralToProcesses(field) {
            const value = $('#' + field).val();
            $('[id^="' + field + '_"]').each(function() {
                $(this).val(value);
            });
        }
        // Lista de campos generales
        const fields = [
            'consecutivo_no',
            'fecha_analisis',
            'nombre_analista',
            'metodologia_utilizada',
            'codigo_termometro',
            'codigo_hidrometro'
        ];
        // Agregar listeners a los campos generales
        fields.forEach(function(field) {
            $('#' + field).on('input change', function() {
                syncGeneralToProcesses(field);
            });
        });
        // Al cargar la página, sincronizar los valores generales a los procesos
        fields.forEach(function(field) {
            syncGeneralToProcesses(field);
        });
    });

    $(document).ready(function() {
        // Antes de enviar el formulario, copiar los valores generales a cada proceso
        $('#textureBatchForm').on('submit', function(e) {
            // Si es un análisis rechazado, mostrar confirmación
            @if(isset($analysis))
            if (!confirm('¿Está seguro de que desea actualizar este análisis rechazado? El análisis será enviado nuevamente para revisión.')) {
                e.preventDefault();
                return false;
            }
            @endif
            
            const fields = [
                'consecutivo_no',
                'fecha_analisis',
                'nombre_analista',
                'metodologia_utilizada',
                'codigo_termometro',
                'codigo_hidrometro'
            ];
            fields.forEach(function(field) {
                const value = $('#' + field).val();
                $('[name^="analyses"][name$="['+field+']"]').each(function() {
                    $(this).val(value);
                });
            });
        });
    });
</script>
@endpush
