@extends('lscefa::layouts.technical')

@section('title', 'Análisis Técnicos')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Enlaces rápidos a análisis específicos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="m-0 font-weight-bold text-primary">Análisis Específicos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                                                  <div class="col-md-3 mb-3">
                              <a href="{{ route('lscefa.ph_analysis.index') }}" class="btn btn-outline-primary btn-block">
                                  <i class="fas fa-flask me-2"></i>Análisis de pH
                              </a>
                          </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('lscefa.technical.analyses.phosphorus.index') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-flask me-2"></i>Análisis de Fósforo
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('lscefa.technical.analyses.texture.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-layer-group me-2"></i>Análisis de Textura
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('lscefa.technical.analyses.exchangeable_bases.index') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-flask me-2"></i>Bases Cambiables
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enlaces a análisis específicos -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Procesos Técnicos Pendientes</h5>
                </div>
                <div class="card-body p-0">
                    @if ($processes->isEmpty())
                        <div class="alert alert-info m-4">No hay procesos pendientes para análisis técnico.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código de Item</th>
                                        <th>Servicios Pendientes</th>
                                        <th>Servicios Realizados</th>
                                        <th>Fecha de Entrega</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($processes as $process)
                                        <tr>
                                            <td class="align-middle">{{ $process->item_code }}</td>
                                            <td class="align-middle">
                                                @if(empty($pending[$process->process_id]))
                                                    <span class="text-muted">Ningún servicio pendiente</span>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($pending[$process->process_id] as $spd)
                                                            <li>{{ $spd->service->descripcion ?? 'Servicio' }} (ID: {{ $spd->service_id }})</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if(empty($completed[$process->process_id]))
                                                    <span class="text-muted">Ningún servicio realizado</span>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($completed[$process->process_id] as $spd)
                                                            <li>{{ $spd->service->descripcion ?? 'Servicio' }} (ID: {{ $spd->service_id }})</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @php
                                                    $date = !empty($process->delivery_date) ? \Carbon\Carbon::parse($process->delivery_date) : null;
                                                    $daysLeft = $date ? \Carbon\Carbon::now()->startOfDay()->diffInDays($date->startOfDay(), false) : null;
                                                    $badgeClass = 'bg-secondary';
                                                    if (!is_null($daysLeft)) {
                                                        if ($daysLeft < 0) {
                                                            $badgeClass = 'bg-danger';
                                                        } elseif ($daysLeft <= 2) {
                                                            $badgeClass = 'bg-danger';
                                                        } elseif ($daysLeft <= 5) {
                                                            $badgeClass = 'bg-orange';
                                                        } elseif ($daysLeft <= 7) {
                                                            $badgeClass = 'bg-warning text-dark';
                                                        } else {
                                                            $badgeClass = 'bg-success';
                                                        }
                                                    }
                                                @endphp
                                                @if($date)
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ $date->format('d/m/Y') }}
                                                        @if(!is_null($daysLeft)) ({{ $daysLeft }} días) @endif
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $processes->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabla de Análisis Devueltos -->
            <div class="card shadow mt-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Análisis Devueltos</h5>
                </div>
                <div class="card-body p-0">
                    @php
                        $returnedDetails = collect();
                        foreach ($returned as $processId => $details) {
                            foreach ($details as $spd) {
                                $returnedDetails->push($spd);
                            }
                        }
                    @endphp
                    @if ($returnedDetails->isEmpty())
                        <div class="alert alert-info m-4">No hay análisis devueltos para mostrar.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Servicio</th>
                                        <th>Fecha de Creación</th>
                                        <th>Estado de Revisión</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($returnedDetails as $spd)
                                        <tr>
                                            <td class="align-middle">{{ $spd->process_id }}</td>
                                            <td class="align-middle">{{ $spd->service->descripcion ?? 'Servicio' }}</td>
                                            <td class="align-middle">{{ $spd->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="align-middle">
                                                <span class="badge bg-danger">Rechazado</span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="#" class="btn btn-warning btn-sm" title="Corregir">
                                                    <i class="fas fa-edit"></i> Corregir
                                                </a>
                                                <a href="#" class="btn btn-info btn-sm" title="Descargar Reporte">
                                                    <i class="fas fa-download"></i> Reporte
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Ajustes específicos para mejorar el espaciado */
    .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
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
    
    /* Helper para naranja (Bootstrap usa warning como amarillo) */
    .bg-orange { background-color: #fd7e14 !important; color: #fff !important; }
</style>
@endsection