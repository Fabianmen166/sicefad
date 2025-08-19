@extends('lscefa::layouts.master')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Revisión de Análisis de Conductividad</h3>
        <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Información General -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Información General</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Consecutivo No.:</strong> {{ $analysis->consecutivo_no ?? 'N/A' }}</p>
                    <p><strong>Fecha del Análisis:</strong> {{ $detail->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Proceso:</strong> {{ $detail->process->item_code ?? 'N/A' }}</p>
                    <p><strong>Servicio:</strong> {{ $detail->service->descripcion ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Analista:</strong> {{ $analysis->user->name ?? 'N/A' }}</p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-{{ $detail->status === 'rejected' ? 'danger' : ($detail->status === 'approved' ? 'success' : 'warning text-dark') }}">
                            {{ $detail->status }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles del Equipo -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Detalles del Equipo</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p><strong>Equipo Utilizado:</strong> {{ $analysis->equipo_utilizado ?? $analysis->codigo_equipo ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Resolución Instrumental:</strong> {{ $analysis->resolucion_instrumental ?? $analysis->serial_conductimetro ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Unidades de Reporte Equipo:</strong> {{ $analysis->unidades_reporte ?? $analysis->serial_sonda_temperatura ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Intervalo del Método:</strong> {{ $analysis->intervalo_metodo ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Blanco del Proceso -->
    @php
        // Preferir estructura guardada en controles_analiticos[0]
        $blanco = null;
        if (isset($analysis->controles_analiticos) && is_array($analysis->controles_analiticos) && isset($analysis->controles_analiticos[0])) {
            $blanco = $analysis->controles_analiticos[0];
        }
        $blanco_valor = $blanco['valor_leido'] ?? ($analysis->blanco_valor_leido ?? null);
        $blanco_obs = $blanco['observaciones'] ?? ($analysis->blanco_observaciones ?? null);
        // Si viene texto 'Aceptable'/'No aceptable' desde backend, usarlo; de lo contrario, calcular por regla ≤ 0.1 dS/m
        $blanco_flag = $blanco['aceptable'] ?? null;
        $blanco_aceptable = null;
        if (is_string($blanco_flag)) {
            $blanco_aceptable = strtolower($blanco_flag) === 'aceptable';
        } elseif (is_numeric($blanco_valor)) {
            $blanco_aceptable = ($blanco_valor <= 0.1);
        }
    @endphp
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Blanco del Proceso</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Blanco del proceso</th>
                            <th>Valor leído (dS/m)</th>
                            <th>Aceptable/No aceptable</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Blanco del proceso</td>
                            <td>{{ is_null($blanco_valor) ? 'N/A' : number_format((float)$blanco_valor, 2) }}</td>
                            <td>
                                @if(!is_null($blanco_aceptable))
                                    <span class="badge {{ $blanco_aceptable ? 'bg-success' : 'bg-danger' }}">{{ $blanco_aceptable ? 'Aceptable' : 'No aceptable' }}</span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $blanco_obs ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Precisión (Duplicados) -->
    @php
        // Estructura de almacenamiento: precision_analitica { duplicado_a: {identificacion, peso, volumen_agua, temperatura, valor_leido}, duplicado_b: {...}, promedio, diferencia, aceptable }
        $prec = is_array($analysis->precision_analitica ?? null) ? $analysis->precision_analitica : [];
        // Lecturas en µS/cm
        $dupA = $prec['duplicado_a']['valor_leido'] ?? ($analysis->duplicado_a_valor_leido ?? null);
        $dupB = $prec['duplicado_b']['valor_leido'] ?? ($analysis->duplicado_b_valor_leido ?? null);
        // Variables extra A
        $a_peso = $prec['duplicado_a']['peso'] ?? ($analysis->duplicado_peso ?? null);
        $a_volumen = $prec['duplicado_a']['volumen_agua'] ?? ($analysis->duplicado_volumen_agua ?? null);
        $a_temp = $prec['duplicado_a']['temperatura'] ?? ($analysis->duplicado_temperatura ?? null);
        $a_ident = $prec['duplicado_a']['identificacion'] ?? null;
        // Variables extra B
        $b_peso = $prec['duplicado_b']['peso'] ?? ($analysis->duplicado_b_peso ?? null);
        $b_volumen = $prec['duplicado_b']['volumen_agua'] ?? ($analysis->duplicado_b_volumen_agua ?? null);
        $b_temp = $prec['duplicado_b']['temperatura'] ?? ($analysis->duplicado_b_temperatura ?? null);
        $b_ident = $prec['duplicado_b']['identificacion'] ?? null;
        // Identificación común: preferir la de A si existe, si no la B, si no el legado
        $ident_common = $a_ident ?? $b_ident ?? ($analysis->duplicado_identificacion ?? null);

        // Conversión a mS/m para mostrar
        $a_msm = (is_numeric($dupA) ? ($dupA/10) : null);
        $b_msm = (is_numeric($dupB) ? ($dupB/10) : null);
        // Si backend ya calculó promedio/diferencia/aceptable, usarlos; si no, calcularlos
        $promedio_calc = (!is_null($a_msm) && !is_null($b_msm)) ? (($a_msm + $b_msm)/2) : null;
        $diferencia_calc = (!is_null($a_msm) && !is_null($b_msm)) ? (abs($a_msm - $b_msm)) : null;
        $promedio = isset($prec['promedio']) && is_numeric($prec['promedio']) ? ($prec['promedio']/10) : $promedio_calc; // backend promedio podría venir en µS/cm -> mS/m
        $diferencia = isset($prec['diferencia']) && is_numeric($prec['diferencia']) ? ($prec['diferencia']/10) : $diferencia_calc; // µS/cm -> mS/m
        $acc_flag = $prec['aceptable'] ?? null; // 'Aceptable'/'No aceptable'
        $precision_aceptable = null;
        if (is_string($acc_flag)) {
            $precision_aceptable = strtolower($acc_flag) === 'aceptable';
        } elseif (!is_null($promedio) && !is_null($diferencia)) {
            // Criterios en mS/m
            if ($promedio <= 50) { $limite = 5; }
            elseif ($promedio <= 200) { $limite = 20; }
            else { $limite = $promedio * 0.10; }
            $precision_aceptable = ($diferencia <= $limite);
        }
    @endphp
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Precisión (Duplicados)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <td colspan="9"><strong>Identificación:</strong> {{ $ident_common ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Réplica</th>
                        <th>Peso (g)</th>
                        <th>Volumen H₂O (mL)</th>
                        <th>Temperatura (°C)</th>
                        <th>Valor leído (µS/cm)</th>
                        <th>Valor leído (mS/m)</th>
                        <th>Promedio (mS/m)</th>
                        <th>Diferencia (mS/m)</th>
                        <th>Aceptable/No aceptable</th>
                        <th>Observaciones</th>
                    </tr>
                    <tr>
                        <td><strong>A</strong></td>
                        <td>{{ $a_peso ?? 'N/A' }}</td>
                        <td>{{ $a_volumen ?? 'N/A' }}</td>
                        <td>{{ $a_temp ?? 'N/A' }}</td>
                        <td>{{ is_null($dupA) ? 'N/A' : number_format((float)$dupA, 2) }}</td>
                        <td>{{ is_null($a_msm) ? 'N/A' : number_format((float)$a_msm, 2) }}</td>
                        <td rowspan="2">{{ is_null($promedio) ? '' : number_format((float)$promedio, 2) }}</td>
                        <td rowspan="2">{{ is_null($diferencia) ? '' : number_format((float)$diferencia, 2) }}</td>
                        <td rowspan="2">
                            @if(!is_null($precision_aceptable))
                                <span class="badge {{ $precision_aceptable ? 'bg-success' : 'bg-danger' }}">{{ $precision_aceptable ? 'Aceptable' : 'No aceptable' }}</span>
                            @endif
                        </td>
                        <td rowspan="2">{{ $analysis->duplicado_observaciones ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>B</strong></td>
                        <td>{{ $b_peso ?? 'N/A' }}</td>
                        <td>{{ $b_volumen ?? 'N/A' }}</td>
                        <td>{{ $b_temp ?? 'N/A' }}</td>
                        <td>{{ is_null($dupB) ? 'N/A' : number_format((float)$dupB, 2) }}</td>
                        <td>{{ is_null($b_msm) ? 'N/A' : number_format((float)$b_msm, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Veracidad (Controles de calidad) -->
    @php
        $veracidad = $analysis->veracidad_analitica ?? [];
        // Asegurar formato de array indexado
        if (is_object($veracidad)) { $veracidad = (array)$veracidad; }
        if (!is_array($veracidad)) { $veracidad = []; }
        $veracidad = array_values($veracidad);
        // Filtrar solo entradas con datos numéricos en esperado o leído
        $veracidad_filtrada = array_values(array_filter($veracidad, function($row) {
            if (is_object($row)) { $row = (array)$row; }
            $ve = $row['valor_esperado'] ?? null; $vl = $row['valor_leido'] ?? null;
            return (is_numeric($ve) && $ve != 0) || is_numeric($vl);
        }));
        // Tomar las primeras dos entradas válidas
        $v0 = $veracidad_filtrada[0] ?? null;
        $v1 = $veracidad_filtrada[1] ?? null;
        $rec0 = (isset($v0['valor_esperado'], $v0['valor_leido']) && is_numeric($v0['valor_esperado']) && (float)$v0['valor_esperado'] != 0)
            ? ((float)$v0['valor_leido'] / (float)$v0['valor_esperado']) * 100 : null;
        $acc0 = !is_null($rec0) ? ($rec0 >= 70 && $rec0 <= 130) : null;
        $rec1 = (isset($v1['valor_esperado'], $v1['valor_leido']) && is_numeric($v1['valor_esperado']) && (float)$v1['valor_esperado'] != 0)
            ? ((float)$v1['valor_leido'] / (float)$v1['valor_esperado']) * 100 : null;
        $acc1 = !is_null($rec1) ? ($rec1 >= 70 && $rec1 <= 130) : null;
    @endphp
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Controles de calidad (Veracidad)</h4>
        </div>
        <div class="card-body">
            <h5>Estándar de Control</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Identificación</th>
                            <th>Valor esperado (dS/m)</th>
                            <th>Valor leído (dS/m)</th>
                            <th>% Recuperación</th>
                            <th>Aceptable/No aceptable</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($v0)
                            <tr>
                                <td>{{ $v0['identificacion'] ?? 'N/A' }}</td>
                                <td>{{ isset($v0['valor_esperado']) ? number_format((float)$v0['valor_esperado'], 4) : 'N/A' }}</td>
                                <td>{{ isset($v0['valor_leido']) ? number_format((float)$v0['valor_leido'], 4) : 'N/A' }}</td>
                                <td>{{ is_null($rec0) ? '' : number_format((float)$rec0, 2) . '%' }}</td>
                                <td>
                                    @if(!is_null($acc0))
                                        <span class="badge {{ $acc0 ? 'bg-success' : 'bg-danger' }}">{{ $acc0 ? 'Aceptable' : 'No aceptable' }}</span>
                                    @endif
                                </td>
                                <td>{{ $v0['observaciones'] ?? '' }}</td>
                            </tr>
                        @else
                            <tr><td colspan="6" class="text-center text-muted">Sin datos</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <h5>Muestra de Referencia</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Identificación</th>
                            <th>Peso (g)</th>
                            <th>Volumen H₂O (mL)</th>
                            <th>Temperatura (°C)</th>
                            <th>Valor esperado (dS/m)</th>
                            <th>Valor leído (dS/m)</th>
                            <th>% Recuperación</th>
                            <th>Aceptable/No aceptable</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($v1)
                            <tr>
                                <td>{{ $v1['identificacion'] ?? 'N/A' }}</td>
                                <td>{{ $v1['peso'] ?? 'N/A' }}</td>
                                <td>{{ $v1['volumen_agua'] ?? 'N/A' }}</td>
                                <td>{{ $v1['temperatura'] ?? 'N/A' }}</td>
                                <td>{{ isset($v1['valor_esperado']) ? number_format((float)$v1['valor_esperado'], 4) : 'N/A' }}</td>
                                <td>{{ isset($v1['valor_leido']) ? number_format((float)$v1['valor_leido'], 4) : 'N/A' }}</td>
                                <td>{{ is_null($rec1) ? '' : number_format((float)$rec1, 2) . '%' }}</td>
                                <td>
                                    @if(!is_null($acc1))
                                        <span class="badge {{ $acc1 ? 'bg-success' : 'bg-danger' }}">{{ $acc1 ? 'Aceptable' : 'No aceptable' }}</span>
                                    @endif
                                </td>
                                <td>{{ $v1['observaciones'] ?? '' }}</td>
                            </tr>
                        @else
                            <tr><td colspan="9" class="text-center text-muted">Sin datos</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <small class="text-muted">Criterios: Blanco ≤ 0,1 dS/m. Precisión: 0–50 mS/m: ≤5 mS/m; 50–200 mS/m: ≤20 mS/m; >200 mS/m: ≤10%. Veracidad: Recuperación 70–130%.</small>
            </div>
        </div>
    </div>

    <!-- Ítems de Ensayo -->
    @if(isset($analysis->items_ensayo) && count($analysis->items_ensayo) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Ítems de Ensayo</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Identificación</th>
                                <th>Peso (g)</th>
                                <th>Volumen H₂O (mL)</th>
                                <th>Temperatura (°C)</th>
                                <th>Valor Leído (µS/cm)</th>
                                <th>Valor Leído (dS/m)</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analysis->items_ensayo as $item)
                                <tr>
                                    <td>{{ $item['identificacion'] ?? 'N/A' }}</td>
                                    <td>{{ $item['peso'] ?? 'N/A' }}</td>
                                    <td>{{ $item['volumen_agua'] ?? 'N/A' }}</td>
                                    <td>{{ $item['temperatura'] ?? 'N/A' }}</td>
                                    <td>{{ $item['valor_leido'] ?? 'N/A' }}</td>
                                    <td>{{ $item['valor_leido_dsm'] ?? 'N/A' }}</td>
                                    <td>{{ $item['observaciones'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Observaciones del Analista -->
    @php $obsAnalista = $analysis->observaciones ?? $analysis->observaciones_analista ?? null; @endphp
    @if(!empty($obsAnalista))
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Observaciones del Analista</h4>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded">
                    {{ $obsAnalista }}
                </div>
            </div>
        </div>
    @endif

    <!-- Acciones de Revisión -->
    @if($detail->status === 'completed')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Acciones de Revisión</h4>
                @if($analysis->review_status === 'rejected')
                    <span class="badge badge-danger">Rechazado</span>
                @elseif($analysis->review_status === 'approved')
                    <span class="badge badge-success">Aprobado</span>
                @else
                    <span class="badge badge-warning text-dark">Pendiente</span>
                @endif
            </div>
            <div class="card-body text-right">
                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#approveModalConductivity">
                    <i class="fas fa-check"></i> Aprobar
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModalConductivity">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </div>

        <!-- Modal de Aprobación Conductividad -->
        <div class="modal fade" id="approveModalConductivity" tabindex="-1" role="dialog" aria-labelledby="approveModalConductivityLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalConductivityLabel">Confirmar Aprobación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>¿Está seguro de aprobar este análisis de conductividad?</p>
                            <div class="form-group">
                                <label for="approval_notes_conductivity">Observaciones (opcional):</label>
                                <textarea class="form-control" id="approval_notes_conductivity" name="observations" rows="3"></textarea>
                                <input type="hidden" name="analysis_type" value="conductivity">
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

        <!-- Modal de Rechazo Conductividad -->
        <div class="modal fade" id="rejectModalConductivity" tabindex="-1" role="dialog" aria-labelledby="rejectModalConductivityLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalConductivityLabel">Motivo de Rechazo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST" id="rejectFormConductivity">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejectReasonConductivity">Motivo del rechazo <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="rejectReasonConductivity"
                                          name="observations"
                                          rows="4"
                                          required minlength="3">{{ old('observations') }}</textarea>
                                @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <input type="hidden" name="analysis_type" value="conductivity">
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
                $('#rejectFormConductivity').on('submit', function(e) {
                    const reason = $('#rejectReasonConductivity').val().trim();
                    if (!reason || reason.length < 3) {
                        e.preventDefault();
                        $('#rejectReasonConductivity').addClass('is-invalid');
                        const $fb = $('#rejectReasonConductivity').siblings('.invalid-feedback');
                        if ($fb.length) { $fb.text('El motivo del rechazo es obligatorio y debe tener al menos 3 caracteres.'); }
                    }
                });
            });
        </script>
    @endif
</div>
@endsection
