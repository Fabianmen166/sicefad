@extends('lscefa::layouts.technical')

@section('title', 'Gestión de Análisis de pH')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Gestión de pH</li>
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

            <!-- Sección de Análisis de pH Pendientes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Análisis de pH Pendientes</h5>
                </div>
                <div class="card-body p-0">
                    @include('lscefa::partials.pending_ph_batch')
                </div>
            </div>

            <!-- Sección de Análisis Devueltos (si existen) -->
            @if(isset($phAnalyses) && $phAnalyses->isNotEmpty())
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-primary">Análisis Devueltos</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Servicio</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($phAnalyses as $analysis)
                                        <tr>
                                            <td class="align-middle">{{ $analysis->process->item_code ?? 'N/A' }}</td>
                                            <td class="align-middle">{{ $analysis->service->descripcion ?? 'N/A' }}</td>
                                            <td class="align-middle">
                                                <span class="badge bg-warning">Devuelto</span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="{{ route('lscefa.ph_analysis.ph_analysis', ['processId' => $analysis->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                   class="btn btn-primary btn-sm"
                                                   title="Ver detalles">
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
    </div>
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
</style>
@endsection