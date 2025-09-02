@extends('lscefa::layouts.master')

@section('title', 'Historial de Consecutivos')

@push('styles')
    <style>
        .analysis-card { margin-bottom: 20px; border-left: 4px solid #6c757d; }
        .analysis-card .card-header { background-color: #f8f9fa; }
        .analysis-badge { font-size: 0.8rem; padding: 0.35em 0.65em; }
        .analysis-type-badge { font-size: 0.8rem; padding: 0.35em 0.65em; margin-left: 0.5rem; }
        .filter-form { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .filter-form .form-control { margin-bottom: 10px; }
        .filter-form .btn { margin-left: 5px; }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Historial de Consecutivos</h1>
                        <small class="text-muted">Listado de todos los análisis realizados (solo lectura)</small>
                    </div>
                    <div class="col-sm-6">
                        <form action="{{ route('lscefa.quality.reviews.history') }}" method="GET" class="float-right filter-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="type" class="form-control">
                                        <option value="" {{ request('type') ? '' : 'selected' }}>Todos</option>
                                        <option value="ph" {{ request('type') === 'ph' ? 'selected' : '' }}>pH</option>
                                        <option value="conductivity" {{ request('type') === 'conductivity' ? 'selected' : '' }}>Conductividad</option>
                                        <option value="humidity" {{ request('type') === 'humidity' ? 'selected' : '' }}>Humedad</option>
                                        <option value="phosphorus" {{ request('type') === 'phosphorus' ? 'selected' : '' }}>Fósforo</option>
                                        <option value="texture" {{ request('type') === 'texture' ? 'selected' : '' }}>Textura</option>
                                        <option value="boron" {{ request('type') === 'boron' ? 'selected' : '' }}>Boro</option>
                                        <option value="micronutrients" {{ request('type') === 'micronutrients' ? 'selected' : '' }}>Micronutrientes</option>
                                        <option value="cationic" {{ request('type') === 'cationic' ? 'selected' : '' }}>Intercambio Catiónico</option>
                                        <option value="carbon" {{ request('type') === 'carbon' ? 'selected' : '' }}>Carbono</option>
                                        <option value="acidity" {{ request('type') === 'acidity' ? 'selected' : '' }}>Acidez</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="muestra" class="form-control" placeholder="Filtrar por muestra..." value="{{ request('muestra') }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" name="q" class="form-control" placeholder="Buscar por consecutivo o cliente..." value="{{ request('q') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('lscefa.quality.reviews.history') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times"></i> Limpiar filtros
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @forelse($allAnalyses as $analysis)
                    @php
                        $typeClasses = [
                            'ph' => 'bg-primary',
                            'conductivity' => 'bg-info',
                            'turbidity' => 'bg-warning',
                            'hardness' => 'bg-secondary',
                            'texture' => 'bg-success',
                            'phosphorus' => 'bg-success',
                            'humidity' => 'bg-dark',
                            'boron' => 'bg-info',
                            'micronutrients' => 'bg-warning',
                            'cationic' => 'bg-secondary',
                            'carbon' => 'bg-danger',
                            'acidity' => 'bg-warning',
                        ];
                        $typeTexts = [
                            'ph' => 'pH',
                            'conductivity' => 'Conductividad',
                            'turbidity' => 'Turbidez',
                            'hardness' => 'Dureza',
                            'texture' => 'Textura',
                            'phosphorus' => 'Fósforo',
                            'humidity' => 'Humedad',
                            'boron' => 'Boro',
                            'micronutrients' => 'Micronutrientes',
                            'cationic' => 'Intercambio Catiónico',
                            'carbon' => 'Carbono',
                            'acidity' => 'Acidez',
                        ];
                        $itemsCount = count($analysis->items_ensayo ?? []);
                    @endphp

                    <div class="card analysis-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-flask mr-2"></i>
                                    Análisis #{{ $analysis->consecutivo_no ?? 'N/A' }}
                                    <span class="badge bg-dark analysis-type-badge">{{ $analysis->service_name ?? ucfirst($analysis->type) }}</span>
                                    @foreach ($analysis->analysis_types ?? [] as $type)
                                        @if (isset($typeClasses[$type]) && isset($typeTexts[$type]))
                                            <span class="badge {{ $typeClasses[$type] }} analysis-type-badge">{{ $typeTexts[$type] }}</span>
                                        @endif
                                    @endforeach
                                    @if (isset($analysis->fecha_analisis))
                                        <span class="badge bg-primary analysis-badge ml-2">{{ $analysis->fecha_analisis }}</span>
                                    @endif
                                </h3>
                                <div>
                                    <span class="badge bg-info analysis-badge">{{ $itemsCount }} ítem{{ $itemsCount != 1 ? 's' : '' }} de ensayo</span>
                                    @if (isset($analysis->analysis_count) && $analysis->analysis_count > 1)
                                        <span class="badge bg-secondary analysis-badge ml-1">{{ $analysis->analysis_count }} análisis combinados</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><strong>Muestra:</strong> {{ $analysis->codigo_probeta ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="{{ route('lscefa.quality.reviews.show', ['id' => $analysis->id]) }}?type={{ $analysis->type }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Revisar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        No hay análisis en el historial para los filtros aplicados.
                    </div>
                @endforelse

                <div class="d-flex justify-content-center mt-4">
                    @if($allAnalyses instanceof \Illuminate\Pagination\LengthAwarePaginator && $allAnalyses->total() > 0)
                        {{ $allAnalyses->appends(request()->query())->links() }}
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Historial de consecutivos cargado correctamente');
});
</script>
@endpush
