@extends('lscefa::layouts.master')

@section('title', 'Editar Paquete de Servicio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Paquete de Servicio</h3>
                    <div class="card-tools">
                        <a href="{{ route('lscefa.quality.service_packages.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('lscefa.quality.service_packages.update', $servicePackage->service_package_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Nombre del Paquete</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $servicePackage->name) }}" 
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price">Precio</label>
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   step="0.01" 
                                   value="{{ old('price', $servicePackage->price) }}" 
                                   required>
                            @error('price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="accredited" value="0">
                                <input type="checkbox" class="custom-control-input" id="accredited" name="accredited" value="1" {{ old('accredited', $servicePackage->accredited) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="accredited">Acreditado</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Servicios Incluidos</label>
                            <div>
                                @foreach($services as $service)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="included_services[]"
                                               id="service_{{ $service->services_id }}"
                                               value="{{ $service->services_id }}"
                                               {{ in_array($service->services_id, old('included_services', $servicePackage->included_services ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="service_{{ $service->services_id }}">
                                            {{ $service->descripcion }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('included_services')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush 