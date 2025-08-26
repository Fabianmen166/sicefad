@extends('lscefa::layouts.master')

@section('title', 'Revisión de Análisis')

@push('styles')
<style>
    .analysis-card {
        margin-bottom: 20px;
        border-left: 4px solid #007bff;
    }
    .analysis-card .card-header {
        background-color: #f8f9fa;
    }
    .analysis-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
    }
    .analysis-type-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
        margin-left: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Análisis Pendientes de Revisión</h1>
                </div>
                <div class="col-sm-6">
                    <form action="{{ route('lscefa.quality.reviews.index') }}" method="GET" class="float-right">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <select name="type" class="form-control">
                                    <option value="" {{ request('type') ? '' : 'selected' }}>Todos</option>
                                    <option value="ph" {{ request('type') === 'ph' ? 'selected' : '' }}>pH</option>
                                    <option value="conductivity" {{ request('type') === 'conductivity' ? 'selected' : '' }}>Conductividad</option>
                                    <option value="humidity" {{ request('type') === 'humidity' ? 'selected' : '' }}>Humedad</option>
                                    <option value="phosphorus" {{ request('type') === 'phosphorus' ? 'selected' : '' }}>Fósforo</option>
                                    <option value="texture" {{ request('type') === 'texture' ? 'selected' : '' }}>Textura</option>
                                    <option value="boron" {{ request('type') === 'boron' ? 'selected' : '' }}>Boro</option>
                                </select>
                            </div>
                            <input type="text" name="q" class="form-control" 
                                   placeholder="Buscar por número de consecutivo o cliente..." 
                                   value="{{ request('q') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
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

            @forelse($allAnalyses as $analysis)
                @php
                    // Obtener los tipos de análisis únicos para mostrar en los badges
                    $typeClasses = [
                        'ph' => 'bg-primary',
                        'conductivity' => 'bg-info',
                        'turbidity' => 'bg-warning',
                        'hardness' => 'bg-secondary',
                        'texture' => 'bg-success',
                        'phosphorus' => 'bg-success'
                    ];
                    
                    $typeTexts = [
                        'ph' => 'pH',
                        'conductivity' => 'Conductividad',
                        'turbidity' => 'Turbidez',
                        'hardness' => 'Dureza',
                        'texture' => 'Textura',
                        'phosphorus' => 'Fósforo'
                    ];
                    
                    // Contar ítems de ensayo
                    $itemsCount = count($analysis->items_ensayo ?? []);
                @endphp
                
                <div class="card analysis-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-flask mr-2"></i>
                                Análisis #{{ $analysis->consecutivo_no ?? 'N/A' }}
                                <span class="badge bg-dark analysis-type-badge">{{ $analysis->service_name ?? ucfirst($analysis->type) }}</span>
                                @foreach($analysis->analysis_types ?? [] as $type)
                                    @if(isset($typeClasses[$type]) && isset($typeTexts[$type]))
                                    <span class="badge {{ $typeClasses[$type] }} analysis-type-badge">
                                        {{ $typeTexts[$type] }}
                                    </span>
                                    @endif
                                @endforeach
                                @if(isset($analysis->fecha_analisis))
                                <span class="badge bg-primary analysis-badge ml-2">
                                    {{ $analysis->fecha_analisis }}
                                </span>
                                @endif
                            </h3>
                            <div>
                                <span class="badge bg-info analysis-badge">
                                    {{ $itemsCount }} ítem{{ $itemsCount != 1 ? 's' : '' }} de ensayo
                                </span>
                                @if(isset($analysis->analysis_count) && $analysis->analysis_count > 1)
                                <span class="badge bg-secondary analysis-badge ml-1">
                                    {{ $analysis->analysis_count }} análisis combinados
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Muestra:</strong> {{ $analysis->codigo_probeta ?? 'N/A' }}</p>
                                        <!-- <p><strong>Equipo:</strong> {{ $analysis->codigo_equipo ?? 'N/A' }}</p> -->
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Analista:</strong> {{ $analysis->user->name ?? 'N/A' }}</p>
                                        <!-- <p><strong>Cliente:</strong> {{ $analysis->customer->applicant ?? 'N/A' }}</p> -->
                                    </div>
                                </div>
                                
                                @if(!empty($analysis->items_ensayo) && count($analysis->items_ensayo) > 0)
                                <div class="mt-3">
                                    <p class="mb-1"><strong>Parámetros a revisar:</strong></p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @php
                                            $uniqueParams = collect($analysis->items_ensayo)
                                                ->pluck('parametro')
                                                ->unique()
                                                ->filter();
                                        @endphp
                                        @foreach($uniqueParams as $param)
                                            <span class="badge bg-light text-dark border">
                                                {{ $param }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-4 text-right">
                                @if(isset($analysis->review_status) && $analysis->review_status === 'approved')
                                    <a href="{{ route('lscefa.technical.analyses.texture.report', $analysis->id) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-download mr-1"></i> Descargar Reporte
                                    </a>
                                @else
                                    <a href="{{ route('lscefa.quality.reviews.show', $analysis->id) }}?type={{ $analysis->type ?? '' }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-eye mr-1"></i> Revisar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay análisis pendientes de revisión.
                </div>
            @endforelse

            <div class="d-flex justify-content-center mt-4">
                {{ $allAnalyses->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
