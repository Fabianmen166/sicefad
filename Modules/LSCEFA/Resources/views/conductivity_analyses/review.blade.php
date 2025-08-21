@extends('lscefa::layouts.technical')

@section('title', 'Revisión de Análisis de Conductividad')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Análisis de Conductividad</h1>
                    <p class="mb-0"><strong>Consecutivo No.:</strong> {{ $analysis->consecutivo_no ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Proceso:</strong> {{ $detail->process->item_code ?? 'N/A' }}</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Mostrar el formulario en modo solo lectura -->
            @include('lscefa::conductivity_analyses.partials.form', [
                'readonly' => true,
                'analysis' => $analysis,
                'user' => $analysis->user ?? null,
                'pendingItems' => $analysis->items_ensayo ?? []
            ])

            <!-- Sección de revisión -->
            @if($detail->status === 'completed')
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Acciones de Revisión</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="post" action="{{ route('lscefa.quality.reviews.accept', $detail) }}" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="analysis_type" value="conductivity">
                                    <div class="form-group">
                                        <label>Observaciones (opcional)</label>
                                        <textarea name="observations" class="form-control" rows="3" placeholder="Ingrese observaciones adicionales (opcional)"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> Aprobar Análisis
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="post" action="{{ route('lscefa.quality.reviews.reject', $detail) }}" onsubmit="return confirm('¿Está seguro de rechazar este análisis?')">
                                    @csrf
                                    <div class="form-group">
                                        <label>Motivo del Rechazo <span class="text-danger">*</span></label>
                                        <textarea name="observations" class="form-control" rows="3" required placeholder="Explique el motivo del rechazo"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-times"></i> Rechazar Análisis
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<!-- Incluir los scripts necesarios para la visualización -->
@push('scripts')
<script>
    // Deshabilitar todos los campos del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('conductivityForm');
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select, button');
            inputs.forEach(input => {
                input.disabled = true;
                input.readOnly = true;
                
                // Aplicar estilos a los campos deshabilitados para mejor legibilidad
                if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                    input.classList.add('bg-light');
                }
            });
            
            // Asegurarse de que los botones de radio y checkbox también estén deshabilitados
            const checkboxes = form.querySelectorAll('input[type="checkbox"], input[type="radio"]');
            checkboxes.forEach(checkbox => {
                checkbox.disabled = true;
            });
        }
    });
</script>
@endpush
@endsection
