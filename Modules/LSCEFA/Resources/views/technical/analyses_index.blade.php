@extends('lscefa::layouts.technical')

@section('title', 'Análisis Técnicos')

@section('content')
<div class="container py-4">
    <!-- Enlaces a análisis específicos -->
    <div class="card shadow">
        <h5 class="card-header">Procesos Técnicos Pendientes</h5>
        <div class="card-body">
            @if ($processes->isEmpty())
                <div class="alert alert-info">No hay procesos pendientes para análisis técnico.</div>
            @else
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Código de Item</th>
                            <th>Servicios Pendientes</th>
                            <th>Servicios Realizados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($processes as $process)
                            <tr>
                                <td>{{ $process->item_code }}</td>
                                <td>
                                    @if(empty($pending[$process->process_id]))
                                        <span class="text-gray-500">Ningún servicio pendiente</span>
                                    @else
                                        <ul class="list-unstyled">
                                            @foreach ($pending[$process->process_id] as $spd)
                                                <li>{{ $spd->service->descripcion ?? 'Servicio' }} (ID: {{ $spd->service_id }})</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>
                                    @if(empty($completed[$process->process_id]))
                                        <span class="text-gray-500">Ningún servicio realizado</span>
                                    @else
                                        <ul class="list-unstyled">
                                            @foreach ($completed[$process->process_id] as $spd)
                                                <li>{{ $spd->service->descripcion ?? 'Servicio' }} (ID: {{ $spd->service_id }})</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $processes->links() }}
            @endif
        </div>
    </div>

    <!-- Tabla de Análisis Devueltos -->
    <div class="card shadow mt-4">
        <h5 class="card-header">Análisis Devueltos</h5>
        <div class="card-body">
            @php
                $returnedDetails = collect();
                foreach ($returned as $processId => $details) {
                    foreach ($details as $spd) {
                        $returnedDetails->push($spd);
                    }
                }
            @endphp
            @if ($returnedDetails->isEmpty())
                <div class="alert alert-info">No hay análisis devueltos para mostrar.</div>
            @else
                <table class="table table-bordered table-hover">
                    <thead>
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
                                <td>{{ $spd->process_id }}</td>
                                <td>{{ $spd->service->descripcion ?? 'Servicio' }}</td>
                                <td>{{ $spd->created_at }}</td>
                                <td><span class="badge badge-danger">Rechazado</span></td>
                                <td>
                                    <a href="#" class="btn btn-warning btn-sm disabled">Corregir</a>
                                    <a href="#" class="btn btn-info btn-sm disabled">Descargar Reporte</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection 