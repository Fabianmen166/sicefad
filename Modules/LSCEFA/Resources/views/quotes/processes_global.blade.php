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
                            <th>ID Proceso</th>
                            <th>Cotización</th>
                            <th>Item Code</th>
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
                                <td>{{ $process->process_id }}</td>
                                <td>
                                    @if($process->quote)
                                        <a href="{{ route('lscefa.quality.quotes.show', $process->quote->quote_id) }}">{{ $process->quote->quote_id }}</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $process->item_code }}</td>
                                <td>{{ $process->description }}</td>
                                <td>{{ ucfirst($process->status) }}</td>
                                <td>{{ $process->reception_date }}</td>
                                <td>{{ $process->delivery_date }}</td>
                                <td>
                                    <a href="{{ route('lscefa.quality.processes.show', $process->process_id) }}" class="btn btn-primary btn-sm">Ver proceso</a>
                                    @php
                                        $user = auth()->user();
                                    @endphp
                                    @if($user && ($user->havePermission('lscefa.quality.processes.index') || $user->havePermission('lscefa.admin.processes.index')))
                                        <form action="{{ route('lscefa.quality.processes.destroy', $process->process_id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este proceso?')">Eliminar</button>
                                        </form>
                                    @endif
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