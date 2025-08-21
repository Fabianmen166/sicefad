@extends('lscefa::layouts.technical')

@section('title', 'Revisión de Análisis de pH')

@push('styles')
<style>
    .form-control[readonly] {
        background-color: #f8f9fa !important;
        border: 1px solid #ced4da;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .bg-light-gray {
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
                    <p class="mb-0"><strong>Consecutivo No.:</strong> {{ $analysis->consecutivo_no ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Proceso:</strong> {{ $detail->process->item_code ?? 'N/A' }}</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Información General -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Fecha de Análisis:</strong> {{ $analysis->fecha_analisis ?? 'N/A' }}</p>
                            <p><strong>Código de Probeta:</strong> {{ $analysis->codigo_probeta ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Equipo Utilizado:</strong> {{ $analysis->codigo_equipo ?? 'N/A' }}</p>
                            <p><strong>Serial del Electrodo:</strong> {{ $analysis->serial_electrodo ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Analista:</strong> {{ $analysis->user->name ?? 'N/A' }}</p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-{{ $detail->status === 'rejected' ? 'danger' : ($detail->status === 'approved' ? 'success' : 'warning') }}">
                                    {{ ucfirst($detail->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controles Analíticos -->
            @if(!empty($analysis->controles_analiticos))
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Controles Analíticos</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ítems de Ensayo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Ítems de Ensayo</h3>
                </div>
                <div class="card-body">
                    @if(!empty($items_ensayo) && count($items_ensayo) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
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
                                    @foreach($items_ensayo as $index => $item)
                                        <tr>
                                            <td>{{ $item['identificacion'] ?? 'N/A' }}</td>
                                            <td class="text-right">{{ number_format($item['peso'] ?? 0, 4) }}</td>
                                            <td class="text-right">{{ number_format($item['volumen_agua'] ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($item['temperatura'] ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($item['valor_leido'] ?? 0, 2) }}</td>
                                            <td>{{ $item['observaciones'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No se encontraron ítems de ensayo para mostrar.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Precisión Analítica -->
            @if(!empty($analysis->precision_analitica))
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Precisión Analítica</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
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
                    <div class="card-header">
                        <h3 class="card-title">Observaciones del Analista</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" rows="3" readonly>{{ $analysis->observaciones }}</textarea>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Acciones de Revisión -->
            @if($detail->status === 'completed')
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Acciones de Revisión</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="post" action="{{ route('lscefa.quality.reviews.accept', $detail) }}" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="analysis_type" value="ph">
                                    <div class="form-group">
                                        <label>Observaciones (opcional)</label>
                                        <textarea name="observations" class="form-control" rows="3" placeholder="Ingrese observaciones adicionales (opcional)"></textarea>
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
                                        <textarea name="observations" class="form-control" rows="3" required placeholder="Explique el motivo del rechazo"></textarea>
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
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Deshabilitar todos los campos del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select, button');
            inputs.forEach(input => {
                input.disabled = false; // Habilitar temporalmente para que el formulario se pueda enviar
                
                // Aplicar estilos a los campos de solo lectura
                if (input.readOnly) {
                    input.classList.add('bg-light');
                }
            });
        }
    });
</script>
@endpush
