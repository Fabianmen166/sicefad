@extends('lscefa::layouts.index')

@section('title', 'Detalles del Tipo de Cliente')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalles del Tipo de Cliente</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID:</label>
                                <p>{{ $customerType->id }}</p>
                            </div>

                            <div class="form-group">
                                <label>Nombre:</label>
                                <p>{{ $customerType->name }}</p>
                            </div>

                            <div class="form-group">
                                <label>Porcentaje de Descuento:</label>
                                <p>{{ $customerType->discount_percentage }}%</p>
                            </div>

                            <div class="form-group">
                                <label>Descripción:</label>
                                <p>{{ $customerType->description ?: 'Sin descripción' }}</p>
                            </div>

                            <div class="form-group">
                                <label>Fecha de Creación:</label>
                                <p>{{ $customerType->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>

                            <div class="form-group">
                                <label>Última Actualización:</label>
                                <p>{{ $customerType->updated_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('lscefa.quality.customer_types.edit', $customerType->id) }}" class="btn btn-primary">Editar</a>
                        <a href="{{ route('lscefa.quality.customer_types.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 