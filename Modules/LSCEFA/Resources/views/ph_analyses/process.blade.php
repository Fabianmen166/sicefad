@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de pH')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de pH</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.ph_analysis.index') }}">Gestión de pH</a></li>
                        <li class="breadcrumb-item active">Procesar Análisis</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin-bottom: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                // Si no existe $pendingAnalyses pero sí $analysis, lo convertimos en colección para reutilizar el flujo
                if (!isset($pendingAnalyses) && isset($analysis)) {
                    $pendingAnalyses = collect([$analysis]);
                }
            @endphp

            @if ($pendingAnalyses->isEmpty())
                <div class="alert alert-danger">
                    Error: No hay análisis de pH pendientes para procesar.
                </div>
                <a href="{{ route('lscefa.ph_analysis.index') }}" class="btn btn-secondary">Regresar</a>
            @else
                <!-- Usamos el primer análisis para las secciones generales -->
                @php
                    $firstAnalysis = $pendingAnalyses->first();
                @endphp

                @php /* Prefill coincide con Conductividad: old() y variable del controlador */ @endphp

                <form action="{{ route('lscefa.ph_analysis.store') }}" method="POST" id="phAnalysisForm" autocomplete="off">
                    @csrf

                    <!-- Hidden Input for Analysis IDs -->
                    @foreach ($pendingAnalyses as $analysis)
                        <input type="hidden" name="analyses[{{$analysis->id}}][analysis_id]" value="{{$analysis->id}}">
                    @endforeach

                    <!-- Sección: Información General -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información General</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="process_id">Procesos Involucrados</label>
                                        <input type="text" class="form-control" id="process_id" value="{{ $pendingAnalyses->pluck('process.item_code')->unique()->filter()->implode(', ') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="service">Servicios Involucrados</label>
                                        <input type="text" class="form-control" id="service" value="{{ $pendingAnalyses->pluck('service.descripcion')->unique()->implode(', ') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="consecutivo">Consecutivo No.</label>
                                        <input type="text" class="form-control @error('consecutivo_no') is-invalid @enderror" id="consecutivo" name="consecutivo_no" value="{{ request()->query('consecutivo', ((old('consecutivo_no') !== null && old('consecutivo_no') !== '') ? old('consecutivo_no') : ($consecutivo_no ?? ''))) }}" data-query-consecutivo="{{ request()->query('consecutivo','') }}" required>
                                        <script>
                                          (function(){
                                            var input = document.getElementById('consecutivo');
                                            if (!input) return;
                                            var fromAttr = input.getAttribute('data-query-consecutivo') || '';
                                            // Si el valor sigue vacío, y tenemos consecutivo por query, setearlo
                                            if ((input.value === '' || input.value.trim() === '') && fromAttr) {
                                                input.value = fromAttr;
                                            }
                                          })();
                                        </script>
                                        @error('consecutivo_no')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_analisis">Fecha del Análisis</label>
                                        <input type="date" class="form-control @error('fecha_analisis') is-invalid @enderror" id="fecha_analisis" name="fecha_analisis" value="{{ old('fecha_analisis', $firstAnalysis->phAnalysis->fecha_analisis ?? now()->format('Y-m-d')) }}" required>
                                        @error('fecha_analisis')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="analista">Analista</label>
                                        <input type="text" class="form-control" id="analista" value="{{ $user ? $user->nickname : '' }}" readonly>
                                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Detalles del Equipo -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detalles del Equipo</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo_probeta">Código de la Probeta</label>
                                        <input type="text" class="form-control @error('codigo_probeta') is-invalid @enderror" id="codigo_probeta" name="codigo_probeta" value="{{ old('codigo_probeta', $firstAnalysis->phAnalysis->codigo_probeta ?? '') }}" required>
                                        @error('codigo_probeta')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo_equipo">Código del Equipo Potenciométrico</label>
                                        <input type="text" class="form-control @error('codigo_equipo') is-invalid @enderror" id="codigo_equipo" name="codigo_equipo" value="{{ old('codigo_equipo', $firstAnalysis->phAnalysis->codigo_equipo ?? '') }}" required>
                                        @error('codigo_equipo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="serial_electrodo">Serial del Electrodo</label>
                                        <input type="text" class="form-control @error('serial_electrodo') is-invalid @enderror" id="serial_electrodo" name="serial_electrodo" value="{{ old('serial_electrodo', $firstAnalysis->phAnalysis->serial_electrodo ?? '') }}" required>
                                        @error('serial_electrodo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="serial_sonda">Serial de la Sonda de Temperatura</label>
                                        <input type="text" class="form-control @error('serial_sonda_temperatura') is-invalid @enderror" id="serial_sonda" name="serial_sonda_temperatura" value="{{ old('serial_sonda_temperatura', $firstAnalysis->phAnalysis->serial_sonda_temperatura ?? '') }}" required>
                                        @error('serial_sonda_temperatura')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Controles Analíticos -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Controles Analíticos</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered" id="controles_analiticos">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Lote</th>
                                        <th>Valor Leído (pH)</th>
                                        <th>Valor Esperado (pH)</th>
                                        <th>% Error</th>
                                        <th>Aceptabilidad del control <span title="≤0.15 si pH≤7.00, ≤0.20 si 7.00<pH<7.5, ≤0.30 si 7.5≤pH≤8.00, ≤0.40 si pH>8.00">[?]</span></th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Mostrar solo los 3 controles de buffer: pH 4, 7, 10
                                        $identificaciones = ['Buffer de pH 4', 'Buffer de pH 7', 'Buffer de pH 10'];
                                        $controlCount = 3;
                                    @endphp
                                    @for ($index = 0; $index < $controlCount; $index++)
                                        @php
                                            $identificacion = $identificaciones[$index];
                                        @endphp
                                        <tr class="control-row">
                                            <td>
                                                <input type="text" class="form-control control-identificacion" name="controles_analiticos[{{$index}}][identificacion]" value="{{ $identificacion }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control control-lote" name="controles_analiticos[{{$index}}][lote]" value="{{ old('controles_analiticos.' . $index . '.lote', $firstAnalysis->phAnalysis->controles_analiticos[$index]['lote'] ?? '') }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" class="form-control control-valor-leido" name="controles_analiticos[{{$index}}][valor_leido]" value="{{ old('controles_analiticos.' . $index . '.valor_leido', $firstAnalysis->phAnalysis->controles_analiticos[$index]['valor_leido'] ?? '') }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" class="form-control control-valor-esperado" name="controles_analiticos[{{$index}}][valor_esperado]" value="{{ old('controles_analiticos.' . $index . '.valor_esperado', $firstAnalysis->phAnalysis->controles_analiticos[$index]['valor_esperado'] ?? '') }}">
                                            </td>
                                            <td class="control-error"></td>
                                            <td class="control-aceptabilidad"></td>
                                            <td>
                                                <input type="text" class="form-control" name="controles_analiticos[{{$index}}][observaciones]" value="{{ old('controles_analiticos.' . $index . '.observaciones', $firstAnalysis->phAnalysis->controles_analiticos[$index]['observaciones'] ?? '') }}">
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                            <div id="controles-error" class="text-danger" style="display: none;">Debe completar al menos un control analítico y todos los completados deben ser aceptables.</div>
                        </div>
                    </div>

                    <!-- Sección: Veracidad -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Veracidad</h3>
                        </div>
                        <div class="card-body">
                            <!-- Tabla de Muestra de Referencia -->
                            <h5>Muestra de Referencia</h5>
                            <table class="table table-bordered mb-4">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Lote</th>
                                        <th>Peso (g)</th>
                                        <th>Volumen H₂O (mL)</th>
                                        <th>Temperatura (°C)</th>
                                        <th>Valor Leído (pH)</th>
                                        <th>Valor Esperado (pH)</th>
                                        <th>% Error</th>
                                        <th>Aceptabilidad</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Obtener los datos de la muestra de referencia si existen
                                        $indiceMuestraReferencia = 3; // Índice de la muestra de referencia en controles_analiticos
                                        $muestraRef = $firstAnalysis->phAnalysis->controles_analiticos[$indiceMuestraReferencia] ?? [];
                                    @endphp
                                    <tr class="control-row">
                                        <td>
                                            <input type="text" class="form-control" name="muestra_referencia[identificacion]" value="Muestra de referencia o MRC" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="muestra_referencia[lote]" value="{{ old('muestra_referencia.lote', $muestraRef['lote'] ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" class="form-control" name="muestra_referencia[peso]" value="{{ old('muestra_referencia.peso', $muestraRef['peso'] ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" class="form-control" name="muestra_referencia[volumen_agua]" value="{{ old('muestra_referencia.volumen_agua', $muestraRef['volumen_agua'] ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" class="form-control" name="muestra_referencia[temperatura]" value="{{ old('muestra_referencia.temperatura', $muestraRef['temperatura'] ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" class="form-control control-valor-leido" name="muestra_referencia[valor_leido]" value="{{ old('muestra_referencia.valor_leido', $muestraRef['valor_leido'] ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" class="form-control control-valor-esperado" name="muestra_referencia[valor_esperado]" value="{{ old('muestra_referencia.valor_esperado', $muestraRef['valor_esperado'] ?? '') }}">
                                        </td>
                                        <td class="control-error"></td>
                                        <td class="control-aceptabilidad"></td>
                                        <td>
                                            <input type="text" class="form-control" name="muestra_referencia[observaciones]" value="{{ old('muestra_referencia.observaciones', $muestraRef['observaciones'] ?? '') }}">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Tabla de Duplicados -->
                            <h5>Precisión</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td colspan="9">
                                        <div class="form-group mb-0">
                                            <label for="identificacion_duplicados" class="mb-0">Identificación de la Muestra (Aplicable a ambas réplicas)</label>
                                            <input type="text" 
                                                   class="form-control @error('precision_analitica.identificacion') is-invalid @enderror" 
                                                   id="identificacion_duplicados" 
                                                   name="precision_analitica[identificacion]" 
                                                   value="{{ old('precision_analitica.identificacion', $firstAnalysis->phAnalysis->precision_analitica['identificacion'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.identificacion')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Réplica</th>
                                    <th>Peso (g)</th>
                                    <th>Volumen H₂O (mL)</th>
                                    <th>Temperatura (°C)</th>
                                    <th>Valor Leído (pH)</th>
                                    <th>Promedio</th>
                                    <th>Diferencia</th>
                                    <th>Aceptabilidad</th>
                                    <th>Observaciones</th>
                                </tr>
                                <tbody>
                                    <tr>
                                        <td><strong>A</strong></td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                   class="form-control @error('precision_analitica.duplicado_a.peso') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_a][peso]" 
                                                   value="{{ old('precision_analitica.duplicado_a.peso', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['peso'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_a.peso')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                   class="form-control @error('precision_analitica.duplicado_a.volumen_agua') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_a][volumen_agua]" 
                                                   value="{{ old('precision_analitica.duplicado_a.volumen_agua', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['volumen_agua'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_a.volumen_agua')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                   class="form-control @error('precision_analitica.duplicado_a.temperatura') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_a][temperatura]" 
                                                   value="{{ old('precision_analitica.duplicado_a.temperatura', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['temperatura'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_a.temperatura')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.00001" pattern="^\d+(\.\d{1,5})?$" 
                                                   class="form-control duplicado-a @error('precision_analitica.duplicado_a.valor_leido') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_a][valor_leido]" 
                                                   value="{{ old('precision_analitica.duplicado_a.valor_leido', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['valor_leido'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_a.valor_leido')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td rowspan="2"><span class="form-control-plaintext" id="promedio"></span></td>
                                        <td rowspan="2"><span class="form-control-plaintext" id="diferencia"></span></td>
                                        <td rowspan="2"><span class="form-control-plaintext" id="aceptabilidad_precision"></span></td>
                                        <td>
                                            <input type="text" class="form-control @error('precision_analitica.duplicado_a.observaciones') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_a][observaciones]" 
                                                   value="{{ old('precision_analitica.duplicado_a.observaciones', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['observaciones'] ?? '') }}">
                                            @error('precision_analitica.duplicado_a.observaciones')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>B</strong></td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                   class="form-control @error('precision_analitica.duplicado_b.peso') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_b][peso]" 
                                                   value="{{ old('precision_analitica.duplicado_b.peso', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['peso'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_b.peso')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                   class="form-control @error('precision_analitica.duplicado_b.volumen_agua') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_b][volumen_agua]" 
                                                   value="{{ old('precision_analitica.duplicado_b.volumen_agua', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['volumen_agua'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_b.volumen_agua')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                   class="form-control @error('precision_analitica.duplicado_b.temperatura') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_b][temperatura]" 
                                                   value="{{ old('precision_analitica.duplicado_b.temperatura', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['temperatura'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_b.temperatura')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.00001" pattern="^\d+(\.\d{1,5})?$" 
                                                   class="form-control duplicado-b @error('precision_analitica.duplicado_b.valor_leido') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_b][valor_leido]" 
                                                   value="{{ old('precision_analitica.duplicado_b.valor_leido', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['valor_leido'] ?? '') }}" 
                                                   required>
                                            @error('precision_analitica.duplicado_b.valor_leido')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text" class="form-control @error('precision_analitica.duplicado_b.observaciones') is-invalid @enderror" 
                                                   name="precision_analitica[duplicado_b][observaciones]" 
                                                   value="{{ old('precision_analitica.duplicado_b.observaciones', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['observaciones'] ?? '') }}">
                                            @error('precision_analitica.duplicado_b.observaciones')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sección: Ítems de Ensayo (Unificada) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ítems de Ensayo Pendientes</h3>
                        </div>
                        <div class="card-body">
                            @if (empty($pendingItems))
                                <p>No hay ítems pendientes para procesar.</p>
                            @else
                                <table class="table table-bordered" id="items_ensayo">
                                    <thead>
                                        <tr>
                                            <th>Código de Ítem</th>
                                            <th>Peso (g)</th>
                                            <th>Volumen H₂O (mL)</th>
                                            <th>Temperatura (°C)</th>
                                            <th>Valor Leído (pH)</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $itemIndex = 0;
                                            $selectedAnalysisIds = $pendingAnalyses->pluck('id')->toArray();
                                        @endphp
                                        @foreach ($pendingItems as $index => $item)
                                            @if (in_array($item['analysis_id'], $selectedAnalysisIds))
                                                @php
                                                    $analysis = $pendingAnalyses->firstWhere('id', $item['analysis_id']);
                                                @endphp
                                                @if ($analysis)
                                                    <tr class="item-row">
                                                        <td>
                                                            {{ $analysis->process->item_code }}
                                                            <input type="hidden" name="items_ensayo[{{$index}}][item_id]" value="{{ $item['id'] ?? '' }}">
                                                            <input type="hidden" name="items_ensayo[{{$index}}][identificacion]" value="{{ $analysis->process->item_code }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                                   class="form-control @error('items_ensayo.' . $index . '.peso') is-invalid @enderror" 
                                                                   name="items_ensayo[{{$index}}][peso]" 
                                                                   value="{{ old('items_ensayo.' . $index . '.peso', $item['peso'] ?? '') }}" 
                                                                   required>
                                                            @error('items_ensayo.' . $index . '.peso')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.0001" pattern="^\d+(\.\d{1,4})?$" 
                                                                   class="form-control @error('items_ensayo.' . $index . '.volumen_agua') is-invalid @enderror" 
                                                                   name="items_ensayo[{{$index}}][volumen_agua]" 
                                                                   value="{{ old('items_ensayo.' . $index . '.volumen_agua', $item['volumen_agua'] ?? '') }}" 
                                                                   required>
                                                            @error('items_ensayo.' . $index . '.volumen_agua')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.1" 
                                                                   class="form-control @error('items_ensayo.' . $index . '.temperatura') is-invalid @enderror" 
                                                                   name="items_ensayo[{{$index}}][temperatura]" 
                                                                   value="{{ old('items_ensayo.' . $index . '.temperatura', $item['temperatura'] ?? '') }}" 
                                                                   required>
                                                            @error('items_ensayo.' . $index . '.temperatura')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.00001" pattern="^\d+(\.\d{1,5})?$" 
                                                                   class="form-control @error('items_ensayo.' . $index . '.valor_leido') is-invalid @enderror" 
                                                                   name="items_ensayo[{{$index}}][valor_leido]" 
                                                                   value="{{ old('items_ensayo.' . $index . '.valor_leido', $item['valor_ph'] ?? '') }}" 
                                                                   required>
                                                            @error('items_ensayo.' . $index . '.valor_leido')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="text" 
                                                                   class="form-control @error('items_ensayo.' . $index . '.observaciones') is-invalid @enderror" 
                                                                   name="items_ensayo[{{$index}}][observaciones]" 
                                                                   value="{{ old('items_ensayo.' . $index . '.observaciones', $item['observaciones'] ?? '') }}">
                                                            @error('items_ensayo.' . $index . '.observaciones')
                                                                <span class="invalid-feedback">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <input type="hidden" name="items_ensayo[{{$index}}][analysis_id]" value="{{ $item['analysis_id'] }}">
                                                    </tr>
                                                    @php
                                                        $itemIndex++;
                                                    @endphp
                                                @endif
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-primary" id="add-item" style="display: none;">Agregar Ítem</button>
                            @endif
                        </div>
                    </div>

                    <!-- Sección: Observaciones -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Observaciones</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" name="observaciones" rows="3">{{ old('observaciones', $firstAnalysis->phAnalysis->observaciones ?? '') }}</textarea>
                                @error('observaciones')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Guardar Análisis de pH</button>
                    <a href="{{ route('lscefa.ph_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            @endif
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function actualizarAceptabilidadControles() {
        document.querySelectorAll('.control-row').forEach(function(row) {
            const valorLeidoInput = row.querySelector('.control-valor-leido');
            const valorEsperadoInput = row.querySelector('.control-valor-esperado');
            const errorCell = row.querySelector('.control-error');
            const aceptabilidadCell = row.querySelector('.control-aceptabilidad');
            let valorLeido = parseFloat(valorLeidoInput.value);
            let valorEsperado = parseFloat(valorEsperadoInput.value);
            if (!isNaN(valorLeido) && !isNaN(valorEsperado) && valorEsperado !== 0) {
                let error = Math.abs((valorLeido - valorEsperado) / valorEsperado) * 100;
                error = Math.round(error * 100) / 100; // 2 decimales
                errorCell.textContent = error + '%';
                if (error >= 0 && error <= 20) {
                    aceptabilidadCell.textContent = 'Aceptable';
                    aceptabilidadCell.classList.remove('text-danger');
                    aceptabilidadCell.classList.add('text-success');
                } else {
                    aceptabilidadCell.textContent = 'No aceptable';
                    aceptabilidadCell.classList.remove('text-success');
                    aceptabilidadCell.classList.add('text-danger');
                }
            } else {
                errorCell.textContent = '';
                aceptabilidadCell.textContent = '';
                aceptabilidadCell.classList.remove('text-success', 'text-danger');
            }
        });
    }
    document.querySelectorAll('.control-valor-leido, .control-valor-esperado').forEach(function(input) {
        input.addEventListener('input', actualizarAceptabilidadControles);
    });
    actualizarAceptabilidadControles(); // Inicial

    function actualizarPrecisionAnalitica() {
        const duplicadoAInput = document.querySelector('.duplicado-a');
        const duplicadoBInput = document.querySelector('.duplicado-b');
        const promedioSpan = document.getElementById('promedio');
        const diferenciaSpan = document.getElementById('diferencia');
        const aceptabilidadSpan = document.getElementById('aceptabilidad_precision');
        let duplicadoA = parseFloat(duplicadoAInput.value);
        let duplicadoB = parseFloat(duplicadoBInput.value);
        if (!isNaN(duplicadoA) && !isNaN(duplicadoB)) {
            let promedio = (duplicadoA + duplicadoB) / 2;
            let diferencia = Math.abs(duplicadoA - duplicadoB);
            promedioSpan.textContent = promedio.toFixed(4);
            diferenciaSpan.textContent = diferencia.toFixed(4);
            let limite = 0.15;
            if (promedio > 7.00 && promedio < 7.5) {
                limite = 0.20;
            } else if (promedio >= 7.5 && promedio <= 8.00) {
                limite = 0.30;
            } else if (promedio > 8.00) {
                limite = 0.40;
            }
            if (diferencia <= limite) {
                aceptabilidadSpan.textContent = 'Aceptable';
                aceptabilidadSpan.classList.remove('text-danger');
                aceptabilidadSpan.classList.add('text-success');
            } else {
                aceptabilidadSpan.textContent = 'No aceptable';
                aceptabilidadSpan.classList.remove('text-success');
                aceptabilidadSpan.classList.add('text-danger');
            }
        } else {
            promedioSpan.textContent = '';
            diferenciaSpan.textContent = '';
            aceptabilidadSpan.textContent = '';
            aceptabilidadSpan.classList.remove('text-success', 'text-danger');
        }
    }
    document.querySelectorAll('.duplicado-a, .duplicado-b').forEach(function(input) {
        input.addEventListener('input', actualizarPrecisionAnalitica);
    });
    actualizarPrecisionAnalitica(); // Inicial
});
</script>
@endsection

