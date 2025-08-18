@extends('lscefa::layouts.master')

@section('title', 'Revisión de Análisis de pH')

@push('styles')
<style>
    .form-control[readonly] {
        background-color: #f8f9fa !important;
        border: 1px solid #ced4da;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .bg-light-gray {
        background-color: #f8f9fa;
    }
    .card-header h3 {
        margin-bottom: 0;
        font-size: 1.2rem;
    }
    .info-badge {
        font-size: 0.9rem;
        padding: 0.4em 0.8em;
    }
    .verification-item {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
    }
    .value-display {
        padding: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.25rem;
        min-height: 38px;
    }
    .badge-status {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .card {
        margin-bottom: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 0.75rem 1.25rem;
    }
    .card-body {
        padding: 1.25rem;
    }
    .table {
        margin-bottom: 1rem;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Encabezado con información principal -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Análisis de pH</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.quality.reviews.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Revisión de Análisis</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            @include('lscefa::partials.alerts')

            @php
                // Normalización de datos de entrada para evitar errores si vienen nulos o como JSON
                $analysis = $analysis ?? null;
                if ($analysis) {
                    // Convertir posibles JSON a array
                    $analysis->controles_analiticos = is_string($analysis->controles_analiticos ?? null)
                        ? json_decode($analysis->controles_analiticos, true)
                        : ($analysis->controles_analiticos ?? []);
                    $analysis->precision_analitica = is_string($analysis->precision_analitica ?? null)
                        ? json_decode($analysis->precision_analitica, true)
                        : ($analysis->precision_analitica ?? []);
                    $analysis->items_ensayo = is_string($analysis->items_ensayo ?? null)
                        ? json_decode($analysis->items_ensayo, true)
                        : ($analysis->items_ensayo ?? []);
                }

                $controles_analiticos = $controles_analiticos ?? ($analysis->controles_analiticos ?? []);
                $precision_analitica = $precision_analitica ?? ($analysis->precision_analitica ?? []);
                $items_ensayo = $items_ensayo ?? ($analysis->items_ensayo ?? []);
                $process = $process ?? ($analysis->process ?? null);
                $customer = $customer ?? ($process->customer ?? null);
            @endphp

            <!-- Información General -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Número de Consecutivo</label>
                                <div class="value-display">{{ $analysis->consecutivo_no ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Fecha de Análisis</label>
                                <div class="value-display">{{ $analysis->fecha_analisis ? \Carbon\Carbon::parse($analysis->fecha_analisis)->format('d/m/Y') : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Técnico Responsable</label>
                                <div class="value-display">{{ $technicianName ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- Detalles del Equipo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Equipo</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Código de la Probeta</label>
                                <div class="value-display">{{ $analysis->codigo_probeta ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Código del Equipo Potenciométrico</label>
                                <div class="value-display">{{ $analysis->codigo_equipo ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Serial del Electrodo</label>
                                <div class="value-display">{{ $analysis->serial_electrodo ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Serial de la Sonda de Temperatura</label>
                                <div class="value-display">{{ $analysis->serial_sonda_temperatura ?? 'N/A' }}</div>
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
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Identificación</th>
                                    <th>Lote</th>
                                    <th>Valor Leído (pH)</th>
                                    <th>Valor Esperado (pH)</th>
                                    <th>Error</th>
                                    <th>Aceptabilidad</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(empty($controles_analiticos))
                                    <tr>
                                        <td colspan="7" class="text-center">No hay controles analíticos registrados</td>
                                    </tr>
                                @else
                                    @foreach($controles_analiticos as $control)
                                        @php
                                            $control = is_array($control) ? $control : (array)$control;
                                            $identificacion = $control['identificacion'] ?? 'N/A';
                                            $lote = $control['lote'] ?? 'N/A';
                                            $valor_leido = $control['valor_leido'] ?? 'N/A';
                                            $valor_esperado = $control['valor_esperado'] ?? 'N/A';
                                            $error = (isset($control['valor_leido'], $control['valor_esperado']) && is_numeric($control['valor_leido']) && is_numeric($control['valor_esperado']) && $control['valor_esperado'] != 0)
                                                ? round((($control['valor_leido'] - $control['valor_esperado']) / $control['valor_esperado']) * 100, 2)
                                                : 'N/A';
                                            // Soportar tanto booleano 'aceptable' como string 'aceptabilidad'
                                            $aceptabilidadStr = $control['aceptabilidad'] ?? null; // 'Aceptable' | 'No aceptable'
                                            $aceptableBool = array_key_exists('aceptable', $control) ? (bool)$control['aceptable'] : null;
                                            $observaciones = $control['observaciones'] ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $identificacion }}</td>
                                            <td>{{ $lote }}</td>
                                            <td>{{ $valor_leido }}</td>
                                            <td>{{ $valor_esperado }}</td>
                                            <td>{{ is_numeric($error) ? $error . '%' : $error }}</td>
                                            <td>
                                                @if(!empty($aceptabilidadStr))
                                                    <span class="badge badge-{{ strtolower($aceptabilidadStr) === 'aceptable' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($aceptabilidadStr) }}
                                                    </span>
                                                @elseif($aceptableBool !== null)
                                                    <span class="badge badge-{{ $aceptableBool ? 'success' : 'danger' }}">
                                                        {{ $aceptableBool ? 'Aceptable' : 'No Aceptable' }}
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $observaciones }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Veracidad -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Veracidad</h3>
                </div>
                <div class="card-body">
                    @php
                        // 1) Usar la variable pasada por el controlador si existe
                        $muestraRef = !empty($muestra_referencia ?? []) ? (array) $muestra_referencia : [];
                        // 2) Fallback: buscar en controles_analiticos por tipo o índice 3
                        if (empty($muestraRef)) {
                            foreach ($controles_analiticos as $control) {
                                $ctrl = is_object($control) ? (array)$control : (array)$control;
                                if (isset($ctrl['tipo']) && $ctrl['tipo'] === 'muestra_referencia') {
                                    $muestraRef = $ctrl;
                                    break;
                                }
                            }
                        }
                        if (empty($muestraRef) && isset($controles_analiticos[3])) {
                            $muestraRef = is_object($controles_analiticos[3]) ? (array)$controles_analiticos[3] : (array)$controles_analiticos[3];
                        }
                        // 3) Fallback: buscar por identificacion que contenga 'muestra de referencia'
                        if (empty($muestraRef)) {
                            foreach ($controles_analiticos as $control) {
                                $ctrl = is_object($control) ? (array)$control : (array)$control;
                                $iden = strtolower((string)($ctrl['identificacion'] ?? ''));
                                if (strpos($iden, 'muestra de referencia') !== false || strpos($iden, 'mrc') !== false) {
                                    $muestraRef = $ctrl;
                                    break;
                                }
                            }
                        }
                        // 4) Fallback final: tomar el último control con valores numéricos
                        if (empty($muestraRef)) {
                            for ($i = count($controles_analiticos) - 1; $i >= 0; $i--) {
                                $ctrl = is_object($controles_analiticos[$i]) ? (array)$controles_analiticos[$i] : (array)$controles_analiticos[$i];
                                $vl = $ctrl['valor_leido'] ?? null;
                                $ve = $ctrl['valor_esperado'] ?? null;
                                if (is_numeric($vl) && is_numeric($ve)) {
                                    $muestraRef = $ctrl;
                                    break;
                                }
                            }
                        }

                        // Cálculos auxiliares
                        $valorLeido = isset($muestraRef['valor_leido']) && is_numeric($muestraRef['valor_leido']) ? (float) $muestraRef['valor_leido'] : null;
                        $valorEsperado = isset($muestraRef['valor_esperado']) && is_numeric($muestraRef['valor_esperado']) ? (float) $muestraRef['valor_esperado'] : null;
                        $porcError = ($valorLeido !== null && $valorEsperado !== null && $valorEsperado != 0)
                            ? round((($valorLeido - $valorEsperado) / $valorEsperado) * 100, 2)
                            : null;

                        // Aceptabilidad: preferir cadena 'aceptabilidad', si no, mapear booleano 'aceptable'
                        $aceptabilidadTexto = null;
                        if (isset($muestraRef['aceptabilidad']) && $muestraRef['aceptabilidad'] !== '') {
                            $aceptabilidadTexto = (string) $muestraRef['aceptabilidad'];
                        } elseif (array_key_exists('aceptable', $muestraRef)) {
                            $aceptabilidadTexto = ((bool)$muestraRef['aceptable']) ? 'Aceptable' : 'No Aceptable';
                        }
                    @endphp

                    @if(!empty($muestraRef))
                        <h5>Muestra de Referencia</h5>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered">
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
                                    <tr>
                                        <td>{{ $muestraRef['identificacion'] ?? 'Muestra de referencia' }}</td>
                                        <td>{{ $muestraRef['lote'] ?? 'N/A' }}</td>
                                        <td>{{ $muestraRef['peso'] ?? 'N/A' }}</td>
                                        <td>{{ $muestraRef['volumen_agua'] ?? 'N/A' }}</td>
                                        <td>{{ $muestraRef['temperatura'] ?? 'N/A' }}</td>
                                        <td>{{ $muestraRef['valor_leido'] ?? 'N/A' }}</td>
                                        <td>{{ $muestraRef['valor_esperado'] ?? 'N/A' }}</td>
                                        <td>{{ $porcError !== null ? $porcError . '%' : 'N/A' }}</td>
                                        <td>
                                            @if(empty($aceptabilidadTexto))
                                                N/A
                                            @else
                                                <span class="badge badge-{{ strtolower($aceptabilidadTexto) === 'aceptable' ? 'success' : 'danger' }}">
                                                    {{ $aceptabilidadTexto }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $muestraRef['observaciones'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">No se encontraron datos de Veracidad (Muestra de Referencia).</div>
                    @endif
                </div>
            </div>

            <!-- Duplicados -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Precisión</h3>
                </div>
                <div class="card-body">
                    @php
                        $prec = $precision_analitica ?? [];
                        $identificacionDup = $prec['identificacion'] ?? null;
                        $dupA = isset($prec['duplicado_a']) ? (array) $prec['duplicado_a'] : [];
                        $dupB = isset($prec['duplicado_b']) ? (array) $prec['duplicado_b'] : [];

                        $valA = isset($dupA['valor_leido']) && is_numeric($dupA['valor_leido']) ? (float) $dupA['valor_leido'] : null;
                        $valB = isset($dupB['valor_leido']) && is_numeric($dupB['valor_leido']) ? (float) $dupB['valor_leido'] : null;

                        $promedio = ($valA !== null && $valB !== null) ? ($valA + $valB) / 2 : null;
                        $diferencia = ($valA !== null && $valB !== null) ? abs($valA - $valB) : null;

                        // Tomar aceptabilidad si viene guardada; si no, mostrar N/A
                        $aceptabilidadValor = $prec['aceptabilidad'] ?? ($prec['cumple'] ?? null);
                    @endphp

                    <table class="table table-bordered">
                        <tr>
                            <td colspan="9">
                                <div class="form-group mb-0">
                                    <label class="mb-0">Identificación de la Muestra (Aplicable a ambas réplicas)</label>
                                    <div class="value-display">{{ $identificacionDup ?? 'N/A' }}</div>
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
                            @if(!empty($dupA) || !empty($dupB))
                                @if(!empty($dupA))
                                    <tr>
                                        <td><strong>A</strong></td>
                                        <td>{{ $dupA['peso'] ?? 'N/A' }}</td>
                                        <td>{{ $dupA['volumen_agua'] ?? 'N/A' }}</td>
                                        <td>{{ $dupA['temperatura'] ?? 'N/A' }}</td>
                                        <td>{{ $dupA['valor_leido'] ?? 'N/A' }}</td>
                                        <td rowspan="2">{{ $promedio !== null ? number_format($promedio, 2) : 'N/A' }}</td>
                                        <td rowspan="2">{{ $diferencia !== null ? number_format($diferencia, 2) : 'N/A' }}</td>
                                        <td rowspan="2">
                                            @if($aceptabilidadValor !== null)
                                                <span class="badge badge-{{ (bool)$aceptabilidadValor ? 'success' : 'danger' }}">
                                                    {{ (bool)$aceptabilidadValor ? 'Aceptable' : 'No Aceptable' }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $dupA['observaciones'] ?? '' }}</td>
                                    </tr>
                                @endif
                                @if(!empty($dupB))
                                    <tr>
                                        <td><strong>B</strong></td>
                                        <td>{{ $dupB['peso'] ?? 'N/A' }}</td>
                                        <td>{{ $dupB['volumen_agua'] ?? 'N/A' }}</td>
                                        <td>{{ $dupB['temperatura'] ?? 'N/A' }}</td>
                                        <td>{{ $dupB['valor_leido'] ?? 'N/A' }}</td>
                                        <td>{{ $dupB['observaciones'] ?? '' }}</td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td colspan="9" class="text-center">No hay datos de duplicados disponibles</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>

            <!-- Ítems de Ensayo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ítems de Ensayo</h3>
                </div>
                <div class="card-body">
                    @if(empty($items_ensayo))
                        <p>No hay ítems de ensayo registrados.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
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
                                    @foreach($items_ensayo as $item)
                                        @php
                                            // Asegurarse de que $item sea un array
                                            $item = is_array($item) ? $item : (array)$item;
                                            $identificacion = $item['identificacion'] ?? 'N/A';
                                            $peso = $item['peso'] ?? 'N/A';
                                            $volumen_agua = $item['volumen_agua'] ?? 'N/A';
                                            $temperatura = $item['temperatura'] ?? 'N/A';
                                            $valor_leido = $item['valor_leido'] ?? 'N/A';
                                            $observaciones = $item['observaciones'] ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $identificacion }}</td>
                                            <td>{{ $peso }}</td>
                                            <td>{{ $volumen_agua }}</td>
                                            <td>{{ $temperatura }}</td>
                                            <td>{{ $valor_leido }}</td>
                                            <td>{{ $observaciones }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Observaciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Observaciones</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <div class="value-display" style="min-height: 100px;">
                            {{ $analysis->observaciones ?? 'Sin observaciones' }}
                        </div>
                    </div>
                    @if(!empty($analysis->observaciones_revision))
                        <div class="form-group mt-3">
                            <label class="form-label text-danger">Observaciones de Revisión</label>
                            <div class="value-display text-danger" style="min-height: 80px;">
                                {{ $analysis->observaciones_revision }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Acciones -->
            @if($detail->status === 'completed' && in_array($analysis->review_status, ['pending', 'rejected']))
                <div class="card">
                    <div class="card-footer text-right">
                        @if($analysis->review_status === 'rejected')
                            <span class="badge badge-danger mr-2">Rechazado</span>
                        @endif
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </div>
                </div>
            @elseif($analysis->review_status === 'approved')
                <div class="card">
                    <div class="card-footer text-right">
                        <span class="badge badge-success">Aprobado</span>
                    </div>
                </div>
            @endif

            <!-- Modal de Aprobación -->
            @if($detail->status === 'completed' && in_array($analysis->review_status, ['pending', 'rejected']))
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
                                    <p>¿Está seguro de aprobar este análisis de pH?</p>
                                    <div class="form-group">
                                        <label for="approval_notes">Observaciones (opcional):</label>
                                        <textarea class="form-control" id="approval_notes" name="observations" rows="3"></textarea>
                                        <input type="hidden" name="analysis_type" value="ph">
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
                                                 required>{{ old('observations') }}</textarea>
                                        @error('observations')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <input type="hidden" name="analysis_type" value="ph">
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
            @endif

            <script>
                $(document).ready(function() {
                    // Validación mínima del formulario de rechazo (en caso de no usar plugin de validate)
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

            @endsection
