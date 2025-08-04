@extends('lscefa::layouts.technical')

@section('title', 'Gestión de Análisis de Carbono Orgánico')

@section('contenido')
<div class="content-wrapper">
    <!-- Encabezado -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Carbono Orgánico</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="">Inicio</a></li>
                        <li class="breadcrumb-item active">Gestión de Carbono</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido Principal -->
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

            <!-- Sección de análisis individual -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Procesar Análisis de Carbono Orgánico</h3>
                </div>
                <div class="card-body">
                    @if($hasProcesses)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($processes as $process)
                                        @foreach($process->services as $service)
                                            @if(str_contains($service->descripcion, 'Carbono'))
                                                <tr>
                                                    <td>PRC-{{ $process->id }}</td>
                                                    <td>{{ $process->quote->customer->nombre ?? 'N/A' }}</td>
                                                    <td>{{ $service->descripcion }}</td>
                                                    <td>
                                                        <a href="{{ route('lscefa.technical.analyses.carbon.process', [$process->id, $service->id]) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            Realizar Análisis
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Botón para procesar todos -->
                        <div class="mt-3 text-right">
                            <button class="btn btn-green">
                                <i class="fas fa-play mr-2"></i> Procesar Todos los Análisis
                            </button>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p>No hay análisis de carbono orgánico pendientes para procesar.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección de análisis en lote -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Análisis de Carbono Pendientes (Procesar en Lotes)</h3>
                </div>
                <div class="card-body">
                    @if($hasProcesses)
                        <!-- Contenido cuando hay procesos para lotes -->
                        <div class="alert alert-info">
                            Seleccione los análisis que desea procesar en lote.
                        </div>
                        <!-- Aquí iría la tabla para selección múltiple -->
                    @else
                        <div class="alert alert-info">
                            No hay análisis disponibles para procesar en lote.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
