@extends('lscefa::layouts.master')

@section('title', 'Editar Servicio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Servicio</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('lscefa.quality.services.update', $service->services_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <input type="text" class="form-control @error('descripcion') is-invalid @enderror" 
                                id="descripcion" name="descripcion" value="{{ old('descripcion', $service->descripcion) }}" required>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" step="0.01" class="form-control @error('precio') is-invalid @enderror" 
                                id="precio" name="precio" value="{{ old('precio', $service->precio) }}" required>
                            @error('precio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="acreditado" name="acreditado" 
                                    value="1" {{ $service->acreditado ? 'checked' : '' }}>
                                <label class="custom-control-label" for="acreditado">Acreditado</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
                            <a href="{{ route('lscefa.quality.services.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 