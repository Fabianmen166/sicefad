@extends('lscefa::layouts.master')

@section('title', 'Revisión de Análisis de Humedad')

@section('content')
<div class="content-wrapper p-0 m-0" style="max-width: 100%;">
    <!-- Content Header -->
    <section class="content-header p-0 m-0">
        <div class="container-fluid p-0 m-0"></div>
    </section>

    @if(isset($analisis_completos) && count($analisis_completos) > 1)
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Este servicio contiene <strong>{{ $total_analisis }}</strong> análisis de humedad individuales.
        </div>
    @endif

    <!-- Main Content -->
    <section class="content p-0 m-0">
        <div class="container-fluid p-0 m-0">
            <!-- INFORMACIÓN GENERAL -->
            <div class="card border-0 shadow-none">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Información General del Servicio</h3>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Proceso (solo lectura) -->
                        <div class="form-group col-md-3">
                            <label for="proceso">Procesos Involucrados</label>
                            <input type="text" class="form-control form-control-sm" id="proceso"
                                value="{{ $process->process_id ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Servicio (solo lectura) -->
                        <div class="form-group col-md-3">
                            <label for="servicio">Servicios Involucrados</label>
                            <input type="text" class="form-control form-control-sm" id="servicio"
                                value="{{ $detail->service->descripcion ?? 'Humedad' }}" readonly>
                        </div>
                        <!-- Total de Análisis -->
                        <div class="form-group col-md-3">
                            <label for="total_analisis">Total de Análisis</label>
                            <input type="text" class="form-control form-control-sm" id="total_analisis"
                                value="{{ $total_analisis ?? 1 }}" readonly>
                        </div>
                        <!-- Estado -->
                        <div class="form-group col-md-3">
                            <label for="estado">Estado</label>
                            <input type="text" class="form-control form-control-sm" id="estado"
                                value="{{ ucfirst($analysis->review_status ?? 'pending') }}" readonly>
                        </div>
                    </div>
                                        <div class="row g-3 mt-2">
                         <!-- Cliente -->
                         <div class="form-group col-md-6">
                             <label for="cliente">Cliente</label>
                             <input type="text" class="form-control form-control-sm" id="cliente"
                                 value="{{ $customer->applicant ?? 'N/A' }}" readonly>
                         </div>
                         <!-- Técnico -->
                         <div class="form-group col-md-6">
                             <label for="tecnico">Técnico</label>
                             <input type="text" class="form-control form-control-sm" id="tecnico"
                                 value="{{ $technicianName ?? 'No asignado' }}" readonly>
                         </div>
                     </div>
                </div>
            </div>

            <!-- DETALLES DEL EQUIPO -->
            <div class="card mt-3 border-0 shadow-none">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalles del Equipo</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Hora ingreso al horno -->
                        <div class="col-md-3">
                            <label for="hora_ingreso_horno">Hora ingreso al horno</label>
                            <input type="text" class="form-control form-control-sm" id="hora_ingreso_horno"
                                value="{{ $analysis->hora_ingreso_horno ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Hora salida del horno -->
                        <div class="col-md-3">
                            <label for="hora_salida_horno">Hora salida del horno</label>
                            <input type="text" class="form-control form-control-sm" id="hora_salida_horno"
                                value="{{ $analysis->hora_salida_horno ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Temperatura del horno -->
                        <div class="col-md-3">
                            <label for="temperatura_horno">Temperatura del horno (°C)</label>
                            <input type="text" class="form-control form-control-sm" id="temperatura_horno"
                                value="{{ $analysis->temperatura_horno ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Resolución instrumental -->
                        <div class="col-md-3">
                            <label for="resolucion_instrumental">Resolución instrumental</label>
                            <input type="text" class="form-control form-control-sm" id="resolucion_instrumental"
                                value="{{ $analysis->resolucion_instrumental ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <!-- Nombre del método -->
                        <div class="col-md-4">
                            <label for="nombre_metodo">Nombre del método</label>
                            <input type="text" class="form-control form-control-sm" id="nombre_metodo"
                                value="{{ $analysis->nombre_metodo ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Equipo utilizado -->
                        <div class="col-md-4">
                            <label for="equipo_utilizado">Equipo Utilizado</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_utilizado"
                                value="{{ $analysis->equipo_utilizado ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Unidades de reporte del equipo -->
                        <div class="col-md-4">
                            <label for="unidades_reporte_equipo">Unidades de Reporte del Equipo</label>
                            <input type="text" class="form-control form-control-sm" id="unidades_reporte_equipo"
                                value="{{ $analysis->unidades_reporte_equipo ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <!-- Intervalo del método -->
                        <div class="col-md-4">
                            <label for="intervalo_metodo">Intervalo del método</label>
                            <input type="text" class="form-control form-control-sm" id="intervalo_metodo"
                                value="{{ $analysis->intervalo_metodo ?? 'N/A' }}" readonly>
                        </div>
                        <!-- Fecha del análisis -->
                        <div class="col-md-4">
                            <label for="fecha_analisis">Fecha del Análisis</label>
                            <input type="text" class="form-control form-control-sm" id="fecha_analisis"
                                value="{{ $analysis->fecha_analisis ? $analysis->fecha_analisis->format('d/m/Y') : 'N/A' }}" readonly>
                        </div>
                        <!-- Fecha fin del análisis -->
                        <div class="col-md-4">
                            <label for="fecha_fin_analisis">Fecha fin del Análisis</label>
                            <input type="text" class="form-control form-control-sm" id="fecha_fin_analisis"
                                value="{{ $analysis->fecha_fin_analisis ? $analysis->fecha_fin_analisis->format('d/m/Y') : 'N/A' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
