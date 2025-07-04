@extends('lscefa::layouts.master')

@section('title', 'Dashboard Administrador')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-primary">Dashboard Administrador LSCEFA</h2>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <p>Bienvenido, {{ isset($user) ? $user->nickname : 'Administrador' }}.</p>
        </div>
    </div>
</div>
@endsection 