@extends('lscefa::layouts.master')

@section('title', 'Servicios')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-success">Servicios</h2>
            <hr>
        </div>
    </div>
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
    <form action="{{ route('lscefa.quality.services.index') }}" method="GET" class="form-inline mb-3">
        <input type="text" name="search" class="form-control mr-2" placeholder="Buscar por descripción" value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Buscar</button>
        @if(request('search'))
            <a href="{{ route('lscefa.quality.services.index') }}" class="btn btn-secondary ml-2">Limpiar</a>
        @endif
    </form>
    
    <div class="mb-3">
        <a href="{{ route('lscefa.quality.services.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Crear Servicio
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Acreditado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                    <tr>
                        <td>{{ $service->services_id }}</td>
                        <td>{{ $service->descripcion }}</td>
                        <td>${{ number_format($service->precio, 2) }}</td>
                        <td>{{ $service->acreditado ? 'Sí' : 'No' }}</td>
                        <td>
                            <a href="{{ route('lscefa.quality.services.edit', $service) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $services->links() }}
    </div>
</div>
@endsection