<!-- CONTROLES DE CALIDAD ANALÍTICOS -->
@if(isset($controles_analiticos) && !empty($controles_analiticos))
<div class="card mt-3 border-0 shadow-none">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0">Controles de Calidad Analíticos</h6>
    </div>
    <div class="card-body p-3">
        @foreach($controles_analiticos as $index => $control)
        <div class="border p-3 mb-3 rounded">
            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">
                Controles del Análisis {{ $index + 1 }}
            </h6>
            
            <!-- Muestra Fortificada -->
            @if(isset($control['masa_suelo']) || isset($control['masa_agua']) || isset($control['masa_suelo_seco']) || 
                isset($control['humedad_fortificada_teorica']) || isset($control['humedad_obtenida']) || 
                isset($control['humedad_fortificada']) || isset($control['recuperacion']) || isset($control['identificacion_mf']))
            <div class="mb-3">
                <h6 class="text-primary">Muestra Fortificada</h6>
                <div class="row g-3">
                    @if(isset($control['masa_suelo']))
                    <div class="col-md-3">
                        <label class="small">Masa de suelo (g)</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['masa_suelo']) ? number_format($control['masa_suelo'], 2) : $control['masa_suelo'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['masa_agua']))
                    <div class="col-md-3">
                        <label class="small">Masa de agua adicionada (g)</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['masa_agua']) ? number_format($control['masa_agua'], 2) : $control['masa_agua'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['masa_suelo_seco']))
                    <div class="col-md-3">
                        <label class="small">Masa de suelo seco (g)</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['masa_suelo_seco']) ? number_format($control['masa_suelo_seco'], 2) : $control['masa_suelo_seco'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['humedad_fortificada_teorica']))
                    <div class="col-md-3">
                        <label class="small">% Humedad fortificada teórica</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['humedad_fortificada_teorica']) ? number_format($control['humedad_fortificada_teorica'], 2) : $control['humedad_fortificada_teorica'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['identificacion_mf']))
                    <div class="col-md-3">
                        <label class="small">Identificación de la Muestra</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['identificacion_mf'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['humedad_obtenida']))
                    <div class="col-md-3">
                        <label class="small">% Humedad obtenida en la muestra</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['humedad_obtenida']) ? number_format($control['humedad_obtenida'], 2) : $control['humedad_obtenida'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['humedad_fortificada']))
                    <div class="col-md-3">
                        <label class="small">% Humedad muestra fortificada</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['humedad_fortificada']) ? number_format($control['humedad_fortificada'], 2) : $control['humedad_fortificada'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['recuperacion']))
                    <div class="col-md-2">
                        <label class="small">%Recuperacion</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['recuperacion']) ? number_format($control['recuperacion'], 2) : $control['recuperacion'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['aceptable_fortificada']))
                    <div class="col-md-1">
                        <label class="small">Aceptable</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['aceptable_fortificada'] }}" readonly>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Muestra Referencia -->
            @if(isset($control['identificacion_mr']) || isset($control['valor_referencia']) || 
                isset($control['valor_obtenido']) || isset($control['recuperacion_referencia']) || 
                isset($control['aceptable_referencia']))
            <div class="mb-3">
                <h6 class="text-primary">Muestra Referencia</h6>
                <div class="row g-3">
                    @if(isset($control['identificacion_mr']))
                    <div class="col-md-3">
                        <label class="small">Identificación de Muestra</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['identificacion_mr'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['valor_referencia']))
                    <div class="col-md-3">
                        <label class="small">Valor Referencia % Humedad</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['valor_referencia']) ? number_format($control['valor_referencia'], 2) : $control['valor_referencia'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['valor_obtenido']))
                    <div class="col-md-3">
                        <label class="small">Valor Obtenido % Humedad</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['valor_obtenido']) ? number_format($control['valor_obtenido'], 2) : $control['valor_obtenido'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['recuperacion']))
                    <div class="col-md-2">
                        <label class="small">%REC</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['recuperacion']) ? number_format($control['recuperacion'], 2) : $control['recuperacion'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['aceptable_referencia']))
                    <div class="col-md-1">
                        <label class="small">Aceptable</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['aceptable_referencia'] }}" readonly>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Duplicado Muestra -->
            @if(isset($control['identificacion_dm']) || isset($control['humedad_replica_1']) || 
                isset($control['humedad_replica_2']) || isset($control['dpr']) || isset($control['aceptable_duplicado']))
            <div class="mb-3">
                <h6 class="text-primary">Duplicado Muestra</h6>
                <div class="row g-3">
                    @if(isset($control['identificacion_dm']))
                    <div class="col-md-3">
                        <label class="small">Identificación de Muestra</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['identificacion_dm'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['humedad_replica_1']))
                    <div class="col-md-3">
                        <label class="small">% Humedad Réplica 1</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['humedad_replica_1']) ? number_format($control['humedad_replica_1'], 2) : $control['humedad_replica_1'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['humedad_replica_2']))
                    <div class="col-md-3">
                        <label class="small">% Humedad Réplica 2</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['humedad_replica_2']) ? number_format($control['humedad_replica_2'], 2) : $control['humedad_replica_2'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['dpr']))
                    <div class="col-md-2">
                        <label class="small">% DPR</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['dpr']) ? number_format($control['dpr'], 2) : $control['dpr'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['aceptable_duplicado']))
                    <div class="col-md-1">
                        <label class="small">Aceptable</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['aceptable_duplicado'] }}" readonly>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Blanco del método -->
            @if(isset($control['identificacion_bm']) || isset($control['resultado_blanco']) || 
                isset($control['limite_cuantificacion_metodo']) || isset($control['rango_metodo']) || 
                isset($control['aceptable_blanco']))
            <div class="mb-3">
                <h6 class="text-primary">Blanco del método</h6>
                <div class="row g-3">
                    @if(isset($control['identificacion_bm']))
                    <div class="col-md-3">
                        <label class="small">Identificación</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['identificacion_bm'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['resultado']))
                    <div class="col-md-3">
                        <label class="small">Resultado</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['resultado']) ? number_format($control['resultado'], 2) : $control['resultado'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['limite_cuantificacion_metodo']))
                    <div class="col-md-3">
                        <label class="small">Límite de Cuantificación del Método (LCM)</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ is_numeric($control['limite_cuantificacion_metodo']) ? number_format($control['limite_cuantificacion_metodo'], 2) : $control['limite_cuantificacion_metodo'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['rango_metodo']))
                    <div class="col-md-2">
                        <label class="small">Rango del Método</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['rango_metodo'] }}" readonly>
                    </div>
                    @endif
                    @if(isset($control['aceptable_blanco']))
                    <div class="col-md-1">
                        <label class="small">Aceptable</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $control['aceptable_blanco'] }}" readonly>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if(isset($control['observaciones']))
            <div class="mt-3">
                <label class="small">Observaciones</label>
                <textarea class="form-control form-control-sm" rows="2" readonly>{{ $control['observaciones'] }}</textarea>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@else
