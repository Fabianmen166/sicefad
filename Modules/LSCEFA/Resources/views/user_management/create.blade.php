@extends('lscefa::layouts.master')

@section('title', 'Crear Usuario - LSCEFA')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Crear Nuevo Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.admin.welcome') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.admin.users.index') }}">Usuarios</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('lscefa.admin.users.store') }}" method="POST">
                        @csrf

                        <h5 class="mb-3">Datos de la Persona</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="document_type">Tipo de Documento</label>
                                <select id="document_type" name="document_type" class="form-control @error('document_type') is-invalid @enderror" required>
                                    <option value="">Seleccione un tipo</option>
                                    @php($docTypes = [
                                        'Cédula de Ciudadanía',
                                        'Tarjeta de Identidad',
                                        'Cédula de Extranjería',
                                        'Pasaporte',
                                        'Documento Nacional de Identidad',
                                        'Registro Civil',
                                        'Número de Identificación Tributaria'
                                    ])
                                    @foreach($docTypes as $type)
                                        <option value="{{ $type }}" {{ old('document_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('document_type')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="document_number">Número de Documento</label>
                                <input type="text" id="document_number" name="document_number" class="form-control @error('document_number') is-invalid @enderror" value="{{ old('document_number') }}" required>
                                @error('document_number')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="first_name">Nombres</label>
                                <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="first_last_name">Primer Apellido</label>
                                <input type="text" id="first_last_name" name="first_last_name" class="form-control @error('first_last_name') is-invalid @enderror" value="{{ old('first_last_name') }}" required>
                                @error('first_last_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="second_last_name">Segundo Apellido (opcional)</label>
                                <input type="text" id="second_last_name" name="second_last_name" class="form-control @error('second_last_name') is-invalid @enderror" value="{{ old('second_last_name') }}">
                                @error('second_last_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">Datos del Usuario</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nickname">Nombre (nickname)</label>
                                <input type="text" id="nickname" name="nickname" class="form-control @error('nickname') is-invalid @enderror" value="{{ old('nickname') }}" required>
                                @error('nickname')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="password_confirmation">Confirmar Contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="role">Rol</label>
                                <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                                    <option value="">Seleccione un rol</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('lscefa.', '', $role->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <a href="{{ route('lscefa.admin.users.index') }}" class="btn btn-default mr-2">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
