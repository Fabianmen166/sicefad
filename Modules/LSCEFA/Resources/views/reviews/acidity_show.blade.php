@extends('lscefa::layouts.master')

@section('title', 'Revisión de Análisis de Acidez')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lscefa.quality.reviews.index') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Revisión de Acidez</li>
        </ol>
    </nav>

    <!-- Información del Proceso -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-flask"></i> Análisis de Acidez - {{ $analysis->consecutivo_no ?? 'N/A' }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-info">Información del Cliente</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Cliente:</strong></td>
                            <td>{{ $customer->applicant ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Proceso:</strong></td>
                            <td>{{ $process->process_id ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Análisis:</strong></td>
                            <td>{{ $analysis->fecha_analisis ? \Carbon\Carbon::parse($analysis->fecha_analisis)->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Técnico:</strong></td>
                            <td>{{ $technicianName ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-info">Información del Análisis</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Método:</strong></td>
                            <td>{{ $analysis->nombre_metodo ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Equipo:</strong></td>
                            <td>{{ $analysis->equipo_utilizado ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Intervalo:</strong></td>
                            <td>{{ $analysis->intervalo_metodo ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Resolución:</strong></td>
                            <td>{{ $analysis->resolucion_instrumental ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados del Análisis -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-line"></i> Resultados del Análisis
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Identificación</th>
                            <th>Peso Muestra</th>
                            <th>Consumido Blanco</th>
                            <th>Consumido Muestra</th>
                            <th>Acidez</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($items_ensayo) && count($items_ensayo) > 0)
                            @foreach($items_ensayo as $item)
                                <tr>
                                    <td>{{ $item['identificacion'] ?? 'N/A' }}</td>
                                    <td>{{ $item['peso_muestra'] ?? 'N/A' }}</td>
                                    <td>{{ $item['consumido_blanco'] ?? 'N/A' }}</td>
                                    <td>{{ $item['consumido_muestra'] ?? 'N/A' }}</td>
                                    <td>{{ $item['acidez'] ?? 'N/A' }}</td>
                                    <td>{{ $item['observaciones'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <em>No hay ítems de ensayo disponibles</em>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Controles Analíticos -->
    @if(!empty($controles_analiticos) && count($controles_analiticos) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-vial"></i> Controles Analíticos
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Identificación</th>
                            <th>Valor Leído</th>
                            <th>Valor Esperado</th>
                            <th>Aceptable</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($controles_analiticos as $control)
                            <tr>
                                <td>{{ $control['identificacion'] ?? 'N/A' }}</td>
                                <td>{{ $control['valor_leido'] ?? 'N/A' }}</td>
                                <td>{{ $control['valor_esperado'] ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($control['aceptable']) && $control['aceptable'])
                                        <span class="badge badge-success">Sí</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </td>
                                <td>{{ $control['observaciones'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Muestra de Referencia -->
    @if(!empty($muestra_referencia) && count($muestra_referencia) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-certificate"></i> Muestra de Referencia
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Identificación</th>
                            <th>Lote</th>
                            <th>Peso</th>
                            <th>Consumido</th>
                            <th>Valor Leído</th>
                            <th>Valor Esperado</th>
                            <th>Aceptable</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($muestra_referencia as $referencia)
                            <tr>
                                <td>{{ $referencia['identificacion'] ?? 'N/A' }}</td>
                                <td>{{ $referencia['lote'] ?? 'N/A' }}</td>
                                <td>{{ $referencia['peso'] ?? 'N/A' }}</td>
                                <td>{{ $referencia['consumido'] ?? 'N/A' }}</td>
                                <td>{{ $referencia['valor_leido'] ?? 'N/A' }}</td>
                                <td>{{ $referencia['valor_esperado'] ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($referencia['aceptable']) && $referencia['aceptable'])
                                        <span class="badge badge-success">Sí</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </td>
                                <td>{{ $referencia['observaciones'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Precisión Analítica -->
    @if(!empty($precision_analitica) && count($precision_analitica) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-bar"></i> Precisión Analítica
            </h5>
        </div>
        <div class="card-body">
            @if(isset($precision_analitica['duplicados']) && !empty($precision_analitica['duplicados']))
                <h6>Duplicados</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Identificación</th>
                                <th>Valor 1</th>
                                <th>Valor 2</th>
                                <th>Diferencia</th>
                                <th>Diferencia %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($precision_analitica['duplicados'] as $duplicado)
                                <tr>
                                    <td>{{ $duplicado['identificacion'] ?? 'N/A' }}</td>
                                    <td>{{ $duplicado['valor1'] ?? 'N/A' }}</td>
                                    <td>{{ $duplicado['valor2'] ?? 'N/A' }}</td>
                                    <td>{{ $duplicado['diferencia'] ?? 'N/A' }}</td>
                                    <td>{{ $duplicado['diferencia_porcentual'] ?? 'N/A' }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(isset($precision_analitica['replicas']) && !empty($precision_analitica['replicas']))
                <h6 class="mt-3">Réplicas</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Identificación</th>
                                <th>Réplica 1</th>
                                <th>Réplica 2</th>
                                <th>Réplica 3</th>
                                <th>Promedio</th>
                                <th>Desv. Est.</th>
                                <th>CV %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($precision_analitica['replicas'] as $replica)
                                <tr>
                                    <td>{{ $replica['identificacion'] ?? 'N/A' }}</td>
                                    <td>{{ $replica['replica1'] ?? 'N/A' }}</td>
                                    <td>{{ $replica['replica2'] ?? 'N/A' }}</td>
                                    <td>{{ $replica['replica3'] ?? 'N/A' }}</td>
                                    <td>{{ $replica['promedio'] ?? 'N/A' }}</td>
                                    <td>{{ $replica['desviacion_estandar'] ?? 'N/A' }}</td>
                                    <td>{{ $replica['coeficiente_variacion'] ?? 'N/A' }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Estadísticas -->
    @if(!empty($estadisticas) && count($estadisticas) > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-calculator"></i> Estadísticas
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if(isset($estadisticas['promedio_acidez']))
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-primary">{{ number_format($estadisticas['promedio_acidez'], 2) }}</h4>
                        <p class="text-muted mb-0">Promedio de Acidez</p>
                    </div>
                </div>
                @endif
                @if(isset($estadisticas['min_acidez']))
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-success">{{ number_format($estadisticas['min_acidez'], 2) }}</h4>
                        <p class="text-muted mb-0">Valor Mínimo</p>
                    </div>
                </div>
                @endif
                @if(isset($estadisticas['max_acidez']))
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-warning">{{ number_format($estadisticas['max_acidez'], 2) }}</h4>
                        <p class="text-muted mb-0">Valor Máximo</p>
                    </div>
                </div>
                @endif
                @if(isset($estadisticas['total_muestras']))
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-info">{{ $estadisticas['total_muestras'] }}</h4>
                        <p class="text-muted mb-0">Total Muestras</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Acciones -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-tasks"></i> Acciones
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="analysis_type" value="acidity">
                        <div class="form-group">
                            <label for="observations_accept">Observaciones (opcional):</label>
                            <textarea class="form-control" id="observations_accept" name="observations" rows="3" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Aprobar Análisis
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST" id="rejectForm">
                        @csrf
                        <input type="hidden" name="analysis_type" value="acidity">
                        <div class="form-group">
                            <label for="observations_reject">Observaciones (requeridas):</label>
                            <textarea class="form-control" id="observations_reject" name="observations" rows="3" placeholder="Debe especificar el motivo del rechazo..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('¿Está seguro de rechazar este análisis?')">
                            <i class="fas fa-times"></i> Rechazar Análisis
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        color: #4e73df;
        background-color: #f8f9fc;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .breadcrumb {
        background-color: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
    }
    
    .alert {
        border: none;
        border-radius: 0.35rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario de rechazo
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const observations = document.getElementById('observations_reject').value.trim();
            if (observations.length < 3) {
                e.preventDefault();
                alert('Debe ingresar observaciones con al menos 3 caracteres para rechazar el análisis.');
                return false;
            }
        });
    }
});
</script>
@endsection
