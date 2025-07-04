@extends('lscefa::layouts.index')

@section('title', 'Tipos de Cliente')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tipos de Cliente</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <a href="{{ route('lscefa.quality.dashboard') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                        <a href="{{ route('lscefa.quality.customer_types.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Tipo de Cliente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
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

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Porcentaje de Descuento</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerTypes as $type)
                                <tr>
                                    <td>{{ $type->customer_type_id }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->discount_percentage }}%</td>
                                    <td>{{ $type->description }}</td>
                                    <td>
                                        <a href="{{ route('lscefa.quality.customer_types.edit', $type->customer_type_id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('lscefa.quality.customer_types.destroy', $type->customer_type_id) }}" 
                                              method="POST" 
                                              style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('¿Está seguro de eliminar este tipo de cliente?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay tipos de cliente registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 