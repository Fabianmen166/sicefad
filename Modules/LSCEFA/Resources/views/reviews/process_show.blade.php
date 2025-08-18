@extends('lscefa::layouts.technical')

@section('title', 'Revisión de Proceso')

@push('styles')
<style>
    .nav-tabs .nav-link {
        font-weight: 600;
    }
    .tab-pane {
        padding: 20px 0;
    }
    .analysis-section {
        margin-bottom: 30px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
    }
    .analysis-header {
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #dee2e6;
    }
    .analysis-body {
        padding: 15px;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión de Proceso</h1>
                    <p class="mb-0"><strong>Código:</strong> {{ $process->item_code ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Cliente:</strong> {{ $process->customer->applicant ?? 'N/A' }}</p>
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

            <!-- Pestañas para cada tipo de análisis -->
            <ul class="nav nav-tabs" id="analysisTabs" role="tablist">
                @if(!empty($analyses['ph']))
                <li class="nav-item">
                    <a class="nav-link active" id="ph-tab" data-toggle="tab" href="#ph" role="tab">
                        <i class="fas fa-flask"></i> Análisis de pH
                        <span class="badge badge-primary">{{ count($analyses['ph']) }}</span>
                    </a>
                </li>
                @endif
                @if(!empty($analyses['conductivity']))
                <li class="nav-item">
                    <a class="nav-link {{ empty($analyses['ph']) ? 'active' : '' }}" id="conductivity-tab" data-toggle="tab" href="#conductivity" role="tab">
                        <i class="fas fa-bolt"></i> Conductividad
                        <span class="badge badge-primary">{{ count($analyses['conductivity']) }}</span>
                    </a>
                </li>
                @endif
            </ul>

            <div class="tab-content" id="analysisTabsContent">
                <!-- Pestaña de pH -->
                @if(!empty($analyses['ph']))
                <div class="tab-pane fade show active" id="ph" role="tabpanel">
                    @foreach($analyses['ph'] as $index => $item)
                        @include('lscefa::reviews.partials.ph_analysis', [
                            'analysis' => $item['analysis'],
                            'detail' => $item['detail'],
                            'index' => $index + 1
                        ])
                    @endforeach
                </div>
                @endif

                <!-- Pestaña de Conductividad -->
                @if(!empty($analyses['conductivity']))
                <div class="tab-pane fade {{ empty($analyses['ph']) ? 'show active' : '' }}" id="conductivity" role="tabpanel">
                    @foreach($analyses['conductivity'] as $index => $item)
                        @include('lscefa::reviews.partials.conductivity_analysis', [
                            'analysis' => $item['analysis'],
                            'detail' => $item['detail'],
                            'index' => $index + 1
                        ])
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Acciones de Revisión -->
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Acciones de Revisión</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="{{ route('lscefa.quality.reviews.process.accept', $process) }}" class="mb-3">
                                @csrf
                                <div class="form-group">
                                    <label>Observaciones (opcional)</label>
                                    <textarea name="observations" class="form-control" rows="3" placeholder="Ingrese observaciones adicionales (opcional)"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> Aprobar Todo el Proceso
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="post" action="{{ route('lscefa.quality.reviews.process.reject', $process) }}" onsubmit="return confirm('¿Está seguro de rechazar todo el proceso?')">
                                @csrf
                                <div class="form-group">
                                    <label>Motivo del Rechazo <span class="text-danger">*</span></label>
                                    <textarea name="observations" class="form-control" rows="3" required placeholder="Explique el motivo del rechazo"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-times"></i> Rechazar Todo el Proceso
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Activar las pestañas
    $(function () {
        $('#analysisTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>
@endpush
