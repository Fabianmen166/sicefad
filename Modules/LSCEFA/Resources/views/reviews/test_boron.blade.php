@extends('lscefa::layouts.master')

@section('title', 'Test de Vista de Boro')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Test de Vista de Boro</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary float-right">Volver</a>
                </div>
            </div>
        </div>
    </section>
    
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de Test</h3>
                </div>
                <div class="card-body">
                    <p><strong>Estado:</strong> Vista de prueba cargada correctamente</p>
                    <p><strong>Timestamp:</strong> {{ now() }}</p>
                    <p><strong>URL:</strong> {{ request()->url() }}</p>
                    <p><strong>Método:</strong> {{ request()->method() }}</p>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Acciones de Test</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a Revisiones
                    </a>
                    <button class="btn btn-success ml-2" onclick="testJavaScript()">
                        <i class="fas fa-play mr-1"></i> Test JavaScript
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function testJavaScript() {
    alert('JavaScript está funcionando correctamente!');
    console.log('Test de JavaScript ejecutado');
}

$(document).ready(function() {
    console.log('Vista de test de Boro cargada correctamente');
    console.log('jQuery disponible:', typeof $ !== 'undefined');
    console.log('URL actual:', window.location.href);
});
</script>
@endpush
@endsection
