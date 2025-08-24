@extends('lscefa::layouts.master')
@section('title', 'Revisión de Análisis de Textura (Solo Lectura)')
@section('content')
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
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Consecutivo No.</label>
                                        <input type="text" class="form-control" value="{{ $analysis->consecutive_no }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>ID Cotización</label>
                                        <input type="text" class="form-control" value="{{ $analysis->process->quote_id ?? 'N/A' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha del análisis</label>
                                        <input type="text" class="form-control" value="{{ $analysis->analysis_date }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Analista</label>
                                        <input type="text" class="form-control" value="{{ $analysis->analyst_name }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Metodología Utilizada</label>
                                        <input type="text" class="form-control" value="{{ $analysis->methodology_used }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Código Termómetro</label>
                                        <input type="text" class="form-control" value="{{ $analysis->thermometer_code }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Código Hidrómetro</label>
                                        <input type="text" class="form-control" value="{{ $analysis->hydrometer_code }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Items de Ensayo (Muestras) -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h3 class="card-title">Items de Ensayo (Muestras)</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nombre de la muestra</th>
                                                    <th>Peso (g)</th>
                                                    <th>Lecturas 40s</th>
                                                    <th>Temp 40s</th>
                                                    <th>Lecturas 2h</th>
                                                    <th>Temp 2h</th>
                                                    <th>Lectura Corr. 40s</th>
                                                    <th>Lectura Corr. 2h</th>
                                                    <th>Humedad %</th>
                                                    <th>% Arena</th>
                                                    <th>% Arcilla</th>
                                                    <th>% Limo</th>
                                                    <th>Clase textural</th>
                                                    <th>Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($analysis->samples ?? [] as $sample)
                                                <tr>
                                                    <td><input type="text" class="form-control" value="{{ $sample['codigo_interno'] ?? '' }}" readonly></td>
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
                                                @if(empty($analysis->samples))
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
                                    <th>Dpr Arena</th>
                                    <th>Dpr Arcilla</th>
                                    <th>Dpr Limo</th>
                                    <th>Aceptabilidad Control</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis->analytical_controls ?? [] as $control)
                                    @php
                                        $controlData = is_string($control) ? json_decode($control, true) : $control;
                                    @endphp
                                    @if(isset($controlData['identificacion']) && $controlData['identificacion'] !== 'Material de Referencia')
                                        <tr>
                                            <td><input type="text" class="form-control" value="{{ $controlData['identificacion'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['codigo_interno'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['arena_1'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['arcilla_1'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['limo_1'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['dpr_arena'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['dpr_arcilla'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['dpr_limo'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['aceptabilidad_control'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['observaciones'] ?? '' }}" readonly></td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if(!collect($analysis->analytical_controls ?? [])->contains(function($control) {
                                    $controlData = is_string($control) ? json_decode($control, true) : $control;
                                    return isset($controlData['identificacion']) && $controlData['identificacion'] !== 'Material de Referencia';
                                }))
                                    <tr>
                                        <td colspan="10" class="text-center">No hay controles analíticos registrados</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
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
                                    <th>Dpr Arena</th>
                                    <th>Dpr Arcilla</th>
                                    <th>Dpr Limo</th>
                                    <th>Aceptabilidad Control</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis->analytical_controls ?? [] as $control)
                                    @php
                                        $controlData = is_string($control) ? json_decode($control, true) : $control;
                                    @endphp
                                    @if(isset($controlData['identificacion']) && $controlData['identificacion'] === 'Material de Referencia')
                                        <tr>
                                            <td><input type="text" class="form-control" value="{{ $controlData['identificacion'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['codigo_interno'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['arena_1'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['arcilla_1'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['limo_1'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['dpr_arena'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['dpr_arcilla'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['dpr_limo'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['aceptabilidad_control'] ?? '' }}" readonly></td>
                                            <td><input type="text" class="form-control" value="{{ $controlData['observaciones'] ?? '' }}" readonly></td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if(!collect($analysis->analytical_controls ?? [])->contains(function($control) {
                                    $controlData = is_string($control) ? json_decode($control, true) : $control;
                                    return isset($controlData['identificacion']) && $controlData['identificacion'] === 'Material de Referencia';
                                }))
                                    <tr>
                                        <td colspan="10" class="text-center">No hay datos de Material de Referencia</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
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
