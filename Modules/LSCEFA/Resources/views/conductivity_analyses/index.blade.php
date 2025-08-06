@extends('lscefa::layouts.technical')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis de Conductividad</h3>
                    <div class="card-tools">
                        <a href="{{ route('lscefa.technical.analyses.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('lscefa::partials.pending_conductivity_batch', ['processes' => $processes])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection