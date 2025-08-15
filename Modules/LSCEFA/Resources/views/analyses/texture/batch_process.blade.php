@extends('lscefa::layouts.technical')

@section('title', 'Procesamiento por Lotes - Análisis de Textura')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesamiento por Lotes - Análisis de Textura</h1>
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

            <form action="{{ route('lscefa.technical.analyses.texture.batch_store') }}" method="POST" id="textureBatchForm">
                @csrf
                
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
                        
                        <!-- Barra de Navegación Horizontal -->
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

                        <!-- Contenido de las Pestañas -->
                        <div class="tab-content" id="analysisTabsContent">
                            <!-- Pestaña Información General -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <!-- Información General del Análisis -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Información General del Análisis - Proceso {{ $process->process_id }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="consecutivo_no_{{ $index }}">Consecutivo No. *</label>
                                                            <input type="text" class="form-control" id="consecutivo_no_{{ $index }}" name="analyses[{{ $index }}][consecutivo_no]" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="fecha_analisis_{{ $index }}">Fecha del análisis *</label>
                                                            <input type="date" class="form-control" id="fecha_analisis_{{ $index }}" name="analyses[{{ $index }}][fecha_analisis]" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="nombre_analista_{{ $index }}">Nombre del Analista *</label>
                                                            <input type="text" class="form-control" id="nombre_analista_{{ $index }}" name="analyses[{{ $index }}][nombre_analista]" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="metodologia_utilizada_{{ $index }}">Metodología Utilizada *</label>
                                                            <input type="text" class="form-control" id="metodologia_utilizada_{{ $index }}" name="analyses[{{ $index }}][metodologia_utilizada]" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="codigo_termometro_{{ $index }}">Código Interno termómetro</label>
                                                            <input type="text" class="form-control" id="codigo_termometro_{{ $index }}" name="analyses[{{ $index }}][codigo_termometro]">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="codigo_hidrometro_{{ $index }}">Código interno Hidrómetro</label>
                                                            <input type="text" class="form-control" id="codigo_hidrometro_{{ $index }}" name="analyses[{{ $index }}][codigo_hidrometro]">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Controles Analíticos -->
                            <div class="tab-pane fade" id="controls" role="tabpanel" aria-labelledby="controls-tab">
                                <!-- Controles Analíticos -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Controles Analíticos - Proceso {{ $process->process_id }}</h3>
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
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_promedio_arena]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_promedio_arcilla]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_promedio_limo]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_dpr_arena]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_dpr_arcilla]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_a_dpr_limo]"></td>
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
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_promedio_arena]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_promedio_arcilla]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_promedio_limo]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_dpr_arena]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_dpr_arcilla]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][duplicado_b_dpr_limo]"></td>
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
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_error_arena]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_error_arcilla]"></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_error_limo]"></td>
                                                                        <td>
                                                                            <select class="form-control form-control-sm" name="analyses[{{ $index }}][material_referencia_aceptabilidad]">
                                                                                <option value="">Seleccionar</option>
                                                                                <option value="Aceptable">Aceptable</option>
                                                                                <option value="No aceptable">No aceptable</option>
                                                                            </select>
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
                                                <h3 class="card-title">Análisis de Muestras - Proceso {{ $process->process_id }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm" id="muestras_table_{{ $index }}">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th rowspan="2">Código Interno</th>
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
                                                            <tr class="muestra-row">
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][codigo_interno]" placeholder="Código">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[{{ $index }}][items][0][peso]" placeholder="0.0000">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][lecturas_40s]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.1" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][temperatura_40s]" placeholder="0.0">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][lecturas_2h]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.1" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][temperatura_2h]" placeholder="0.0">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][lecturas_corregidas_40s]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][lecturas_corregidas_2h]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][humedad]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[{{ $index }}][items][0][porcentaje_arena]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[{{ $index }}][items][0][porcentaje_arcilla]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[{{ $index }}][items][0][porcentaje_limo]" placeholder="0.00">
                                                                </td>
                                                                <td>
                                                                    <select class="form-control form-control-sm clase-textural" name="analyses[{{ $index }}][items][0][clase_textural]">
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
                                                                <td>
                                                                    <textarea class="form-control form-control-sm" name="analyses[{{ $index }}][items][0][observaciones]" rows="2" placeholder="Observaciones"></textarea>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-danger btn-sm remove-muestra" title="Eliminar muestra">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                
                                                <!-- Barra de Navegación Horizontal para la Tabla -->
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <div class="card">
                                                            <div class="card-body p-0">
                                                                <ul class="nav nav-pills nav-fill" id="tableNavigation_{{ $index }}" role="tablist">
                                                                    <li class="nav-item" role="presentation">
                                                                        <a class="nav-link active" id="data-tab-{{ $index }}" data-toggle="pill" href="#data-content-{{ $index }}" role="tab" aria-controls="data-content-{{ $index }}" aria-selected="true">
                                                                            <i class="fas fa-table mr-2"></i>Datos de Muestras
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item" role="presentation">
                                                                        <a class="nav-link" id="calculations-tab-{{ $index }}" data-toggle="pill" href="#calculations-content-{{ $index }}" role="tab" aria-controls="calculations-content-{{ $index }}" aria-selected="false">
                                                                            <i class="fas fa-calculator mr-2"></i>Cálculos
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item" role="presentation">
                                                                        <a class="nav-link" id="validation-tab-{{ $index }}" data-toggle="pill" href="#validation-content-{{ $index }}" role="tab" aria-controls="validation-content-{{ $index }}" aria-selected="false">
                                                                            <i class="fas fa-check-circle mr-2"></i>Validación
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item" role="presentation">
                                                                        <a class="nav-link" id="export-tab-{{ $index }}" data-toggle="pill" href="#export-content-{{ $index }}" role="tab" aria-controls="export-content-{{ $index }}" aria-selected="false">
                                                                            <i class="fas fa-download mr-2"></i>Exportar
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
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
                                                                    <span class="badge badge-primary" id="totalMuestras_{{ $index }}">1</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 text-right">
                                                                <button type="button" class="btn btn-secondary" id="addMuestraBtn_{{ $index }}" data-process-index="{{ $index }}">
                                                                    <i class="fas fa-plus"></i> Agregar Muestra
                                                                </button>
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
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Todos los Análisis
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
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
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
    $(document).ready(function() {
        // Set default date to today for all date fields
        $('input[type="date"]').val(new Date().toISOString().split('T')[0]);
        
        // Initialize variables for each process
        let controlIndexes = {};
        let muestraIndexes = {};
        
        // Initialize indexes for each process
        @foreach($processes as $index => $process)
            controlIndexes[{{ $index }}] = 1;
            muestraIndexes[{{ $index }}] = 1;
        @endforeach
        
        // Add new muestra for specific process
        $(document).on('click', '[id^="addMuestraBtn_"]', function() {
            const processIndex = $(this).data('process-index');
            const muestraIndex = muestraIndexes[processIndex];
            
            const newMuestra = `
                <tr class="muestra-row">
                    <td>
                        <input type="text" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][codigo_interno]" placeholder="Código">
                    </td>
                    <td>
                        <input type="number" step="0.0001" class="form-control form-control-sm peso-muestra" name="analyses[${processIndex}][items][${muestraIndex}][peso]" placeholder="0.0000">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_40s]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.1" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][temperatura_40s]" placeholder="0.0">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_2h]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.1" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][temperatura_2h]" placeholder="0.0">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_corregidas_40s]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][lecturas_corregidas_2h]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][humedad]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm porcentaje-arena" name="analyses[${processIndex}][items][${muestraIndex}][porcentaje_arena]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm porcentaje-arcilla" name="analyses[${processIndex}][items][${muestraIndex}][porcentaje_arcilla]" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm porcentaje-limo" name="analyses[${processIndex}][items][${muestraIndex}][porcentaje_limo]" placeholder="0.00">
                    </td>
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
                    <td>
                        <textarea class="form-control form-control-sm" name="analyses[${processIndex}][items][${muestraIndex}][observaciones]" rows="2" placeholder="Observaciones"></textarea>
                    </td>
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
            if ($(`#muestras_container_${processIndex} tr`).length > 1) {
                $(this).closest('tr').remove();
                setTimeout(() => updateSampleCount(processIndex), 100);
            } else {
                alert('Debe mantener al menos una muestra.');
            }
        });
        
        // Tab navigation enhancement
        $('.nav-tabs .nav-link').on('click', function(e) {
            e.preventDefault();
            const target = $(this).attr('href');
            
            // Remove active class from all tabs and content
            $('.nav-tabs .nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            
            // Add active class to clicked tab
            $(this).addClass('active');
            $(target).addClass('show active');
            
            // Update progress indicator
            updateProgressIndicator();
        });
        
        // Progress indicator function
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
        
        // Initialize progress indicator
        updateProgressIndicator();
        
        // Table navigation functionality for each process
        @foreach($processes as $index => $process)
            $(`#tableNavigation_{{ $index }} .nav-link`).on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');
                
                // Remove active class from all tabs and content
                $(`#tableNavigation_{{ $index }} .nav-link`).removeClass('active');
                $(`#tableNavigationContent_{{ $index }} .tab-pane`).removeClass('show active');
                
                // Add active class to clicked tab
                $(this).addClass('active');
                $(target).addClass('show active');
            });
        @endforeach
        
        // Update sample count for specific process
        function updateSampleCount(processIndex) {
            const count = $(`#muestras_container_${processIndex} tr`).length;
            $(`#totalMuestras_${processIndex}`).text(count);
        }
        
        // Calculate averages for specific process
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
        
        // Validate all samples for specific process
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
        
        // Highlight errors for specific process
        @foreach($processes as $index => $process)
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
        @endforeach
        
        // Update counts and calculations when samples change
        $(document).on('input', '.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo', function() {
            const processIndex = $(this).closest('[id^="muestras_table_"]').attr('id').replace('muestras_table_', '');
            calculateAverages(processIndex);
        });
        
        // Initialize for each process
        @foreach($processes as $index => $process)
            updateSampleCount({{ $index }});
            calculateAverages({{ $index }});
        @endforeach
        
        // Calculate percentages when weights change
        $(document).on('input', '.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo', function() {
            const row = $(this).closest('.muestra-row');
            const porcentajeArena = parseFloat(row.find('.porcentaje-arena').val()) || 0;
            const porcentajeArcilla = parseFloat(row.find('.porcentaje-arcilla').val()) || 0;
            const porcentajeLimo = parseFloat(row.find('.porcentaje-limo').val()) || 0;
            
            const total = porcentajeArena + porcentajeArcilla + porcentajeLimo;
            
            // Validate that total is approximately 100%
            if (total > 100.1 || total < 99.9) {
                row.find('.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo').addClass('is-invalid');
            } else {
                row.find('.porcentaje-arena, .porcentaje-arcilla, .porcentaje-limo').removeClass('is-invalid');
                
                // Auto-determine textural class
                const claseTextural = determinarClaseTextural(porcentajeArena, porcentajeArcilla, porcentajeLimo);
                row.find('.clase-textural').val(claseTextural);
            }
        });
        
        // Function to determine textural class based on percentages
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
        
        // Form validation
        $('#textureBatchForm').on('submit', function(e) {
            var isValid = true;
            
            // Check if all required fields are filled
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
            
            // Show loading state
            $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
        });
    });
</script>
@endpush
