@extends('lscefa::layouts.master')

@section('title', 'Historial de procesos')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Historial de procesos</h3>
        <form method="GET" action="{{ route('lscefa.admin.process_history.index') }}" class="form-inline">
            <div class="input-group input-group-sm">
                <input type="text" name="item_code" value="{{ request('item_code') }}" class="form-control" placeholder="Buscar por código de ítem">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            @if(request('item_code'))
                <a href="{{ route('lscefa.admin.process_history.index') }}" class="btn btn-link btn-sm ml-2">Limpiar</a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Código de ítem</th>
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
                            <td>{{ $p->item_code ?? '—' }}</td>
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
            {{ $processes->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
