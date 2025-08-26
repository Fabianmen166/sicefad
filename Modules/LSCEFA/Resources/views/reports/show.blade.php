@extends('lscefa::layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Informe de resultados - Vista previa</h4>
        <a href="{{ route('lscefa.quality.reports.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Código interno ítem de ensayo:</strong> {{ $process->item_code ?? '—' }}
                </div>
                <div class="col-md-6 text-md-right">
                    <strong>Informe número:</strong> —
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Fecha emisión del informe:</strong> —
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Datos del cliente</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @php
                        $customer = optional(optional($process->quote)->customer);
                    @endphp
                    <div><strong>NIT/CC:</strong> {{ $customer->tax_id ?? '—' }}</div>
                    <div><strong>Solicitante:</strong> {{ $customer->applicant ?? '—' }}</div>
                    <div><strong>Contacto:</strong> {{ $customer->applicant ?? '—' }}</div>
                    <div><strong>Teléfono:</strong> {{ $customer->phone ?? '—' }}</div>
                    <div><strong>Correo electrónico:</strong> {{ $customer->email ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Lugar de muestreo:</strong> {{ $process->sampling_place ?? '—' }}</div>
                    <div><strong>Matriz:</strong> Suelo</div>
                    <div><strong>Descripción:</strong> {{ $process->description ?? '—' }}</div>
                    <div><strong>Fecha de muestreo:</strong> {{ $process->sampling_date ?: '—' }}</div>
                    <div><strong>Fecha de recepción:</strong> {{ $process->reception_date ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Resultados</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:25%">Ensayo</th>
                            <th style="width:15%">Resultado</th>
                            <th style="width:15%">Unidad</th>
                            <th style="width:15%">Fecha de Análisis</th>
                            <th style="width:20%">Técnica</th>
                            <th style="width:10%">Documento normativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['ensayo'] ?? '—' }}</td>
                                <td>{{ $row['resultado'] ?? '—' }}</td>
                                <td>{{ $row['unidad'] ?? '—' }}</td>
                                <td>{{ $row['fecha_analisis'] ?? '—' }}</td>
                                <td>{{ $row['tecnica'] ?? '—' }}</td>
                                <td>{{ $row['documento'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay resultados para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Botones de descarga simplificados -->
    <div class="card mt-3">
        <div class="card-header">Descargar Informes</div>
        <div class="card-body">
            <p>Servicios disponibles para descarga:</p>
            @foreach($process->serviceProcessDetails as $spd)
                @if($spd->status === 'approved')
                    @php 
                        $serviceName = strtolower($spd->service->descripcion ?? '');
                        $serviceNameNorm = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $serviceName);
                    @endphp
                    
                    <div class="mb-3">
                        <strong>{{ $spd->service->descripcion ?? 'N/A' }}</strong> - 
                        <span class="badge badge-success">Aprobado</span>
                        
                        @if(strpos($serviceName, 'ph') !== false && isset($spd->phAnalysis))
                            <a href="{{ route('lscefa.ph_analysis.download', $spd->phAnalysis->id) }}" class="btn btn-success btn-sm ml-2">
                                <i class="fas fa-download"></i> Descargar pH
                            </a>
                        @endif
                        
                        @if((strpos($serviceNameNorm, 'textura') !== false || strpos($serviceNameNorm, 'texture') !== false))
                            @php 
                                $textureAnalysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::where('process_id', $spd->process_id)
                                    ->where('service_id', $spd->service_id)
                                    ->first();
                            @endphp
                            @if($textureAnalysis)
                                <a href="{{ route('lscefa.technical.analyses.texture.download', $textureAnalysis->id) }}" class="btn btn-success btn-sm ml-2">
                                    <i class="fas fa-download"></i> Descargar Textura
                                </a>
                            @endif
                        @endif
                        
                        @if((strpos($serviceNameNorm, 'boro') !== false || strpos($serviceNameNorm, 'boron') !== false))
                            @php 
                                $boronAnalysis = \Modules\LSCEFA\Entities\BoronAnalysisDetail::where('process_id', $spd->process_id)
                                    ->where('service_id', $spd->service_id)
                                    ->first();
                            @endphp
                            @if($boronAnalysis)
                                <a href="{{ route('lscefa.technical.analyses.boron.download', $boronAnalysis->id) }}" class="btn btn-success btn-sm ml-2">
                                    <i class="fas fa-download"></i> Descargar Boro
                                </a>
                            @endif
                        @endif
                        
                        @if((strpos($serviceNameNorm, 'humedad') !== false || strpos($serviceNameNorm, 'humidity') !== false))
                            @php 
                                $humidityAnalysis = \Modules\LSCEFA\Entities\HumidityAnalysis::where('process_id', $spd->process_id)
                                    ->where('service_id', $spd->service_id)
                                    ->first();
                            @endphp
                            @if($humidityAnalysis)
                                <a href="{{ route('lscefa.technical.analyses.humidity.download', $humidityAnalysis->id) }}" class="btn btn-success btn-sm ml-2">
                                    <i class="fas fa-download"></i> Descargar Humedad
                                </a>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="mb-2">
                        <strong>{{ $spd->service->descripcion ?? 'N/A' }}</strong> - 
                        Estado: {{ $spd->status }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection