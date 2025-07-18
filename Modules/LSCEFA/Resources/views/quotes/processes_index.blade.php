@extends('lscefa::layouts.master')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <h5 class="card-header">Procesos Iniciados para Cotización #{{ $quote->quote_id ?? 'N/A' }}</h5>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if ($processes->isEmpty())
                <div class="alert alert-info">No hay procesos iniciados para esta cotización.</div>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Proceso</th>
                            <th>Item Code</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha Recepción</th>
                            <th>Fecha Entrega</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($processes as $process)
                            <tr>
                                <td>{{ $process->process_id }}</td>
                                <td>{{ $process->item_code }}</td>
                                <td>{{ $process->description }}</td>
                                <td>{{ ucfirst($process->status) }}</td>
                                <td>{{ $process->reception_date }}</td>
                                <td>{{ $process->delivery_date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <a href="{{ route('lscefa.quality.quotes.show', $quote->quote_id) }}" class="btn btn-secondary mt-3">Volver a Cotización</a>
        </div>
    </div>
</div>
@endsection 