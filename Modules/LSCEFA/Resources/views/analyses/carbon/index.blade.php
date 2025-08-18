@extends('lscefa::layouts.technical')

@section('title', 'Gestión de Análisis de Carbono Orgánico')

@section('content')
    <div class="content-wrapper">
        <!-- Encabezado -->
   

        <!-- Contenido Principal -->
        <section class="content">
            <div class="container-fluid">
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

                <!-- Tabla de Procesos -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Lista de Procesos con Análisis de Carbono Orgánico</h3>
                    </div>
                    <div class="card-body">
                        @if ($processes->isEmpty())
                            <div class="text-center">
                                <p class="mb-0">No hay procesos con análisis de carbono orgánico registrados.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center align-middle">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID Proceso</th>
                                            <th>Descripción</th>
                                            <th>Fecha de Recepción</th>
                                            <th>Cantidad de Análisis</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($processes as $proceso)
                                            <tr>
                                                <td>{{ $proceso->process_id }}</td>
                                                <td>{{ $proceso->description }}</td>
                                                <td>{{ \Carbon\Carbon::parse($proceso->reception_date)->format('d/m/Y') }}
                                                </td>
                                                <td>{{ $proceso->analyses->count() }}</td>
                                                <td>
                                                    <a href="{{ route('lscefa.technical.analyses.carbon.process', [
                                                        'processId' => $proceso->process_id,
                                                        'serviceId' => $proceso->serviceProcessDetails->first()->service_id,
                                                    ]) }}"
                                                        class="btn btn-primary">
                                                        Procesar
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
