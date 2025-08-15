@extends('lscefa::layouts.master')

@section('title', 'Servicios')

@push('styles')
<style>
    .action-buttons .btn {
        margin-right: 5px;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-success">Servicios</h2>
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('lscefa.quality.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="{{ route('lscefa.quality.services.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo Servicio
                </a>
            </div>
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
                        <td class="action-buttons">
                            <a href="{{ route('lscefa.quality.services.edit', $service) }}" class="btn btn-sm btn-info" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('lscefa.quality.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(event, '{{ $service->descripcion }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
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

@push('scripts')
<script>
    function confirmDelete(event, serviceName) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Vas a eliminar el servicio "${serviceName}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.closest('form').submit();
            }
        });
        return false;
    }

    $(document).ready(function() {
        // Delete service with confirmation
        $('.delete-service').on('click', function(e) {
            e.preventDefault();
            if (confirm('¿Está seguro de que desea eliminar este servicio?')) {
                var form = $(this).closest('form');
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if(response.success) {
                            window.location.reload();
                        }
                    },
                    error: function(xhr) {
                        alert('Error al eliminar el servicio. Por favor, intente de nuevo.');
                    }
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: error.message || 'Ocurrió un error al procesar la solicitud',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
    });
</script>
@endpush

@endsection