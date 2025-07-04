@extends('lscefa::layouts.master')

@section('title', 'Editar Cliente')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Cliente</h3>
                    <div class="card-tools">
                        <a href="{{ route('lscefa.quality.customers.index') }}" class="btn btn-default btn-sm">
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

                    <form action="{{ route('lscefa.quality.customers.update', $customer->customer_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="applicant">Solicitante</label>
                                    <input type="text" 
                                           class="form-control @error('applicant') is-invalid @enderror" 
                                           id="applicant" 
                                           name="applicant" 
                                           value="{{ old('applicant', $customer->applicant) }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact">Contacto</label>
                                    <input type="text" 
                                           class="form-control @error('contact') is-invalid @enderror" 
                                           id="contact" 
                                           name="contact" 
                                           value="{{ old('contact', $customer->contact) }}" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Teléfono</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $customer->phone) }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_id">NIT</label>
                                    <input type="text" 
                                           class="form-control @error('tax_id') is-invalid @enderror" 
                                           id="tax_id" 
                                           name="tax_id" 
                                           value="{{ old('tax_id', $customer->tax_id) }}" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $customer->email) }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_type_id">Tipo de Cliente</label>
                                    <select class="form-control @error('customer_type_id') is-invalid @enderror" 
                                            id="customer_type_id" 
                                            name="customer_type_id" 
                                            required>
                                        <option value="">Seleccione un tipo</option>
                                        @foreach($customerTypes as $type)
                                            <option value="{{ $type->customer_type_id }}" 
                                                    {{ old('customer_type_id', $customer->customer_type_id) == $type->customer_type_id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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