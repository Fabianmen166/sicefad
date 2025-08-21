@extends('lscefa::layouts.master')

@section('title', 'Historial de procesos')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Historial de procesos</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID Proceso</th>
                        <th>Cotización</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Recepción</th>
                        <th>Entrega</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processes as $p)
                        <tr>
                            <td>{{ $p->process_id }}</td>
                            <td>
                                @if($p->quote)
                                    <a href="{{ route('lscefa.quality.quotes.show', $p->quote->quote_id ?? $p->quote_id) }}">
                                        {{ $p->quote->quote_id ?? $p->quote_id }}
                                    </a>
                                @else
                                    {{ $p->quote_id }}
                                @endif
                            </td>
                            <td>{{ $p->quote->customer->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($p->status ?? 'pendiente') }}</td>
                            <td>{{ $p->reception_date ?? '-' }}</td>
                            <td>{{ $p->delivery_date ?? '-' }}</td>
                            <td class="text-right">
                                <a class="btn btn-primary btn-sm" href="{{ route('lscefa.admin.process_history.show', $p->process_id) }}">
                                    <i class="fas fa-history mr-1"></i> Ver historial
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay procesos para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($processes, 'links'))
        <div class="card-footer">
            {{ $processes->links() }}
        </div>
    @endif
</div>
@endsection
