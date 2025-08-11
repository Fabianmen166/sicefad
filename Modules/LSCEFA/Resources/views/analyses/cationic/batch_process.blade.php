@extends('lscefa::layouts.technical')

@section('title', 'Análisis de Intercambio Catiónico por Lotes')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Análisis de Intercambio Catiónico por Lotes</h1>
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

            @if ($pendingProcesses->isEmpty())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error: No hay procesos pendientes para procesar.
                </div>
                <a href="{{ route('lscefa.technical.analyses.cationic.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Regresar
                </a>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Procesos seleccionados:</strong> {{ $pendingProcesses->count() }} proceso(s) pendiente(s) de análisis de intercambio catiónico.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Formulario de Análisis de Intercambio Catiónico por Lotes</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('lscefa.technical.analyses.cationic.batch_store') }}" method="POST">
                            @csrf
                            


                            <!-- Información General del Análisis -->
                            <h4 class="mt-4">Información General</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="consecutivo_no">Consecutivo No. *</label>
                                        <input type="text" class="form-control" id="consecutivo_no" name="consecutivo_no" value="1" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_analisis">Fecha del análisis *</label>
                                        <input type="date" class="form-control" id="fecha_analisis" name="fecha_analisis" value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="unidades_reporte_equipo">Unidades de reporte equipo *</label>
                                        <input type="text" class="form-control" id="unidades_reporte_equipo" name="unidades_reporte_equipo" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nombre_metodo">Nombre del Método *</label>
                                        <input type="text" class="form-control" id="nombre_metodo" name="nombre_metodo" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="equipo_utilizado">Equipo utilizado *</label>
                                        <input type="text" class="form-control" id="equipo_utilizado" name="equipo_utilizado" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="intervalo_metodo">Intervalo del método *</label>
                                        <input type="text" class="form-control" id="intervalo_metodo" name="intervalo_metodo" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre_analista">Nombre Analista *</label>
                                        <input type="text" class="form-control" id="nombre_analista" name="nombre_analista" value="{{ Auth::user()->name }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="resolucion_instrumental">Resolución instrumental</label>
                                        <input type="text" class="form-control" id="resolucion_instrumental" name="resolucion_instrumental">
                                    </div>
                                </div>
                            </div>



                            <!-- Observaciones -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
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
                                    <!-- Procesos a Procesar -->
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h4><i class="fas fa-list"></i> Procesos a Procesar</h4>
                                        </div>
                                        <div class="card-body">
                                            @foreach($pendingProcesses as $process)
                                            <div class="process-item mb-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Proceso: {{ $process->process_id }}</h5>
                                                        <input type="hidden" name="process_ids[]" value="{{ $process->process_id }}">
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Cliente:</strong> {{ $process->customer->nombre ?? 'N/A' }}</p>
                                                                <p><strong>Servicio:</strong> 
                                                                    @foreach($process->serviceProcessDetails as $detail)
                                                                        @if(str_contains(strtolower($detail->service->descripcion), 'intercambio cationico') || 
                                                                            str_contains(strtolower($detail->service->descripcion), 'cationic exchange'))
                                                                            {{ $detail->service->descripcion }}
                                                                        @endif
                                                                    @endforeach
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Estado:</strong> {{ $process->status }}</p>
                                                                <p><strong>Fecha:</strong> {{ $process->created_at->format('d/m/Y') }}</p>
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
                                                                            <th class="text-center">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="fila-resultado">
                                                                            <td class="numero-fila text-center">1</td>
                                                                            <td><input type="text" class="form-control" name="items_ensayo[{{ $process->process_id }}][0][codigo_interno]"></td>
                                                                            <td><input type="number" step="0.0001" class="form-control peso-muestra" name="items_ensayo[{{ $process->process_id }}][0][peso_muestra]"></td>
                                                                            <td><input type="number" step="0.01" class="form-control vol-naoh-muestra" name="items_ensayo[{{ $process->process_id }}][0][vol_naoh_muestra]"></td>
                                                                            <td><input type="number" step="0.01" class="form-control vol-naoh-blanco" name="items_ensayo[{{ $process->process_id }}][0][vol_naoh_blanco]"></td>
                                                                            <td><input type="number" step="0.01" class="form-control normalidad-naoh" name="items_ensayo[{{ $process->process_id }}][0][normalidad_naoh]"></td>
                                                                            <td><input type="number" step="0.01" class="form-control humedad-porcentaje" name="items_ensayo[{{ $process->process_id }}][0][humedad_porcentaje]"></td>
                                                                            <td><input type="text" class="form-control cic-resultado" name="items_ensayo[{{ $process->process_id }}][0][cic_resultado]" readonly></td>
                                                                            <td><input type="text" class="form-control" name="items_ensayo[{{ $process->process_id }}][0][observaciones]"></td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-success btn-sm" onclick="addItemToProcess('{{ $process->process_id }}')">
                                                                                    <i class="fas fa-plus"></i>
                                                                                </button>
                                                                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                                                                    <i class="fas fa-minus"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Controles de Calidad Tab -->
                                <div class="tab-pane fade" id="quality-content" role="tabpanel" aria-labelledby="quality-tab">
                                    <!-- Controles de Calidad -->
                                    <h4 class="mt-4">CONTROLES DE CALIDAD</h4>
                                    
                                    <!-- 1. Blanco método -->
                                    <h5 class="mt-3">1. Blanco método</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Identificación</th>
                                            <th>LCM cmol (+)/kg</th>
                                            <th>Valor leído cmol (+)/kg</th>
                                            <th>Aceptable/no aceptable</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="blanco_identificacion" value="Blanco del método">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="blanco_lcm" value="2.61">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="blanco_valor_leido">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="blanco_aceptable" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="blanco_observaciones">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 2. Control de Laboratorio (CRM/SRM) -->
                            <h5 class="mt-3">2. Control de Laboratorio (CRM/SRM)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Identificación de muestra</th>
                                            <th>Valor certificado cmol (+)/kg</th>
                                            <th>Resultado medido cmol (+)/kg</th>
                                            <th>% Exactitud</th>
                                            <th>Aceptable/no aceptable</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="error_identificacion" value="Muestra Referencia Certificada">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="error_valor_teorico">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="error_valor_leido">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="error_porcentaje" readonly>
                                                <small class="form-text text-muted">Criterio: Error Relativo ≤ 20%</small>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="error_aceptable" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="error_observaciones">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 3. Recuperación de Estándar (Spike Recovery) -->
                            <h5 class="mt-3">3. Recuperación de Estándar (Spike Recovery)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Identificación de muestra</th>
                                            <th>Cantidad agregada cmol (+)/kg</th>
                                            <th>Resultado muestra fortificada cmol (+)/kg</th>
                                            <th>% Recuperación</th>
                                            <th>Aceptable/no aceptable</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="recuperacion_identificacion" value="Muestra Fortificada">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="recuperacion_valor_teorico">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="recuperacion_valor_leido">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="recuperacion_porcentaje" readonly>
                                                <small class="form-text text-muted">Criterio: 70-130%</small>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="recuperacion_aceptable" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="recuperacion_observaciones">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 4. Duplicados (DPR/RPD) -->
                            <h5 class="mt-3">4. Duplicados (DPR/RPD)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Identificación de muestra</th>
                                            <th>Resultado replica 1 cmol (+)/kg</th>
                                            <th>Resultado replica 2 cmol (+)/kg</th>
                                            <th>% Diferencia</th>
                                            <th>Aceptable/no aceptable</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="dpr_identificacion" value="Muestra Duplicada">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="dpr_replica1">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" name="dpr_replica2">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="dpr_porcentaje" readonly>
                                                <small class="form-text text-muted">Criterio: RPD ≤ 25%</small>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="dpr_aceptable" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="dpr_observaciones">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                                </div>
                                <!-- End of Controles de Calidad Tab -->
                            </div>
                            <!-- End of Tab Content -->

                            <!-- Botones -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Guardar Análisis por Lotes</button>
                                    <a href="{{ route('lscefa.technical.analyses.cationic.index') }}" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>
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
let itemIndices = {};

