@extends('lscefa::layouts.master')

@section('title', 'Servicios')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-success">Servicios</h2>
            <a href="{{ route('lscefa.quality.services.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nuevo Servicio
            </a>
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
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('lscefa.quality.services.destroy', $service) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este servicio?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $services->links() }}
    </div>
</div>
@endsection 