<div class="alert alert-warning mt-3">
    <i class="fas fa-exclamation-triangle"></i>
    No se encontraron controles de calidad analíticos para este análisis.
</div>
@endif

<!-- REGISTRO DE MUESTRAS -->
<div class="card mt-3 border-0 shadow-none">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0">Registro de Muestras - Todos los Análisis del Servicio</h6>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered text-center" id="tablaMuestras">
                <thead class="thead-light">
                    <tr>
                        <th>Consecutivo</th>
                        <th>Código interno</th>
                        <th>Peso Cápsula (g)</th>
                        <th>Peso Muestra (g)</th>
                        <th>Peso Cápsula + Muestra húmeda (g)</th>
                        <th>Peso Cápsula + Muestra seca (g)</th>
                        <th>% Humedad (g/100g)</th>
                        <th>Observaciones</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="muestras-body">
                    @forelse($analisis_completos ?? [$analysis] as $analisis)
                    <tr class="muestra-fila">
                        <td>
                            <strong>{{ $analisis['consecutivo_no'] ?? $analisis->consecutivo_no }}</strong>
                        </td>
                        <td>
                            {{ $analisis['codigo_interno'] ?? $analisis->codigo_interno ?? 'N/A' }}
                        </td>
                        <td>
                            {{ is_numeric($analisis['peso_capsula'] ?? $analisis->peso_capsula) ? number_format($analisis['peso_capsula'] ?? $analisis->peso_capsula, 2) : ($analisis['peso_capsula'] ?? $analisis->peso_capsula ?? 'N/A') }}
                        </td>
                        <td>
                            {{ is_numeric($analisis['peso_muestra'] ?? $analisis->peso_muestra) ? number_format($analisis['peso_muestra'] ?? $analisis->peso_muestra, 2) : ($analisis['peso_muestra'] ?? $analisis->peso_muestra ?? 'N/A') }}
                        </td>
                        <td>
                            {{ is_numeric($analisis['peso_capsula_muestra_humedad'] ?? $analisis->peso_capsula_muestra_humedad) ? number_format($analisis['peso_capsula_muestra_humedad'] ?? $analisis->peso_capsula_muestra_humedad, 2) : ($analisis['peso_capsula_muestra_humedad'] ?? $analisis->peso_capsula_muestra_humedad ?? 'N/A') }}
                        </td>
                        <td>
                            {{ is_numeric($analisis['peso_capsula_muestra_seca'] ?? $analisis->peso_capsula_muestra_seca) ? number_format($analisis['peso_capsula_muestra_seca'] ?? $analisis->peso_capsula_muestra_seca, 2) : ($analisis['peso_capsula_muestra_seca'] ?? $analisis->peso_capsula_muestra_seca ?? 'N/A') }}
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{ is_numeric($analisis['porcentaje_humedad'] ?? $analisis->porcentaje_humedad) ? number_format($analisis['porcentaje_humedad'] ?? $analisis->porcentaje_humedad, 2) : ($analisis['porcentaje_humedad'] ?? $analisis->porcentaje_humedad ?? 'N/A') }}%
                            </span>
                        </td>
                        <td>
                            {{ $analisis['observaciones'] ?? $analisis->observaciones ?? 'N/A' }}
                        </td>
                        <td>
                            @php
                                $status = $analisis['review_status'] ?? $analisis->review_status ?? 'pending';
                                $statusClass = $status == 'pending' ? 'warning' : ($status == 'approved' ? 'success' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay análisis de humedad registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



            <!-- BOTONES DE ACCIÓN -->
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a la Lista
                    </a>
                    
                    @if($analysis->review_status == 'pending')
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                        <i class="fas fa-check"></i> Aprobar Servicio
                    </button>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times"></i> Rechazar Servicio
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de Aprobación -->
@if($analysis->review_status === 'pending')
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
                        <p>¿Está seguro de aprobar este análisis de humedad?</p>
                        <div class="form-group">
                            <label for="approval_notes">Observaciones (opcional):</label>
                            <textarea class="form-control" id="approval_notes" name="observations" rows="3" placeholder="Ingrese observaciones opcionales..."></textarea>
                            <input type="hidden" name="analysis_type" value="humidity">
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
                                     placeholder="Ingrese el motivo del rechazo..."
                                     required>{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <input type="hidden" name="analysis_type" value="humidity">
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
    // Validación del formulario de rechazo
    $('#rejectForm').on('submit', function(e) {
        const reason = $('#rejectReason').val().trim();
        if (!reason) {
            e.preventDefault();
            $('#rejectReason').addClass('is-invalid');
            const $fb = $('#rejectReason').siblings('.invalid-feedback');
            if ($fb.length) { 
                $fb.text('El motivo del rechazo es obligatorio.'); 
            }
        }
    });
    
    // Limpiar validación cuando se escribe en el campo
    $('#rejectReason').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@endsection