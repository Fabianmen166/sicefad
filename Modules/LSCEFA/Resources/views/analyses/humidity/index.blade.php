@extends('layouts.app')

@section('title', 'Gestión de Análisis de Humedad')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Humedad</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Gestión de Humedad</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
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

            <!-- Pending Analyses Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Procesar Análisis de Humedad</h3>
                </div>
                <div class="card-body">
                    @if ($processes->isEmpty())
                        <p>No hay análisis de humedad pendientes para procesar.</p>
                    @else
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
                                @foreach ($processes as $process)
                                    @foreach($process->analyses as $analysis)
                                        <tr>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $process->customer->nombre ?? 'N/A' }}</td>
                                            <td>{{ $analysis->service->descripcion ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('humidity_analysis.humidity_analysis', ['processId' => $process->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    Realizar Análisis
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <form action="{{ route('humidity_analysis.process_all') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    Procesar Todos los Análisis
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Batch Humidity Analysis Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Análisis de Humedad Pendientes (Procesar en Lotes)</h3>
                </div>
                <div class="card-body">
                   
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