@push('scripts')
<script>
  (function() {
    try {
      var params = new URLSearchParams(window.location.search || '');
      var c = params.get('consecutivo');
      var input = document.getElementById('consecutivo');
      if (input && (!input.value || input.value.trim() === '') && c) {
        input.value = c;
      }
    } catch (e) {
      // noop
    }
  })();
</script>
<script>
(function() {
    const form = document.getElementById('phAnalysisForm');
    if (!form) return;
    const STORAGE_KEY = 'ph_analysis_form';

    // Guardar datos al escribir
    form.addEventListener('input', function() {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    });

    // Recuperar datos al cargar la página
    window.addEventListener('DOMContentLoaded', function() {
        const saved = localStorage.getItem(STORAGE_KEY);
        const params = new URLSearchParams(window.location.search || '');
        const queryConsecutivo = params.get('consecutivo');
        if (saved) {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (!field) return;
                // No sobrescribir el consecutivo si viene en la URL
                if (key === 'consecutivo_no' && queryConsecutivo) return;
                // No sobrescribir con vacío si el campo ya tiene valor
                if ((data[key] === '' || data[key] === null) && field.value) return;
                field.value = data[key];
            });
        }
    });

    // Limpiar datos al enviar el formulario
    form.addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
})();
</script>
@endpush