// Inicializar índices de items para cada proceso
@foreach($pendingProcesses as $process)
    itemIndices['{{ $process->process_id }}'] = 1;
@endforeach

function addItemToProcess(processId) {
    const tbody = document.querySelector(`input[name="items_ensayo[${processId}][0][codigo_interno]"]`).closest('tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'fila-resultado';
    newRow.innerHTML = `
        <td class="numero-fila text-center">${itemIndices[processId] + 1}</td>
        <td><input type="text" class="form-control" name="items_ensayo[${processId}][${itemIndices[processId]}][codigo_interno]"></td>
        <td><input type="number" step="0.0001" class="form-control peso-muestra" name="items_ensayo[${processId}][${itemIndices[processId]}][peso_muestra]"></td>
        <td><input type="number" step="0.01" class="form-control vol-naoh-muestra" name="items_ensayo[${processId}][${itemIndices[processId]}][vol_naoh_muestra]"></td>
        <td><input type="number" step="0.01" class="form-control vol-naoh-blanco" name="items_ensayo[${processId}][${itemIndices[processId]}][vol_naoh_blanco]"></td>
        <td><input type="number" step="0.01" class="form-control normalidad-naoh" name="items_ensayo[${processId}][${itemIndices[processId]}][normalidad_naoh]"></td>
        <td><input type="number" step="0.01" class="form-control humedad-porcentaje" name="items_ensayo[${processId}][${itemIndices[processId]}][humedad_porcentaje]"></td>
        <td><input type="text" class="form-control cic-resultado" name="items_ensayo[${processId}][${itemIndices[processId]}][cic_resultado]" readonly></td>
        <td><input type="text" class="form-control" name="items_ensayo[${processId}][${itemIndices[processId]}][observaciones]"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-row">
                <i class="fas fa-minus"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
    itemIndices[processId]++;
}

function removeItem(button) {
    button.closest('tr').remove();
}

// Event listener para remover filas
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
        const button = e.target.classList.contains('remove-row') ? e.target : e.target.closest('.remove-row');
        button.closest('tr').remove();
    }
});

$(document).ready(function() {
    // Función para calcular el CIC de una fila específica
    function calcularCIC(fila) {
        var peso = parseFloat(fila.find('.peso-muestra').val()) || 0;
        var volNaohMuestra = parseFloat(fila.find('.vol-naoh-muestra').val()) || 0;
        var volNaohBlanco = parseFloat(fila.find('.vol-naoh-blanco').val()) || 0;
        var normalidadNaoh = parseFloat(fila.find('.normalidad-naoh').val()) || 0;
        var humedad = parseFloat(fila.find('.humedad-porcentaje').val()) || 0;

        if (peso > 0) {
            // Fórmula: (Vol NaOH muestra - Vol NaOH blanco) * Normalidad NaOH * (100 + Humedad) / Peso
            var cic = ((volNaohMuestra - volNaohBlanco) * normalidadNaoh * (100 + humedad)) / peso;
            
            // Formatear a máximo 2 decimales
            fila.find('.cic-resultado').val(cic.toFixed(2));
            
            // Mostrar advertencia si el resultado es negativo
            if (cic < 0) {
                fila.find('.cic-resultado').addClass('text-danger');
                fila.find('.cic-resultado').attr('title', 'Advertencia: Resultado negativo. Verifique los valores de Vol NaOH muestra y blanco.');
            } else {
                fila.find('.cic-resultado').removeClass('text-danger');
                fila.find('.cic-resultado').removeAttr('title');
            }
        } else {
            fila.find('.cic-resultado').val('');
            fila.find('.cic-resultado').removeClass('text-danger');
        }
    }

    // Event listeners para recalcular cuando cambien los valores (usando delegación de eventos)
    $(document).on('input', '.peso-muestra, .vol-naoh-muestra, .vol-naoh-blanco, .normalidad-naoh, .humedad-porcentaje', function() {
        var fila = $(this).closest('.fila-resultado');
        calcularCIC(fila);
    });

    // Calcular inicialmente todas las filas
    $('.fila-resultado').each(function() {
        calcularCIC($(this));
    });

    // Validación para evitar comas como separador decimal
    $('input[type="number"]').on('input', function(e) {
        let valor = $(this).val();
        if (valor.includes(',')) {
            alert('Por favor, utiliza punto (.) como separador decimal, no coma (,).');
            $(this).val(valor.replace(/,/g, '.'));
            $(this).focus();
        }
    });

    // Prevención al enviar el formulario
    $('form').on('submit', function(e) {
        let hayComa = false;
        $(this).find('input[type="number"]').each(function() {
            if ($(this).val().includes(',')) {
                hayComa = true;
                $(this).focus();
                return false;
            }
        });
        if (hayComa) {
            alert('No se permite el uso de comas como separador decimal. Por favor, usa punto (.)');
            e.preventDefault();
            return;
        }

        // Mostrar confirmación para procesamiento por lotes
        if (!confirm('¿Está seguro de que desea procesar los análisis con los datos ingresados?')) {
            e.preventDefault();
        }
    });

    // Bloquear la tecla coma en los campos numéricos
    $('input[type="number"]').on('keydown', function(e) {
        if (e.key === ',') {
            alert('No se permite el uso de comas como separador decimal. Usa punto (.)');
            e.preventDefault();
        }
    });

    // Función para calcular %ERROR (Control de Laboratorio - CRM/SRM)
    function calcularError() {
        var valorTeorico = parseFloat($('input[name="error_valor_teorico"]').val()) || 0;
        var valorLeido = parseFloat($('input[name="error_valor_leido"]').val()) || 0;
        
        if (valorTeorico > 0 && valorLeido > 0) {
            // Error Relativo (%) = |Valor Leído - Valor Teórico| / Valor Teórico × 100
            var errorRelativo = (Math.abs(valorLeido - valorTeorico) / valorTeorico) * 100;
            $('input[name="error_porcentaje"]').val(errorRelativo.toFixed(2));
        } else {
            $('input[name="error_porcentaje"]').val('');
        }
    }

    // Función para calcular %REC (Recuperación de Estándar - Spike Recovery)
    function calcularRecuperacion() {
        var valorTeorico = parseFloat($('input[name="recuperacion_valor_teorico"]').val()) || 0; // Cantidad Agregada
        var valorLeido = parseFloat($('input[name="recuperacion_valor_leido"]').val()) || 0; // Resultado Muestra Fortificada
        var resultadoMuestra = parseFloat($('#cic_resultado').val()) || 0; // Resultado Muestra sin fortificar
        
        if (valorTeorico > 0) {
            // % Recuperación = ((Resultado Muestra Fortificada - Resultado Muestra) / Cantidad Agregada) × 100
            var recuperacion = ((valorLeido - resultadoMuestra) / valorTeorico) * 100;
            $('input[name="recuperacion_porcentaje"]').val(recuperacion.toFixed(2));
        } else {
            $('input[name="recuperacion_porcentaje"]').val('');
        }
    }

    // Función para calcular %RPD (Duplicados)
    function calcularRPD() {
        var replica1 = parseFloat($('input[name="dpr_replica1"]').val()) || 0;
        var replica2 = parseFloat($('input[name="dpr_replica2"]').val()) || 0;
        
        if (replica1 > 0 && replica2 > 0) {
            var promedio = (replica1 + replica2) / 2;
            // % Diferencia = (|Muestra 1 - Muestra 2| / Promedio de ambas) × 100
            var rpd = (Math.abs(replica1 - replica2) / promedio) * 100;
            $('input[name="dpr_porcentaje"]').val(rpd.toFixed(2));
        } else {
            $('input[name="dpr_porcentaje"]').val('');
        }
    }

    // Función para evaluar criterios de aceptación
    function evaluarCriterios() {
        // Blanco Método: < 1mL
        var blancoValorLeido = parseFloat($('input[name="blanco_valor_leido"]').val()) || 0;
        if (blancoValorLeido > 0) {
            var esAceptable = (blancoValorLeido < 1);
            $('input[name="blanco_aceptable"]').val(esAceptable ? 'Aceptable' : 'No aceptable');
        } else {
            $('input[name="blanco_aceptable"]').val('');
        }

        // Control de Laboratorio: Error Relativo < 20%
        var errorRelativo = parseFloat($('input[name="error_porcentaje"]').val()) || 0;
        if (errorRelativo >= 0) {
            var esAceptable = (errorRelativo <= 20);
            $('input[name="error_aceptable"]').val(esAceptable ? 'Aceptable' : 'No aceptable');
        } else {
            $('input[name="error_aceptable"]').val('');
        }

        // Recuperación de Estándar: 70-130%
        var recuperacion = parseFloat($('input[name="recuperacion_porcentaje"]').val()) || 0;
        if (recuperacion >= 0) {
            var esAceptable = (recuperacion >= 70 && recuperacion <= 130);
            $('input[name="recuperacion_aceptable"]').val(esAceptable ? 'Aceptable' : 'No aceptable');
        } else {
            $('input[name="recuperacion_aceptable"]').val('');
        }

        // Duplicados: RPD < 25%
        var diferencia = parseFloat($('input[name="dpr_porcentaje"]').val()) || 0;
        if (diferencia >= 0) {
            var esAceptable = (diferencia <= 25);
            $('input[name="dpr_aceptable"]').val(esAceptable ? 'Aceptable' : 'No aceptable');
        } else {
            $('input[name="dpr_aceptable"]').val('');
        }
    }

    // Event listeners para los cálculos de control de calidad
    $('input[name="blanco_valor_leido"]').on('input', function() {
        setTimeout(evaluarCriterios, 100);
    });
    $('input[name="error_valor_teorico"], input[name="error_valor_leido"]').on('input', function() {
        calcularError();
        setTimeout(evaluarCriterios, 100);
    });
    $('input[name="recuperacion_valor_teorico"], input[name="recuperacion_valor_leido"]').on('input', function() {
        calcularRecuperacion();
        setTimeout(evaluarCriterios, 100);
    });
    $('input[name="dpr_replica1"], input[name="dpr_replica2"]').on('input', function() {
        calcularRPD();
        setTimeout(evaluarCriterios, 100);
    });

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
});
</script>
@endpush 