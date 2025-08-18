@extends('lscefa::layouts.master')

@section('title', 'Revisión de Análisis de pH')

@push('styles')
<style>
    .form-control-plaintext {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
    }
    .table th {
        background-color: #f8f9fa;
    }
    .verification-item {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-left: 4px solid #007bff;
        background-color: #f8f9fa;
    }
    .value-display {
        padding: 0.375rem 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
        min-height: 38px;
        display: block;
        width: 100%;
    }
    .badge-status {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    .card-header {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
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

    <section class="content">
        <div class="container-fluid">
            @include('lscefa::partials.alerts')

            <!-- Información General -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Procesos Involucrados</label>
                                <div class="value-display">{{ $process->item_code ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Servicios Involucrados</label>
                                <div class="value-display">{{ $analysis->service->descripcion ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Consecutivo No.</label>
                                <div class="value-display">{{ $analysis->consecutivo_no ?? 'N/A' }}</div>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Código del Equipo</label>
                                <div class="value-display">{{ $analysis->codigo_equipo ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Método de Ensayo</label>
                                <div class="value-display">{{ $analysis->metodo_ensayo ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha de Análisis</label>
                                <div class="value-display">
                                    {{ $analysis->fecha_analisis ? \Carbon\Carbon::parse($analysis->fecha_analisis)->format('d/m/Y') : 'N/A' }}
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
                    @php
                        $controles = $analysis->controles_analiticos ?? [];
                    @endphp
                    
                    @if(!empty($controles))
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Control</th>
                                        <th>Valor de Referencia</th>
                                        <th>Valor Obtenido</th>
                                        <th>Límite de Aceptación</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($controles as $control)
                                        @php
                                            $esAceptable = $control['es_aceptable'] ?? false;
                                            $resultadoClass = $esAceptable ? 'success' : 'danger';
                                            $resultadoText = $esAceptable ? 'Aceptable' : 'No Aceptable';
                                        @endphp
                                        <tr>
                                            <td>{{ $control['nombre'] ?? 'N/A' }}</td>
                                            <td>{{ $control['valor_referencia'] ?? 'N/A' }}</td>
                                            <td>{{ $control['valor_obtenido'] ?? 'N/A' }}</td>
                                            <td>±{{ $control['limite_aceptacion'] ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $resultadoClass }}">
                                                    {{ $resultadoText }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(!empty($analysis->observaciones_controles))
                            <div class="form-group mt-3">
                                <label>Observaciones:</label>
                                <div class="value-display">
                                    {{ $analysis->observaciones_controles }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            No se encontraron controles analíticos registrados para este análisis.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Veracidad -->
            @if(isset($analysis->precision_analitica['muestra_referencia']) || isset($analysis->precision_analitica['duplicado_a']))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Veracidad</h3>
                    </div>
                    <div class="card-body">
                        @if(isset($analysis->precision_analitica['muestra_referencia']))
                            <h5>Muestra de Referencia</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Identificación</th>
                                            <th>Valor de Referencia</th>
                                            <th>Valor Obtenido</th>
                                            <th>% Recuperación</th>
                                            <th>Límite de Aceptación</th>
                                            <th>Resultado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $ref = $analysis->precision_analitica['muestra_referencia'];
                                            $porcentaje = isset($ref['valor_referencia']) && $ref['valor_referencia'] != 0 
                                                ? (($ref['valor_obtenido'] ?? 0) / $ref['valor_referencia']) * 100 
                                                : 0;
                                            $esAceptable = $porcentaje >= 80 && $porcentaje <= 120;
                                        @endphp
                                        <tr>
                                            <td>{{ $ref['identificacion'] ?? 'N/A' }}</td>
                                            <td>{{ $ref['valor_referencia'] ?? 'N/A' }}</td>
                                            <td>{{ $ref['valor_obtenido'] ?? 'N/A' }}</td>
                                            <td>{{ number_format($porcentaje, 2) }}%</td>
                                            <td>80% - 120%</td>
                                            <td>
                                                <span class="badge bg-{{ $esAceptable ? 'success' : 'danger' }}">
                                                    {{ $esAceptable ? 'Aceptable' : 'No Aceptable' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if(isset($analysis->precision_analitica['duplicado_a']) || isset($analysis->precision_analitica['duplicado_b']))
                            <h5>Precisión Analítica</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Réplica</th>
                                            <th>Peso (g)</th>
                                            <th>Volumen Agua (mL)</th>
                                            <th>Temperatura (°C)</th>
                                            <th>Valor de pH</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(['duplicado_a' => 'A', 'duplicado_b' => 'B'] as $key => $label)
                                            @if(isset($analysis->precision_analitica[$key]))
                                                @php
                                                    $duplicado = $analysis->precision_analitica[$key];
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $label }}</strong></td>
                                                    <td>{{ $duplicado['peso'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['volumen_agua'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['temperatura'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['valor_ph'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['observaciones'] ?? 'N/A' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @php
                                $precision = $analysis->precision_analitica;
                                $desviacionEstandar = $precision['desviacion_estandar'] ?? null;
                                $coeficienteVariacion = $precision['coeficiente_variacion'] ?? null;
                                $esAceptable = $precision['es_aceptable'] ?? false;
                            @endphp
                            
                            @if($desviacionEstandar !== null || $coeficienteVariacion !== null)
                                <div class="row mt-4">
                                    @if($desviacionEstandar !== null)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Desviación Estándar:</label>
                                                <div class="value-display">
                                                    {{ $desviacionEstandar }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($coeficienteVariacion !== null)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Coeficiente de Variación:</label>
                                                <div class="value-display">
                                                    {{ $coeficienteVariacion }}%
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Resultado:</label>
                                            <div>
                                                <span class="badge bg-{{ $esAceptable ? 'success' : 'danger' }} badge-status">
                                                    {{ $esAceptable ? 'Aceptable' : 'No Aceptable' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <!-- Ítems de Ensayo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ítems de Ensayo</h3>
                </div>
                <div class="card-body">
                    @php
                        $items = $items_ensayo ?? [];
                    @endphp
                    
                    @if(!empty($items))
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Código de Ítem</th>
                                        <th>Peso (g)</th>
                                        <th>Volumen Agua (mL)</th>
                                        <th>Temperatura (°C)</th>
                                        <th>Valor de pH</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                        <tr>
                                            <td>{{ $item['identificacion'] ?? 'N/A' }}</td>
                                            <td>{{ $item['peso'] ?? 'N/A' }}</td>
                                            <td>{{ $item['volumen_agua'] ?? 'N/A' }}</td>
                                            <td>{{ $item['temperatura'] ?? 'N/A' }}</td>
                                            <td>{{ $item['valor_leido'] ?? 'N/A' }}</td>
                                            <td>{{ $item['observaciones'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if(!empty($estadisticas) && isset($estadisticas['promedio_ph']))
                                    <tfoot>
                                        <tr class="table-active">
                                            <td colspan="4" class="text-end"><strong>Promedio de pH:</strong></td>
                                            <td colspan="2"><strong>{{ number_format($estadisticas['promedio_ph'], 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No se encontraron ítems de ensayo registrados para este análisis.
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
                    <div class="form-group">
                        <label>Observaciones Generales</label>
                        <div class="value-display" style="min-height: 100px;">
                            {{ $analysis->observaciones_generales ?? 'Sin observaciones' }}
                        </div>
                    </div>
                    
                    @if(!empty($analysis->observaciones_revision))
                        <div class="form-group mt-3">
                            <label>Observaciones de la Revisión</label>
                            <div class="value-display" style="min-height: 80px;">
                                {{ $analysis->observaciones_revision }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Acciones -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="float-right">
                                <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver
                                </a>
                                
                                @if($detail->status === 'completed')
                                    <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#approveModal">
                                        <i class="fas fa-check-circle mr-1"></i> Aprobar
                                    </button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                        <i class="fas fa-times-circle mr-1"></i> Rechazar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de Aprobación -->
@if($detail->status === 'completed')
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveModalLabel">Confirmar Aprobación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="analysis_type" value="ph">
                    <div class="modal-body">
                        <p>¿Está seguro que desea aprobar este análisis de pH?</p>
                        <div class="form-group">
                            <label for="observations">Observaciones (opcional):</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3" placeholder="Agregue cualquier observación relevante"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle mr-1"></i> Aprobar Análisis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Rechazo -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">Confirmar Rechazo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST" id="rejectForm">
                    @csrf
                    <input type="hidden" name="analysis_type" value="ph">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejectReason" class="required">Motivo del Rechazo:</label>
                            <textarea class="form-control" id="rejectReason" name="observations" rows="4" required 
                                      placeholder="Por favor, indique el motivo del rechazo del análisis"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle mr-1"></i> Rechazar Análisis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Validación del formulario de rechazo
    $('#rejectForm').validate({
        rules: {
            observations: {
                required: true,
                minlength: 10
            }
        },
        messages: {
            observations: {
                required: "Por favor ingrese el motivo del rechazo.",
                minlength: "El motivo debe tener al menos 10 caracteres."
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
});
</script>
@endpush

@endsection
