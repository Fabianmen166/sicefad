@extends('lscefa::layouts.technical')

@section('title', 'Gestión de Análisis de Acidez')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.index') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Gestión de Acidez</li>
        </ol>
    </nav>

    <!-- Alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Sección de Análisis Pendientes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">Análisis de Acidez Pendientes</h5>
            @if($pendingAnalyses->isNotEmpty())
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">
                        Seleccionar todos
                    </label>
                </div>
            @endif
        </div>
        <div class="card-body p-0">
            <form action="{{ route('lscefa.technical.analyses.acidity.batchProcess') }}" method="POST" id="batchForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50px"></th>
                                <th>ID Proceso</th>
                                <th>Descripción</th>
                                <th>Fecha de Recepción</th>
                                <th>Cantidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingAnalyses as $analysis)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               name="selected_analyses[]" 
                                               value="{{ $analysis->process_id }}_{{ $analysis->serviceProcessDetails->first()->service_id }}"
                                               class="analysis-checkbox form-check-input">
                                    </td>
                                    <td>{{ $analysis->process_id }}</td>
                                    <td>{{ $analysis->description ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($analysis->reception_date)->format('d/m/Y') }}</td>
                                    <td>0</td>
                                    <td>
                                        <a href="{{ route('lscefa.technical.analyses.acidity.process', [
                                            'processId' => $analysis->process_id, 
                                            'serviceId' => $analysis->serviceProcessDetails->first()->service_id
                                        ]) }}" 
                                           class="btn btn-primary btn-sm"
                                           title="Procesar individualmente">
                                            <i class="fas fa-vial"></i> Procesar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        @if(isset($serviceMessage))
                                            {{ $serviceMessage }}
                                        @else
                                            No hay análisis de acidez pendientes
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($pendingAnalyses->isNotEmpty())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div class="form-text">
                            Seleccionados: <span id="selectedCount">0</span>/{{ $pendingAnalyses->count() }}
                        </div>
                        <button type="submit" class="btn btn-success" id="processBatchBtn" disabled>
                            <i class="fas fa-vial"></i> Procesar selección (<span id="selectedBadge">0</span>)
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Sección de Análisis Devueltos -->
    @if(isset($acidityAnalyses) && $acidityAnalyses->isNotEmpty())
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary">Análisis Devueltos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID Proceso</th>
                                <th>Descripción</th>
                                <th>Fecha de Recepción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acidityAnalyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->process_id }}</td>
                                    <td>{{ $analysis->description ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($analysis->reception_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-warning">Devuelto</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('lscefa.technical.analyses.acidity.process', [
                                            'processId' => $analysis->process_id, 
                                            'serviceId' => $analysis->serviceProcessDetails->first()->service_id
                                        ]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
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
    
    .table > :not(:first-child) {
        border-top: none;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    .breadcrumb {
        background-color: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: ">";
    }
    
    .alert {
        border: none;
        border-radius: 0.35rem;
    }
    
    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0;
    }
    
    #selectedBadge {
        background-color: #28a745;
        padding: 0.25rem 0.5rem;
        border-radius: 50%;
        font-size: 0.8rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.analysis-checkbox');
    const processBtn = document.getElementById('processBatchBtn');
    const selectedCount = document.getElementById('selectedCount');
    const selectedBadge = document.getElementById('selectedBadge');
    
    // Función para actualizar el contador y el botón
    function updateSelection() {
        const checked = document.querySelectorAll('.analysis-checkbox:checked');
        const count = checked.length;
        
        // Actualizar contadores
        selectedCount.textContent = count;
        selectedBadge.textContent = count;
        
        // Actualizar botón
        if (processBtn) {
            processBtn.disabled = count === 0;
        }
        
        // Actualizar "Seleccionar todos"
        if (selectAll) {
            selectAll.checked = count === checkboxes.length;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;
        }
    }
    
    // Evento para "Seleccionar todos"
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelection();
        });
    }
    
    // Eventos para checkboxes individuales
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });
    
    // Inicializar
    updateSelection();
});
</script>
@endsection