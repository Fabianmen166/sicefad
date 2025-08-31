@extends('lscefa::layouts.master')

@section('title', 'Revisión - Análisis de Intercambio Catiónico')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revisión - Análisis de Intercambio Catiónico</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Análisis en revisión:</strong> Análisis de intercambio catiónico pendiente de revisión.
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revisión de Análisis de Intercambio Catiónico</h3>
                </div>
                <div class="card-body">
                    
                    <!-- Información General del Análisis -->
                    <h4 class="mt-4">Información General</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="consecutivo_no">Consecutivo No.</label>
                                <input type="text" class="form-control" id="consecutivo_no" value="{{ $analysis->consecutivo_no ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha_analisis">Fecha del análisis</label>
                                <input type="text" class="form-control" id="fecha_analisis" value="{{ $analysis->fecha_analisis ? \Carbon\Carbon::parse($analysis->fecha_analisis)->format('d/m/Y') : 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unidades_reporte_equipo">Unidades de reporte equipo</label>
                                <input type="text" class="form-control" id="unidades_reporte_equipo" value="{{ $analysis->unidades_reporte_equipo ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nombre_metodo">Nombre del Método</label>
                                <input type="text" class="form-control" id="nombre_metodo" value="{{ $analysis->nombre_metodo ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="equipo_utilizado">Equipo utilizado</label>
                                <input type="text" class="form-control" id="equipo_utilizado" value="{{ $analysis->equipo_utilizado ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="intervalo_metodo">Intervalo del método</label>
                                <input type="text" class="form-control" id="intervalo_metodo" value="{{ $analysis->intervalo_metodo ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_analista">Nombre Analista</label>
                                <input type="text" class="form-control" id="nombre_analista" value="{{ $analysis->nombre_analista ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resolucion_instrumental">Resolución instrumental</label>
                                <input type="text" class="form-control" id="resolucion_instrumental" value="{{ $analysis->resolucion_instrumental ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" rows="3" readonly>{{ $analysis->observaciones ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal Navigation Bar -->
                    <div class="mt-4 mb-3">
                        <ul class="nav nav-tabs nav-fill" id="analysisTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-content" type="button" role="tab" aria-controls="items-content" aria-selected="true">
                                    <i class="fas fa-flask"></i> Items de Ensayo
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="quality-tab" data-bs-toggle="tab" data-bs-target="#quality-content" type="button" role="tab" aria-controls="quality-content" aria-selected="false">
                                    <i class="fas fa-check-circle"></i> Controles de Calidad
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="analysisTabContent">
                        <!-- Items de Ensayo Tab -->
                        <div class="tab-pane fade show active" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                            <!-- Información del Proceso -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-list"></i> Información del Proceso</h4>
                                </div>
                                <div class="card-body">
                                    <div class="process-item mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Proceso: {{ $process->process_id ?? 'N/A' }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Cliente:</strong> {{ $customer->nombre ?? $customer->name ?? 'N/A' }}</p>
                                                        <p><strong>Servicio:</strong> Intercambio Catiónico</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Estado:</strong> {{ $process->status ?? 'N/A' }}</p>
                                                        <p><strong>Fecha:</strong> {{ $process->created_at ? $process->created_at->format('d/m/Y') : 'N/A' }}</p>
                                                    </div>
                                                </div>

                                                <!-- Items para este proceso -->
                                                <div class="items-container">
                                                    <h6>Items de Ensayo</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th class="text-center">#</th>
                                                                    <th class="text-center">Código interno</th>
                                                                    <th class="text-center">Peso muestra (g)</th>
                                                                    <th class="text-center">Vol NaOH muestra (mL)</th>
                                                                    <th class="text-center">Vol NaOH blanco (mL)</th>
                                                                    <th class="text-center">Normalidad NaOH</th>
                                                                    <th class="text-center">Humedad (%)</th>
                                                                    <th class="text-center">CIC cmol (+)/kg</th>
                                                                    <th class="text-center">Observaciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr class="fila-resultado">
                                                                    <td class="numero-fila text-center">1</td>
                                                                    <td>{{ $analysis->codigo_interno ?? 'N/A' }}</td>
                                                                    <td>{{ $analysis->peso_muestra ? number_format($analysis->peso_muestra, 4) : 'N/A' }}</td>
                                                                    <td>{{ $analysis->vol_naoh_muestra ? number_format($analysis->vol_naoh_muestra, 2) : 'N/A' }}</td>
                                                                    <td>{{ $analysis->vol_naoh_blanco ? number_format($analysis->vol_naoh_blanco, 2) : 'N/A' }}</td>
                                                                    <td>{{ $analysis->normalidad_naoh ? number_format($analysis->normalidad_naoh, 2) : 'N/A' }}</td>
                                                                    <td>{{ $analysis->humedad_porcentaje ? number_format($analysis->humedad_porcentaje, 2) : 'N/A' }}</td>
                                                                    <td>{{ $analysis->cic_resultado ? number_format($analysis->cic_resultado, 2) : 'N/A' }}</td>
                                                                    <td>{{ $analysis->observaciones ?? 'N/A' }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controles de Calidad Tab -->
                        <div class="tab-pane fade" id="quality-content" role="tabpanel" aria-labelledby="quality-tab">
                            <!-- Controles de Calidad -->
                            <h4 class="mt-4">CONTROLES DE CALIDAD</h4>
                            
                            @if($analysis->analyticalControl)
                                @if(isset($controles_analiticos) && !empty($controles_analiticos))
                                    @foreach($controles_analiticos as $control)
                                        @if(isset($control['tipo']))
                                            <!-- {{ ucfirst($control['tipo']) }} -->
                                            <h5 class="mt-3">{{ ucfirst($control['tipo']) }}</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            @foreach($control['datos_completos'] ?? [] as $key => $value)
                                                                <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            @foreach($control['datos_completos'] ?? [] as $value)
                                                                <td>{{ $value ?? 'N/A' }}</td>
                                                            @endforeach
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No hay controles analíticos registrados para este análisis.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No hay controles analíticos registrados para este análisis.
                                </div>
                            @endif
                        </div>
                        <!-- End of Controles de Calidad Tab -->
                    </div>
                    <!-- End of Tab Content -->

                    <!-- Botones -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <form action="{{ route('lscefa.quality.reviews.accept', $analysis->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="analysis_type" value="cationic">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check mr-2"></i> Aprobar Análisis
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger ml-2" data-toggle="modal" data-target="#rejectModal">
                                        <i class="fas fa-times mr-2"></i> Rechazar Análisis
                                    </button>
                                </div>
                                <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i> Volver a Revisiones
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Rechazar -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('lscefa.quality.reviews.reject', $analysis->id) }}" method="POST">
                @csrf
                <input type="hidden" name="analysis_type" value="cationic">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Rechazar Análisis</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="observations">Observaciones de Rechazo:</label>
                        <textarea class="form-control" id="observations" name="observations" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
#analysisTabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 20px;
}

#analysisTabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    margin-right: 5px;
    padding: 12px 20px;
    font-weight: 500;
    color: #6c757d;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

#analysisTabs .nav-link:hover {
    background-color: #e9ecef;
    color: #495057;
    transform: translateY(-2px);
}

#analysisTabs .nav-link.active {
    background-color: #007bff;
    color: white;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

#analysisTabs .nav-link i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    #analysisTabs .nav-link {
        padding: 8px 12px;
        font-size: 14px;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Tab switching functionality
    $('#analysisTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and content
        $('#analysisTabs .nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        
        // Show corresponding content
        var target = $(this).data('bs-target');
        $(target).addClass('show active');
    });
    
    // Confirmación antes de aprobar
    $('form[action*="accept"]').on('submit', function(e) {
        if (!confirm('¿Está seguro de que desea aprobar este análisis?')) {
            e.preventDefault();
        }
    });
    
    // Validación del modal de rechazo
    $('#rejectModal form').on('submit', function(e) {
        const observations = $('#observations').val().trim();
        if (!observations) {
            alert('Debe ingresar las observaciones de rechazo.');
            e.preventDefault();
            $('#observations').focus();
        }
    });
});
</script>
@endpush
