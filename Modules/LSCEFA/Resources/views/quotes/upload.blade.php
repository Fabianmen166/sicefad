@extends('lscefa::layouts.master')
@section('content')
<div class="container">
    <h1>Subir comprobante para Cotización #{{ $quote->quote_id }}</h1>
    <form action="{{ route('lscefa.quality.quotes.upload', $quote->quote_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="archivo">Archivo (PDF, JPG, PNG, máx 2MB):</label>
            <input type="file" name="archivo" id="archivo" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Subir comprobante</button>
    </form>
    <hr>
    <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-secondary">Volver a cotizaciones</a>
</div>
@endsection 