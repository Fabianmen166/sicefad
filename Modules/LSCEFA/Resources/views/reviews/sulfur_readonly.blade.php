@extends('lscefa::layouts.master')
@section('title', 'Revisión de Análisis de Azufre (Solo Lectura)')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Análisis de Azufre (Solo Lectura)</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary float-right">Volver</a>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">

            <!-- DEBUG: Mostrar qué tipo de análisis tenemos -->
            @php
                $analysisType = get_class($analysis);
                $hasAnalyticalControls = isset($analysis->analyticalControl);
            @endphp
            
            <!-- Mensaje de debug para verificar que la vista se cargue -->
            @if(config('app.debug'))
                <div class="alert alert-info">
                    <strong>Debug:</strong> Vista de Azufre cargada correctamente. 
                    Tipo: {{ $type ?? 'N/A' }}, 
                    ID: {{ $analysis->id ?? 'N/A' }},
                    Clase: {{ $analysisType ?? 'N/A' }}
                </div>
            @endif

            <!-- Información del Proceso -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Proceso</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID Proceso:</strong> {{ $analysis->process->process_id ?? 'N/A' }}</p>
                            <p><strong>Cliente:</strong> {{ $analysis->process->quote->customer->applicant ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Solicitud:</strong> {{ $analysis->process->created_at ? $analysis->process->created_at->format('d/m/Y') : 'N/A' }}</p>
                            <p><strong>Servicio:</strong> Azufre</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Datos del Análisis (antes de la tarjeta) -->
            <div class="table-responsive mb-4">
                <table class="table table-borderless align-middle" style="background: #f8f9fa; border-radius: 8px;">
                    <tr>
                        <td class="fw-bold" style="width: 10%">Consecutivo:</td>
                        <td style="width: 18%"><input type="text" class="form-control" value="{{ $analysis->consecutive_no ?? 'N/A' }}" readonly></td>
                        <td class="fw-bold" style="width: 16%">Metodología aplicada:</td>
                        <td colspan="2" style="width: 30%"><input type="text" class="form-control" value="Extracción por Bray (II) y cuantificación por ácido ascórbico" readonly></td>
                        <td class="fw-bold" style="width: 10%">Intervalo:</td>
                        <td style="width: 16%"><input type="text" class="form-control" value="{{ $analysis->method_interval ?? 'N/A' }}" readonly></td>
                    </tr>
                    <tr style="height: 10px;"></tr>
                    <tr>
                        <td class="fw-bold">Fecha:</td>
                        <td><input type="text" class="form-control" value="{{ $analysis->analysis_date ?? 'N/A' }}" readonly></td>
                        <td class="fw-bold">Equipo:</td>
                        <td><input type="text" class="form-control" value="{{ $analysis->equipment_used ?? 'N/A' }}" readonly></td>
                        <td></td>
                        <td class="fw-bold">Analista:</td>
                        <td><input type="text" class="form-control" value="{{ $analysis->analyst_name ?? 'N/A' }}" readonly></td>
                    </tr>
                </table>
            </div>

            <!-- Horizontal Navigation Bar -->
            <div class="mt-4 mb-3">
                <ul class="nav nav-tabs nav-fill" id="analysisTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="controls-tab" data-bs-toggle="tab" data-bs-target="#controls-content" type="button" role="tab" aria-controls="controls-content" aria-selected="true">
                            <i class="fas fa-check-circle"></i> Controles Analíticos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-content" type="button" role="tab" aria-controls="items-content" aria-selected="false">
                            <i class="fas fa-flask"></i> Items de Ensayo
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="analysisTabContent">
                <!-- Controles Analíticos Tab -->
                <div class="tab-pane fade show active" id="controls-content" role="tabpanel" aria-labelledby="controls-tab">
                    <!-- Controles Analíticos (primero) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Controles Analíticos (aplican a todo el lote)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="controles_analiticos_table">
                                    <thead>
                                        <tr>
                                            <th>Identificación</th>
                                            <th>Valor esperado</th>
                                            <th>Valor leído</th>
                                            <th>% Error</th>
                                            <th>Aceptabilidad</th>
                                            <th>% Recuperación</th>
                                            <th>Aceptabilidad</th>
                                            <th>% DPR</th>
                                            <th>Aceptabilidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($analysis->analyticalControl && is_array($analysis->analyticalControl->controles_analiticos))
                                            @foreach($analysis->analyticalControl->controles_analiticos as $index => $control)
                                                <tr>
                                                    <td>{{ $control['identificacion'] ?? 'Estándar ' . ($index == 0 ? 'A' : 'B') }}</td>
                                                    <td>{{ $control['valor_esperado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['valor_leido'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['porcentaje_error'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['aceptabilidad_error'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['porcentaje_recuperacion'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['aceptabilidad_recuperacion'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['porcentaje_dpr'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['aceptabilidad_dpr'] ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>Estándar A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                            </tr>
                                            <tr>
                                                <td>Estándar B</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                                <td>N/A</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Curva de Calibración y Duplicados (segundo) -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered" id="curva_duplicados_table">
                            <thead>
                                <tr>
                                    <th>Curva de calibración</th>
                                    <th>Valor</th>
                                    <th>Valor leído</th>
                                    <th>% ERROR</th>
                                    <th>Aceptabilidad</th>
                                    <th></th>
                                    <th>Duplicado</th>
                                    <th>Valor leído</th>
                                    <th>% DPR</th>
                                    <th>Aceptabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td rowspan="2">Curva de calibración</td>
                                    <td rowspan="2">0.995</td>
                                    <td rowspan="2">{{ $analysis->analyticalControl->curva_valor_leido ?? 'N/A' }}</td>
                                    <td rowspan="2">{{ $analysis->analyticalControl->curva_error_porcentaje ?? 'N/A' }}</td>
                                    <td rowspan="2">
                                        @if($analysis->analyticalControl->curva_aceptabilidad)
                                            {{ $analysis->analyticalControl->curva_aceptabilidad }}
                                        @elseif($analysis->analyticalControl->curva_error_porcentaje)
                                            @if($analysis->analyticalControl->curva_error_porcentaje <= 5)
                                                Aceptable
                                            @else
                                                No aceptable
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td colspan="2" rowspan="2"></td>
                                    <td>Duplicado A</td>
                                    <td>{{ $analysis->analyticalControl->dpr_duplicado_a ?? $analysis->analyticalControl->duplicado_a ?? 'N/A' }}</td>
                                    <td rowspan="2">{{ $analysis->analyticalControl->dpr_resultado ?? 'N/A' }}</td>
                                    <td rowspan="2">{{ $analysis->analyticalControl->dpr_aceptabilidad ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Duplicado B</td>
                                    <td>{{ $analysis->analyticalControl->dpr_duplicado_b ?? $analysis->analyticalControl->duplicado_b ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End of Controles Analíticos Tab -->

                <!-- Items de Ensayo Tab -->
                <div class="tab-pane fade" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                    <!-- Ítems de Ensayo -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ítems de Ensayo</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                Valores para el análisis de azufre. El cálculo de Azufre disponible (mg/kg) es automático.
                            </div>
                            <table class="table table-bordered" id="items_ensayo_table">
                                <thead>
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Código interno</th>
                                        <th>Peso muestra (g)</th>
                                        <th>pW</th>
                                        <th>V. Extractante (mL)</th>
                                        <th>Lectura Blanco (mg/L)</th>
                                        <th>Factor de dilución (fd)</th>
                                        <th>Azufre disponible (mg/L)</th>
                                        <th>Azufre disponible (mg/kg)</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $analysis->process->process_id ?? 'N/A' }}</td>
                                        <td>{{ $analysis->internal_code ?? 'N/A' }}</td>
                                        <td>{{ $analysis->sample_weight ?? 'N/A' }}</td>
                                        <td>{{ $analysis->pw ?? 'N/A' }}</td>
                                        <td>{{ $analysis->extractant_volume ?? 'N/A' }}</td>
                                        <td>{{ $analysis->blank_reading ?? 'N/A' }}</td>
                                        <td>{{ $analysis->dilution_factor ?? 'N/A' }}</td>
                                        <td>{{ $analysis->available_sulfur_mg_l ?? 'N/A' }}</td>
                                        <td>{{ $analysis->available_sulfur_mg_kg ?? 'N/A' }}</td>
                                        <td>{{ $analysis->item_observations ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End of Items de Ensayo Tab -->
                </div>
                <!-- End of Tab Content -->

            <!-- Observaciones Generales -->
            @if($analysis->observations)
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Observaciones Generales</h3>
                    </div>
                    <div class="card-body">
                        <p>{{ $analysis->observations }}</p>
                    </div>
                </div>
            @endif

            <!-- Botones de Acción -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="analysis_type" value="sulfur">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check mr-2"></i>Aprobar Análisis
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times mr-2"></i>Rechazar Análisis
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Rechazar -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST" id="rejectForm">
                @csrf
                <input type="hidden" name="analysis_type" value="sulfur">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Rechazar Análisis de Azufre</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="observations">Observaciones de Rechazo <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="observations" name="observations" rows="4" required 
                                  placeholder="Ingrese las observaciones que justifican el rechazo del análisis..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar Análisis</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
#analysisTabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 20px;
}

#analysisTabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    margin-right: 5px;
    padding: 12px 20px;
    font-weight: 500;
    color: #6c757d;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

#analysisTabs .nav-link:hover {
    background-color: #e9ecef;
    color: #495057;
    transform: translateY(-2px);
}

#analysisTabs .nav-link.active {
    background-color: #007bff;
    color: white;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

#analysisTabs .nav-link i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    #analysisTabs .nav-link {
        padding: 8px 12px;
        font-size: 14px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Validación del formulario de rechazo
    $('#rejectForm').on('submit', function(e) {
        var observations = $('#observations').val().trim();
        if (observations.length < 3) {
            e.preventDefault();
            alert('Las observaciones deben tener al menos 3 caracteres.');
            return false;
        }
    });

    // Tab switching functionality
    $('#analysisTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and content
        $('#analysisTabs .nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        
        // Show corresponding content
        var target = $(this).data('bs-target');
        $(target).addClass('show active');
    });
});
</script>
@endpush
