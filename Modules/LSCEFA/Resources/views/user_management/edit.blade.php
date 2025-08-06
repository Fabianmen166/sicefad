@extends('lscefa::layouts.technical')

@section('title', 'Editar Usuario - LSCEFA')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Editar Usuario: {{ $user->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.admin.welcome') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.admin.users.index') }}">Usuarios</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                {{-- Incluir el formulario parcial --}}
                @include('lscefa::user_management.form', ['user' => $user, 'userRole' => $userRole, 'roles' => $roles])
            </div>
        </div>
    </section>
</div>
@endsection
