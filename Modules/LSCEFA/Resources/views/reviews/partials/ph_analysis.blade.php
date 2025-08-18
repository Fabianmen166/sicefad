@php
    $analysis = $analysis ?? null;
    $detail = $detail ?? null;
    $index = $index ?? 1;
    $readonly = $readonly ?? true;
    $process = $detail->process ?? null;
    $quote = $process->quote ?? null;
    $customer = $quote->customer ?? null;
@endphp

<div class="analysis-section mb-4">
    <div class="analysis-header">
        <h4 class="mb-0">
            <i class="fas fa-flask"></i> 
            Análisis #{{ $index }} - {{ $detail->service->descripcion ?? 'Análisis de pH' }}
            <span class="badge badge-{{ $detail->status === 'rejected' ? 'danger' : ($detail->status === 'approved' ? 'success' : 'warning') }} float-right">
                {{ ucfirst($detail->status) }}
            </span>
        </h4>
    </div>
    
    <div class="analysis-body">
        <!-- Información General -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Consecutivo No.:</strong> {{ $analysis->consecutivo_no ?? 'N/A' }}</p>
                        <p><strong>Fecha de Análisis:</strong> {{ $analysis->fecha_analisis ?? 'N/A' }}</p>
                        <p><strong>Código de Probeta:</strong> {{ $analysis->codigo_probeta ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Equipo Utilizado:</strong> {{ $analysis->codigo_equipo ?? 'N/A' }}</p>
                        <p><strong>Serial del Electrodo:</strong> {{ $analysis->serial_electrodo ?? 'N/A' }}</p>
                        <p><strong>Serial Sonda de Temperatura:</strong> {{ $analysis->serial_sonda_temperatura ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Analista:</strong> {{ $analysis->user->name ?? 'N/A' }}</p>
                        <p><strong>Cliente:</strong> {{ $customer->applicant ?? 'N/A' }}</p>
                        <p><strong>Proceso:</strong> {{ $process->item_code ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles Analíticos -->
        @if(!empty($analysis->controles_analiticos) && is_array($analysis->controles_analiticos))
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Controles Analíticos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Identificación</th>
                                    <th>Valor de Referencia</th>
                                    <th>Valor Obtenido</th>
                                    <th>% Recuperación</th>
                                    <th>Rango de Aceptación</th>
                                    <th>Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis->controles_analiticos as $control)
                                    @if(is_array($control))
                                    <tr>
                                        <td>{{ ucfirst($control['tipo'] ?? 'N/A') }}</td>
                                        <td>{{ $control['identificacion'] ?? 'N/A' }}</td>
                                        <td>{{ $control['valor_referencia'] ?? 'N/A' }}</td>
                                        <td>{{ $control['valor_obtenido'] ?? 'N/A' }}</td>
                                        <td>{{ $control['porcentaje_recuperacion'] ?? 'N/A' }}%</td>
                                        <td>{{ $control['rango_aceptacion'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(isset($control['resultado']) && $control['resultado'] === 'Aceptable')
                                                <span class="badge bg-success">Aceptable</span>
                                            @else
                                                <span class="badge bg-danger">No Aceptable</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ítems de Ensayo -->
        @if(!empty($analysis->items_ensayo) && is_array($analysis->items_ensayo))
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ítems de Ensayo</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Identificación</th>
                                    <th>Peso (g)</th>
                                    <th>Volumen H₂O (mL)</th>
                                    <th>Temperatura (°C)</th>
                                    <th>Valor de pH</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis->items_ensayo as $item)
                                    @if(is_array($item))
                                    <tr>
                                        <td>{{ $item['identificacion'] ?? 'N/A' }}</td>
                                        <td>{{ $item['peso'] ?? 'N/A' }}</td>
                                        <td>{{ $item['volumen_agua'] ?? 'N/A' }}</td>
                                        <td>{{ $item['temperatura'] ?? 'N/A' }}</td>
                                        <td>{{ $item['valor_ph'] ?? 'N/A' }}</td>
                                        <td>{{ $item['observaciones'] ?? 'N/A' }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Precisión Analítica -->
        @if(!empty($analysis->precision_analitica) && is_array($analysis->precision_analitica))
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Precisión Analítica</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Parámetro</th>
                                    <th>Valor</th>
                                    <th>Unidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analysis->precision_analitica as $key => $value)
                                    @if(!is_array($value))
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                            <td>{{ $value }}</td>
                                            <td>
                                                @if($key === 'desviacion_estandar' || $key === 'coeficiente_variacion')
                                                    %
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Observaciones -->
        @if(!empty($analysis->observaciones))
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Observaciones del Analista</h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <textarea class="form-control" rows="3" readonly>{{ $analysis->observaciones }}</textarea>
                    </div>
                </div>
            </div>
        @endif

        <!-- Acciones de Revisión Individual -->
        @if($detail->status === 'completed' && !$readonly)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Acciones de Revisión</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="{{ route('lscefa.quality.reviews.accept', $detail) }}" class="mb-3">
                                @csrf
                                <div class="form-group">
                                    <label>Observaciones (opcional)</label>
                                    <textarea name="observations" class="form-control" rows="2" placeholder="Ingrese observaciones adicionales (opcional)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> Aprobar Análisis
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="post" action="{{ route('lscefa.quality.reviews.reject', $detail) }}" onsubmit="return confirm('¿Está seguro de rechazar este análisis?')">
                                @csrf
                                <div class="form-group">
                                    <label>Motivo del Rechazo <span class="text-danger">*</span></label>
                                    <textarea name="observations" class="form-control" rows="2" required placeholder="Explique el motivo del rechazo"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-times"></i> Rechazar Análisis
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
