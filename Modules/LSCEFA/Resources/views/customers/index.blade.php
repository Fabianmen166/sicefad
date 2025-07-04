@extends('lscefa::layouts.master')

@section('title', 'Clientes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Clientes</h3>
                    <div class="card-tools">
                        <a href="{{ route('lscefa.quality.customers.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </a>
                    </div>
                </div>
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
                    <form action="{{ route('lscefa.quality.customers.index') }}" method="GET" class="form-inline mb-3">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Buscar por ID, NIT o solicitante" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        @if(request('search'))
                            <a href="{{ route('lscefa.quality.customers.index') }}" class="btn btn-secondary ml-2">Limpiar</a>
                        @endif
                    </form>
                    <div id="tableContainer">
                        @include('lscefa::customers.partials.table', ['customers' => $customers])
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 