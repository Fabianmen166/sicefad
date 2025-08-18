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
                <div class="col-md-4">
                    <p><strong>Equipo Utilizado:</strong> {{ $analysis->codigo_equipo ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Resolución Instrumental:</strong> {{ $analysis->serial_conductimetro ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Unidades de Reporte:</strong> {{ $analysis->serial_sonda_temperatura ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Controles Analíticos -->
    @if(isset($analysis->controles_analiticos))
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Controles Analíticos</h4>
            </div>
            <div class="card-body">
                @foreach($analysis->controles_analiticos as $control)
                    <div class="mb-4">
                        <h5>{{ ucfirst($control['tipo']) }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Valor Leído</th>
                                        <th>Aceptable</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $control['identificacion'] ?? 'N/A' }}</td>
                                        <td>{{ $control['valor_leido'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(isset($control['aceptable']))
                                                @if($control['aceptable'] === 'Aceptable')
                                                    <span class="badge bg-success">Aceptable</span>
                                                @else
                                                    <span class="badge bg-danger">No Aceptable</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $control['observaciones'] ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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
    @if(!empty($analysis->observaciones))
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Observaciones del Analista</h4>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded">
                    {{ $analysis->observaciones }}
                </div>
            </div>
        </div>
    @endif

    <!-- Acciones de Revisión -->
    @if($detail->status === 'completed')
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Acciones de Revisión</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form method="post" action="{{ route('lscefa.quality.reviews.accept', $detail) }}" class="mb-3">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Observaciones (opcional)</label>
                                <textarea name="observations" class="form-control" rows="3" placeholder="Notas internas (opcional)"></textarea>
                            </div>
                            <button class="btn btn-success w-100" type="submit">
                                <i class="fas fa-check me-1"></i> Aprobar Análisis
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="post" action="{{ route('lscefa.quality.reviews.reject', $detail) }}" onsubmit="return confirm('¿Está seguro de rechazar este análisis?');">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Motivo del Rechazo</label>
                                <textarea name="observations" class="form-control" rows="3" required placeholder="Indique el motivo del rechazo"></textarea>
                            </div>
                            <button class="btn btn-danger w-100" type="submit">
                                <i class="fas fa-times me-1"></i> Rechazar Análisis
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
