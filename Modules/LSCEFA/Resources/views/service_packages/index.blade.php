@extends('lscefa::layouts.master')

@section('title', 'Paquetes de Servicio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Paquetes de Servicio</h3>
                    <div class="card-tools">
                        <a href="{{ route('lscefa.quality.service_packages.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Paquete
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form action="{{ route('lscefa.quality.service_packages.index') }}" method="GET" class="form-inline mb-3">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Buscar por nombre" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        @if(request('search'))
                            <a href="{{ route('lscefa.quality.service_packages.index') }}" class="btn btn-secondary ml-2">Limpiar</a>
                        @endif
                    </form>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Acreditado</th>
                                <th>Servicios Incluidos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicePackages as $package)
                            <tr>
                                <td>{{ $package->service_package_id }}</td>
                                <td>{{ $package->name }}</td>
                                <td>${{ number_format($package->price, 2) }}</td>
                                <td>
                                    @if($package->accredited)
                                        <span class="badge badge-success">Sí</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $included = collect($services)->whereIn('services_id', $package->included_services ?? []);
                                    @endphp
                                    @foreach($included as $service)
                                        <span class="badge badge-info">{{ $service->descripcion }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('lscefa.quality.service_packages.edit', $package->service_package_id) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('lscefa.quality.service_packages.destroy', $package->service_package_id) }}" 
                                          method="POST" 
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="return confirm('¿Está seguro de eliminar este paquete?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $servicePackages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 