@extends('lscefa::layouts.master')
@section('title', 'Revisión de Análisis de Boro (Solo Lectura)')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Análisis de Boro (Solo Lectura)</h1>
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
                $hasAnalyticalControls = isset($analysis->standard_a_identification) || isset($analysis->calibration_curve_value);
                $hasTestItems = isset($analysis->test_items) && is_array($analysis->test_items) && count($analysis->test_items) > 0;
            @endphp

            <!-- Información del Proceso -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Proceso</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID Proceso:</strong> {{ $analysis->process->process_id ?? 'N/A' }}</p>
                            <p><strong>Cliente:</strong> {{ $analysis->process->customer->nombre ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Solicitud:</strong> {{ $analysis->process->created_at ? $analysis->process->created_at->format('d/m/Y') : 'N/A' }}</p>
                            <p><strong>Servicio:</strong> {{ $analysis->service->descripcion ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos del Análisis -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos del Análisis</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless align-middle" style="background: #f8f9fa; border-radius: 8px;">
                            <tr>
                                <td class="fw-bold" style="width: 10%">Consecutivo:</td>
                                <td style="width: 18%"><input type="text" class="form-control" value="{{ $analysis->consecutive_no ?? $analysis->consecutive_no ?? 'N/A' }}" readonly></td>
                                <td class="fw-bold" style="width: 16%">Metodología aplicada:</td>
                                <td colspan="2" style="width: 30%"><input type="text" class="form-control" value="{{ $analysis->applied_methodology ?? 'N/A' }}" readonly></td>
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

                    @if($hasAnalyticalControls)
                        <!-- Barra de Navegación Horizontal -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body p-0">
                                        <ul class="nav nav-tabs nav-fill" id="analysisTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="controls-tab" data-toggle="tab" href="#controls-content" role="tab" aria-controls="controls-content" aria-selected="true">
                                                    <i class="fas fa-flask mr-2"></i>Controles Analíticos
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="items-tab" data-toggle="tab" href="#items-content" role="tab" aria-controls="items-content" aria-selected="false">
                                                    <i class="fas fa-list-alt mr-2"></i>Ítems de Ensayo
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido de las Pestañas -->
                        <div class="tab-content" id="analysisTabsContent">
                            <!-- Pestaña Controles Analíticos -->
                            <div class="tab-pane fade show active" id="controls-content" role="tabpanel" aria-labelledby="controls-tab">
                                <h4 class="mb-3">
                                    <i class="fas fa-flask mr-2" style="color: #28a745;"></i>Controles Analíticos
                                </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Identificación</th>
                                                <th class="text-center">Valor Esperado</th>
                                                <th class="text-center">Valor Leído</th>
                                                <th class="text-center">% Error</th>
                                                <th class="text-center">Aceptabilidad Error</th>
                                                <th class="text-center">% Recuperación</th>
                                                <th class="text-center">Aceptabilidad Recuperación</th>
                                                <th class="text-center">% DPR</th>
                                                <th class="text-center">Aceptabilidad DPR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($analysis->standard_a_identification))
                                                <tr>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_identification ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_expected_value ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_read_value ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_error_percentage ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_error_acceptability ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_recovery_percentage ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_recovery_acceptability ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_dpr_percentage ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_a_dpr_acceptability ?? 'N/A' }}" readonly></td>
                                                </tr>
                                            @endif
                                            @if(isset($analysis->standard_b_identification))
                                                <tr>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_identification ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_expected_value ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_read_value ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_error_percentage ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_error_acceptability ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_recovery_percentage ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_recovery_acceptability ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_dpr_percentage ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->standard_b_dpr_acceptability ?? 'N/A' }}" readonly></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pestaña Ítems de Ensayo -->
                            <div class="tab-pane fade" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                                <h4 class="mb-3">
                                    <i class="fas fa-list-alt mr-2" style="color: #28a745;"></i>Ítems de Ensayo
                                </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Código Interno</th>
                                                <th class="text-center">Peso Muestra (g)</th>
                                                <th class="text-center">pW</th>
                                                <th class="text-center">V. Extractante (mL)</th>
                                                <th class="text-center">Lectura Blanco (mg/L)</th>
                                                <th class="text-center">Factor de Dilución (fd)</th>
                                                <th class="text-center">Boro Disponible (mg/L)</th>
                                                <th class="text-center">Boro Disponible (mg/kg)</th>
                                                <th class="text-center">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($hasTestItems)
                                                @foreach($analysis->test_items as $item)
                                                    <tr>
                                                        <td><input type="text" class="form-control" value="{{ $item['internal_code'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['sample_weight'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['pw'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['extractant_volume'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['blank_reading'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['dilution_factor'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['available_boron_mg_l'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['available_boron_mg_kg'] ?? 'N/A' }}" readonly></td>
                                                        <td><input type="text" class="form-control" value="{{ $item['observations'] ?? 'N/A' }}" readonly></td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <!-- Mostrar item individual de BoronAnalysis -->
                                                <tr>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->internal_code ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->sample_weight ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->pw ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->extractant_volume ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->blank_reading ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->dilution_factor ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->available_boron_mg_l ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->available_boron_mg_kg ?? 'N/A' }}" readonly></td>
                                                    <td><input type="text" class="form-control" value="{{ $analysis->item_observations ?? 'N/A' }}" readonly></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Solo mostrar items de ensayo si no hay controles analíticos -->
                        <h4 class="mb-3">
                            <i class="fas fa-list-alt mr-2" style="color: #28a745;"></i>Ítems de Ensayo
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">Código Interno</th>
                                        <th class="text-center">Peso Muestra (g)</th>
                                        <th class="text-center">pW</th>
                                        <th class="text-center">V. Extractante (mL)</th>
                                        <th class="text-center">Lectura Blanco (mg/L)</th>
                                        <th class="text-center">Factor de Dilución (fd)</th>
                                        <th class="text-center">Boro Disponible (mg/L)</th>
                                        <th class="text-center">Boro Disponible (mg/kg)</th>
                                        <th class="text-center">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($hasTestItems)
                                        @foreach($analysis->test_items as $item)
                                            <tr>
                                                <td><input type="text" class="form-control" value="{{ $item['internal_code'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['sample_weight'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['pw'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['extractant_volume'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['blank_reading'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['dilution_factor'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['available_boron_mg_l'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['available_boron_mg_kg'] ?? 'N/A' }}" readonly></td>
                                                <td><input type="text" class="form-control" value="{{ $item['observations'] ?? 'N/A' }}" readonly></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <!-- Mostrar item individual de BoronAnalysis -->
                                        <tr>
                                            <td><input type="text" class="form-control" value="{{ $analysis->internal_code ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->sample_weight ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->pw ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->extractant_volume ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->blank_reading ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->dilution_factor ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->available_boron_mg_l ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->available_boron_mg_kg ?? 'N/A' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $analysis->item_observations ?? 'N/A' }}" readonly></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
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
                                <a href="{{ route('lscefa.technical.analyses.boron.report', $analysis->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Descargar Reporte
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                                <i class="fas fa-check mr-1"></i>Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times mr-1"></i>Rechazar
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de Aprobación -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Aprobar Análisis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST">
                @csrf
                <input type="hidden" name="analysis_type" value="boron">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="review_observations">Observaciones de Revisión:</label>
                        <textarea class="form-control" id="review_observations" name="review_observations" rows="3" placeholder="Observaciones opcionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprobar Análisis</button>
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
                <h5 class="modal-title" id="rejectModalLabel">Rechazar Análisis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST" id="rejectForm">
                @csrf
                <input type="hidden" name="analysis_type" value="boron">
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Activar pestañas
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            // Puedes agregar lógica adicional aquí si es necesario
        });
        
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
@endpush
