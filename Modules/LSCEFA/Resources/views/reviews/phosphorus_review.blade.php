@extends('lscefa::layouts.master')

@section('title', 'Revisión de Análisis de Fósforo')

@push('styles')
<style>
    .form-control[readonly], .value-display { background-color: #f8f9fa !important; border: 1px solid #e9ecef; }
    .table th, .table td { vertical-align: middle; }
    .card { margin-bottom: 1.25rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
    .card-header { background-color: #f8f9fa; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Análisis de Fósforo</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.quality.reviews.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Revisión</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('lscefa::partials.alerts')

            @php
                $process = $process ?? ($analysis->process ?? null);
                $quote = $quote ?? ($process->quote ?? null);
                $customer = $customer ?? ($process->customer ?? ($quote->customer ?? null));
                $service = $service ?? ($analysis->service ?? null);
                $type = $type ?? 'phosphorus';
                $technicianName = $technicianName
                    ?? ($analysis->nombre_analista ?? null)
                    ?? ($analysis->analista ?? null)
                    ?? ($analysis->analyst_name ?? null);
                $phDate = $analysis->fecha_analisis ?? $analysis->analysis_date ?? null;
            @endphp

            <div class="card">
                <div class="card-header"><h3 class="card-title">Información General</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="mb-0">Consecutivo</label>
                            <div class="value-display p-2">{{ $effectiveConsecutivo ?? ($analysis->consecutivo_no ?? ($detail->consecutivo_no ?? 'N/A')) }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-0">Fecha de Análisis</label>
                            <div class="value-display p-2">
                                @if($phDate)
                                    {{ \Carbon\Carbon::parse($phDate)->format('d/m/Y') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-0">Técnico Responsable</label>
                            <div class="value-display p-2">{{ $technicianName ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-0">Servicio</label>
                            <div class="value-display p-2">{{ $service->descripcion ?? 'Fósforo' }}</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="mb-0">Equipo Utilizado</label>
                            <div class="value-display p-2">{{ $analysis->equipo_utilizado ?? $analysis->equipment_used ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-0">Intervalo del Método</label>
                            <div class="value-display p-2">{{ $analysis->intervalo_metodo ?? $analysis->method_interval ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-0">Cliente</label>
                            <div class="value-display p-2">{{ $customer->nombre ?? $customer->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Resultados</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código interno</th>
                                    <th>Peso muestra (g)</th>
                                    <th>pW</th>
                                    <th>V. Extractante (mL)</th>
                                    <th>Lectura blanco</th>
                                    <th>Factor dilución</th>
                                    <th>Fósforo disp. (mg/L)</th>
                                    <th>Fósforo disp. (mg/kg)</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $analysis->codigo_interno ?? $analysis->internal_code ?? 'N/A' }}</td>
                                    <td>{{ $analysis->peso_muestra ?? $analysis->sample_weight ?? 'N/A' }}</td>
                                    <td>{{ $analysis->pw ?? 'N/A' }}</td>
                                    <td>{{ $analysis->v_extractante ?? $analysis->extractant_volume ?? 'N/A' }}</td>
                                    <td>{{ $analysis->lectura_blanco ?? $analysis->blank_reading ?? 'N/A' }}</td>
                                    <td>{{ $analysis->factor_dilucion ?? $analysis->dilution_factor ?? 'N/A' }}</td>
                                    <td>{{ $analysis->fosforo_disponible_mg_l ?? $analysis->available_phosphorus_mg_l ?? 'N/A' }}</td>
                                    <td>{{ $analysis->fosforo_disponible_mg_kg ?? $analysis->available_phosphorus_mg_kg ?? 'N/A' }}</td>
                                    <td>{{ $analysis->observaciones_item ?? $analysis->item_observations ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Controles Analíticos</h3></div>
                <div class="card-body">
                    @php
                        $controls = [];
                        if(isset($analyticalControl) && $analyticalControl){
                            $controls = $analyticalControl->controles_analiticos
                                ?? $analyticalControl->analytical_controls
                                ?? [];
                        }
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Identificación</th>
                                    <th class="text-center">Valor Esperado</th>
                                    <th class="text-center">Valor Leído</th>
                                    <th class="text-center">% Error</th>
                                    <th class="text-center">Aceptabilidad</th>
                                    <th class="text-center">% Recuperación</th>
                                    <th class="text-center">Aceptabilidad</th>
                                    <th class="text-center">% DPR</th>
                                    <th class="text-center">Aceptabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($controls as $row)
                                    <tr>
                                        <td>{{ $row['identificacion'] ?? $row['identification'] ?? '—' }}</td>
                                        <td>{{ $row['valor_esperado'] ?? $row['expected_value'] ?? '—' }}</td>
                                        <td>{{ $row['valor_leido'] ?? $row['measured_value'] ?? '—' }}</td>
                                        <td>{{ $row['porcentaje_error'] ?? $row['error_percentage'] ?? '—' }}</td>
                                        <td>{{ $row['aceptabilidad_error'] ?? $row['error_acceptable'] ?? '—' }}</td>
                                        <td>{{ $row['porcentaje_recuperacion'] ?? $row['recovery_percentage'] ?? '—' }}</td>
                                        <td>{{ $row['aceptabilidad_recuperacion'] ?? $row['recovery_acceptable'] ?? '—' }}</td>
                                        <td>{{ $row['porcentaje_dpr'] ?? $row['dpr_percentage'] ?? '—' }}</td>
                                        <td>{{ $row['aceptabilidad_dpr'] ?? $row['dpr_acceptable'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Sin registros de controles analíticos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @php
                $curveMeasured = isset($analyticalControl) ? ($analyticalControl->curve_measured_value ?? $analyticalControl->curva_valor_leido ?? null) : null;
                $curveErrorPct = isset($analyticalControl) ? ($analyticalControl->curve_error_percentage ?? $analyticalControl->curva_error_porcentaje ?? null) : null;
                $dupA = isset($analyticalControl) ? ($analyticalControl->dpr_duplicate_a ?? $analyticalControl->dpr_duplicado_a ?? $analyticalControl->duplicado_a ?? null) : null;
                $dupB = isset($analyticalControl) ? ($analyticalControl->dpr_duplicate_b ?? $analyticalControl->dpr_duplicado_b ?? $analyticalControl->duplicado_b ?? null) : null;
                $dprResult = isset($analyticalControl) ? ($analyticalControl->dpr_result ?? $analyticalControl->dpr_resultado ?? null) : null;
                $dprAccept = isset($analyticalControl) ? ($analyticalControl->dpr_acceptability ?? $analyticalControl->dpr_aceptabilidad ?? null) : null;
            @endphp
            <div class="card">
                <div class="card-header"><h3 class="card-title">Curva de Calibración y Duplicados (DPR)</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Curva de Calibración</th>
                                    <th class="text-center">Valor</th>
                                    <th class="text-center">Valor Leído</th>
                                    <th class="text-center">% ERROR</th>
                                    <th></th>
                                    <th class="text-center">Duplicado</th>
                                    <th class="text-center">Valor Leído</th>
                                    <th class="text-center">% DPR</th>
                                    <th class="text-center">Aceptabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td rowspan="2" class="align-middle text-center"><strong>Curva de calibración</strong></td>
                                    <td rowspan="2" class="align-middle text-center">0.995</td>
                                    <td rowspan="2" class="align-middle">{{ $curveMeasured !== null ? $curveMeasured : '—' }}</td>
                                    <td rowspan="2" class="align-middle">{{ $curveErrorPct !== null ? $curveErrorPct : '—' }}</td>

                                    <td colspan="2" rowspan="2"></td>
                                    <td class="text-center"><strong>Duplicado A</strong></td>
                                    <td>{{ $dupA !== null ? $dupA : '—' }}</td>
                                    <td rowspan="2">{{ $dprResult !== null ? $dprResult : '—' }}</td>
                                    <td rowspan="2">{{ $dprAccept !== null ? $dprAccept : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-center"><strong>Duplicado B</strong></td>
                                    <td>{{ $dupB !== null ? $dupB : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Observaciones</h3></div>
                <div class="card-body">
                    <div class="value-display p-3" style="min-height:80px">{{ $analysis->observaciones ?? 'Sin observaciones' }}</div>
                    @if(!empty($analysis->observaciones_revision))
                        <div class="mt-3">
                            <label class="text-danger mb-1">Observaciones de revisión</label>
                            <div class="value-display p-2 text-danger">{{ $analysis->observaciones_revision }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($detail->status === 'completed' && in_array($analysis->review_status, ['pending', 'rejected', null]))
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
                <div class="card"><div class="card-footer text-right"><span class="badge badge-success">Aprobado</span></div></div>
            @endif

            @if($detail->status === 'completed' && in_array($analysis->review_status, ['pending', 'rejected', null]))
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
                                    <p>¿Está seguro de aprobar este análisis de Fósforo?</p>
                                    <div class="form-group">
                                        <label for="approval_notes">Observaciones (opcional):</label>
                                        <textarea class="form-control" id="approval_notes" name="observations" rows="3"></textarea>
                                        <input type="hidden" name="analysis_type" value="phosphorus">
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
                                        <textarea class="form-control @error('observations') is-invalid @enderror" id="rejectReason" name="observations" rows="4" required minlength="3">{{ old('observations') }}</textarea>
                                        @error('observations')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <input type="hidden" name="analysis_type" value="phosphorus">
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
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(function(){
    $('#rejectForm').on('submit', function(e){
        const v = $('#rejectReason').val().trim();
        if(v.length < 3){ e.preventDefault(); $('#rejectReason').addClass('is-invalid'); }
    });
});
</script>
@endpush
