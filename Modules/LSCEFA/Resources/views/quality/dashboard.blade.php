@extends('lscefa::layouts.master')

@section('title', 'Dashboard de Calidad')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dashboard de Gestión de Calidad</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Tipos de Cliente -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title text-white">Tipos de Cliente</h3>
                                </div>
                                <div class="card-body">
                                    <p>Gestión de tipos de cliente y sus características.</p>
                                    <a href="{{ route('lscefa.quality.customer_types.index') }}" class="btn btn-primary">
                                        <i class="fas fa-users"></i> Gestionar Tipos de Cliente
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Estándares de Calidad -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h3 class="card-title text-white">Estándares de Calidad</h3>
                                </div>
                                <div class="card-body">
                                    <p>Configuración y gestión de estándares de calidad.</p>
                                    <a href="#" class="btn btn-success">
                                        <i class="fas fa-certificate"></i> Gestionar Estándares
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Auditorías -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h3 class="card-title text-white">Auditorías</h3>
                                </div>
                                <div class="card-body">
                                    <p>Gestión de auditorías y seguimiento de calidad.</p>
                                    <a href="#" class="btn btn-info">
                                        <i class="fas fa-clipboard-check"></i> Gestionar Auditorías
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 