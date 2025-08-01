@extends('lscefa::layouts.master')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <h5 class="card-header">Listado Global de Procesos Iniciados</h5>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if ($processes->isEmpty())
                <div class="alert alert-info">No hay procesos iniciados en el sistema.</div>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Cotización</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha Recepción</th>
                            <th>Fecha Entrega</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($processes as $process)
                            <tr>
                                <td>{{ $process->item_code }}</td>
                                <td>
                                    <a href="{{ route('lscefa.quality.quotes.show', $process->quote_id) }}">
                                        {{ $process->quote_id }}
                                    </a>
                                </td>
                                <td>{{ $process->description }}</td>
                                <td>{{ ucfirst($process->status) }}</td>
                                <td>{{ $process->reception_date }}</td>
                                <td>{{ $process->delivery_date }}</td>
                                <td>
                                    <a href="{{ route('lscefa.quality.processes.show', $process->process_id) }}" class="btn btn-success btn-sm mb-1">Ver proceso</a>
                                    <form action="{{ route('lscefa.quality.processes.destroy', $process->process_id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este proceso?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $processes->links() }}
            @endif
        </div>
    </div>
</div>
@endsection 