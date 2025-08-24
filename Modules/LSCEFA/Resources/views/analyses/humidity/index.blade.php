@extends('lscefa::layouts.technical_no_navbar')

@section('title', 'Gestión de Análisis de Humedad')

@section('content')
<div class="content-wrapper p-0 m-0" style="max-width: 100%;">
    <!-- Contenido Principal -->
    <section class="content p-0 m-0">
        <div class="container-fluid p-0 m-0">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Tabla de Procesos -->
            <div class="card border-0 shadow-none">
                <div class="card-header bg-teal text-white">
                    <h3 class="card-title mb-0">Lista de Procesos con Análisis de Humedad</h3>
                </div>
                <div class="card-body p-3">
                    @if ($processes->isEmpty())
                        <div class="text-center py-4">
                            <p class="mb-0 text-muted">No hay procesos con análisis de humedad registrados.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="align-middle">ID Proceso</th>
                                        <th class="align-middle">Descripción</th>
                                        <th class="align-middle">Fecha de Recepción</th>
                                        <th class="align-middle">Cantidad de Análisis</th>
                                        <th class="align-middle">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($processes as $proceso)
                                        <tr>
                                            <td class="align-middle">{{ $proceso->process_id }}</td>
                                            <td class="align-middle">{{ $proceso->description }}</td>
                                            <td class="align-middle">{{ \Carbon\Carbon::parse($proceso->reception_date)->format('d/m/Y') }}</td>
                                            <td class="align-middle">
                                                <span class="badge bg-primary">{{ $proceso->analyses->count() }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <a href="{{ route('lscefa.technical.analyses.humidity.process', ['processId' => $proceso->process_id]) }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit mr-1"></i> Procesar
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
    </section>
</div>
@endsection