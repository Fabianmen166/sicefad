@extends('lscefa::layouts.master')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Revisión de reporte #{{ $detail->id }}</h3>
        <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">Datos del proceso</div>
                <div class="card-body">
                    <div class="mb-2"><strong>Proceso:</strong> {{ $detail->process_id }}</div>
                    <div class="mb-2"><strong>Cotización:</strong> {{ optional($detail->process->quote)->quote_id ?? '—' }}</div>
                    <div class="mb-2"><strong>Cliente:</strong> {{ optional(optional($detail->process->quote)->customer)->name ?? '—' }}</div>
                    <div class="mb-2"><strong>Servicio:</strong> {{ $detail->service->name ?? $detail->service_id }}</div>
                    <div class="mb-2"><strong>Estado actual:</strong> <span class="badge bg-{{ $detail->status === 'rejected' ? 'danger' : ($detail->status === 'approved' ? 'success' : 'warning text-dark') }} text-uppercase">{{ $detail->status }}</span></div>
                    <div class="mb-2"><strong>Archivo técnico:</strong> {{ $detail->file ?? '—' }}</div>
                    <div class="mb-2"><strong>Observaciones (técnico / previas):</strong><br>
                        <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;">{{ $detail->observations ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Resultados cargados por técnico (solo lectura)</div>
                <div class="card-body">
                    <div class="border rounded p-2" style="white-space: pre-wrap; min-height: 120px;">
                        {{ $detail->result ?? 'Sin resultados registrados.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header">Acciones de revisión</div>
                <div class="card-body">
                    <form method="post" action="{{ route('lscefa.quality.reviews.accept', $detail) }}" class="mb-3">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Observaciones (opcional)</label>
                            <textarea name="observations" class="form-control" rows="3" placeholder="Notas internas (opcional)"></textarea>
                        </div>
                        <button class="btn btn-success w-100" type="submit"><i class="fas fa-check me-1"></i> Aceptar</button>
                    </form>

                    <form method="post" action="{{ route('lscefa.quality.reviews.reject', $detail) }}" onsubmit="return confirm('¿Rechazar este reporte? Se solicitarán observaciones.');">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Observaciones de rechazo</label>
                            <textarea name="observations" class="form-control" rows="4" required placeholder="Explique el motivo del rechazo"></textarea>
                        </div>
                        <button class="btn btn-danger w-100" type="submit"><i class="fas fa-times me-1"></i> Rechazar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
