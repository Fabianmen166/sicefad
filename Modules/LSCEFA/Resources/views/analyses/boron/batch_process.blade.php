@extends('lscefa::layouts.technical')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Boro (Lote)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.panel') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.boron.index') }}">Gestión de Boro</a></li>
                        <li class="breadcrumb-item active">Procesar Lote</li>
                    </ol>
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
            @if($pendingProcesses->isEmpty())
                <div class="alert alert-danger">
                    Error: No hay análisis de Boro pendientes para procesar.
                </div>
                <a href="{{ route('lscefa.technical.analyses.boron.index') }}" class="btn btn-secondary">Regresar</a>
            @else
                @php $firstProcess = $pendingProcesses->first(); @endphp
                <!-- Información General del Proceso -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if(isset($boronAnalysis) && $boronAnalysis->review_status === 'rejected')
                                <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Análisis Rechazado - Edición</span>
                            @else
                                Información del Proceso
                            @endif
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p><strong>ID Proceso:</strong> {{ $pendingProcesses->pluck('process_id')->unique()->implode(', ') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha de Solicitud:</strong> {{ $firstProcess->created_at ? $firstProcess->created_at->format('d/m/Y') : 'N/A' }}</p>
                                <p><strong>Servicio:</strong> Boro</p>
                                @if(isset($boronAnalysis) && $boronAnalysis->review_status === 'rejected')
                                    <p><strong>Estado:</strong> <span class="badge badge-warning">Rechazado</span></p>
                                    @if($boronAnalysis->review_observations)
                                        <p><strong>Observaciones de Rechazo:</strong> <span class="text-danger">{{ $boronAnalysis->review_observations }}</span></p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('lscefa.technical.analyses.boron.batch_store') }}" method="POST" id="boronBatchForm">
                    @csrf
                    @if(isset($boronAnalysis) && $boronAnalysis->review_status === 'rejected')
                        <input type="hidden" name="rejected_analysis_id" value="{{ $boronAnalysis->id }}">
                    @endif
                    @foreach ($pendingProcesses as $index => $process)
                        <input type="hidden" name="process_ids[]" value="{{ $process->process_id }}">
                    @endforeach
                    
                    <!-- Tabla de Datos del Análisis (DENTRO DEL FORMULARIO) -->
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless align-middle" style="background: #f8f9fa; border-radius: 8px;">
                            <tr>
                                <td class="fw-bold" style="width: 10%">Consecutivo:</td>
                                <td style="width: 18%"><input type="text" class="form-control" name="consecutivo_no" value="{{ old('consecutivo_no', isset($boronAnalysis) ? $boronAnalysis->consecutive_no : '') }}"></td>
                                <td class="fw-bold" style="width: 16%">Metodología aplicada:</td>
                                <td colspan="2" style="width: 30%"><input type="text" class="form-control" name="metodologia_aplicada" value="{{ old('metodologia_aplicada', isset($boronAnalysis) ? $boronAnalysis->applied_methodology : 'Extracción por Bray (II) y cuantificación por ácido ascórbico') }}"></td>
                                <td class="fw-bold" style="width: 10%">Intervalo:</td>
                                <td style="width: 16%"><input type="text" class="form-control" name="intervalo_metodo" value="{{ old('intervalo_metodo', isset($boronAnalysis) ? $boronAnalysis->method_interval : '') }}"></td>
                            </tr>
                            <tr style="height: 10px;"></tr>
                            <tr>
                                <td class="fw-bold">Fecha:</td>
                                <td><input type="date" class="form-control" name="fecha_analisis" value="{{ old('fecha_analisis', isset($boronAnalysis) ? $boronAnalysis->analysis_date : '') }}"></td>
                                <td class="fw-bold">Equipo:</td>
                                <td><input type="text" class="form-control" name="equipo_utilizado" value="{{ old('equipo_utilizado', isset($boronAnalysis) ? $boronAnalysis->equipment_used : '') }}"></td>
                                <td></td>
                                <td class="fw-bold">Analista:</td>
                                <td><input type="text" class="form-control" name="nombre_analista" value="{{ old('nombre_analista', isset($boronAnalysis) ? $boronAnalysis->analyst_name : '') }}"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Horizontal Navigation Bar -->
                    <div class="mt-4 mb-3">
                        <ul class="nav nav-tabs nav-fill" id="analysisTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="controls-tab" data-bs-toggle="tab" data-bs-target="#controls-content" type="button" role="tab" aria-controls="controls-content" aria-selected="true">
                                    <i class="fas fa-check-circle"></i> Controles Analíticos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-content" type="button" role="tab" aria-controls="items-content" aria-selected="false">
                                    <i class="fas fa-flask"></i> Items de Ensayo
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="analysisTabContent">
                        <!-- Controles Analíticos Tab -->
                        <div class="tab-pane fade show active" id="controls-content" role="tabpanel" aria-labelledby="controls-tab">
                            <!-- Controles Analíticos (primero) -->
                            <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Controles Analíticos (aplican a todo el lote)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="controles_analiticos_table">
                                    <thead>
                                        <tr>
                                            <th>Identificación</th>
                                            <th>Valor esperado</th>
                                            <th>Valor leído</th>
                                            <th>% Error</th>
                                            <th>Aceptabilidad</th>
                                            <th>% Recuperación</th>
                                            <th>Aceptabilidad</th>
                                            <th>% DPR</th>
                                            <th>Aceptabilidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" class="form-control" name="controles_analiticos[0][identificacion]" value="Estándar A"></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][valor_esperado]" value="{{ isset($boronAnalysis) ? $boronAnalysis->standard_a_expected_value : '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][valor_leido]" value="{{ isset($boronAnalysis) ? $boronAnalysis->standard_a_read_value : '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_error]" readonly></td>
                                            <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_error]" readonly></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_recuperacion]" readonly></td>
                                            <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_recuperacion]" readonly></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_dpr]" readonly></td>
                                            <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_dpr]" readonly></td>
                                        </tr>
                                        <tr>
                                            <td><input type="text" class="form-control" name="controles_analiticos[1][identificacion]" value="Estándar B"></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][valor_esperado]" value="{{ isset($boronAnalysis) ? $boronAnalysis->standard_b_expected_value : '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][valor_leido]" value="{{ isset($boronAnalysis) ? $boronAnalysis->standard_b_read_value : '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_error]" readonly></td>
                                            <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_error]" readonly></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_recuperacion]" readonly></td>
                                            <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_recuperacion]" readonly></td>
                                            <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_dpr]" readonly></td>
                                            <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_dpr]" readonly></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Curva de Calibración y Duplicados (segundo) -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered" id="curva_duplicados_table">
                            <thead>
                                <tr>
                                    <th>Curva de calibración</th>
                                    <th>Valor</th>
                                    <th>Valor leído</th>
                                    <th>% ERROR</th>
                                    <th>Aceptabilidad</th>
                                    <th>Duplicado</th>
                                    <th>Valor leído</th>
                                    <th>% DPR</th>
                                    <th>Aceptabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td rowspan="2">Curva de calibración</td>
                                    <td rowspan="2"><input type="number" class="form-control" value="0.995" readonly></td>
                                    <td rowspan="2"><input type="number" step="any" class="form-control" name="curva_valor_leido" id="curva_valor_leido" value="{{ isset($boronAnalysis) ? $boronAnalysis->calibration_curve_read_value : '' }}"></td>
                                    <td rowspan="2"><input type="number" step="any" class="form-control" name="curva_error_porcentaje" id="curva_error_porcentaje" readonly></td>
                                    <td rowspan="2"><input type="text" class="form-control" name="curva_aceptabilidad" id="curva_aceptabilidad" readonly></td>
                                    <td>Duplicado A</td>
                                    <td><input type="number" step="any" class="form-control" id="duplicado_a" name="duplicado_a" value="{{ isset($boronAnalysis) ? $boronAnalysis->duplicate_a_value : '' }}"></td>
                                    <td rowspan="2"><input type="number" step="any" class="form-control" id="dpr_resultado" name="dpr_resultado" readonly></td>
                                    <td rowspan="2"><input type="text" class="form-control" id="dpr_aceptabilidad" name="dpr_aceptabilidad" readonly></td>
                                </tr>
                                <tr>
                                    <td>Duplicado B</td>
                                    <td><input type="number" step="any" class="form-control" id="duplicado_b" name="duplicado_b" value="{{ isset($boronAnalysis) ? $boronAnalysis->duplicate_b_value : '' }}"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                        </div>
                        <!-- End of Controles Analíticos Tab -->

                        <!-- Items de Ensayo Tab -->
                        <div class="tab-pane fade" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                            <!-- Ítems de Ensayo -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Ítems de Ensayo</h3>
                                </div>
                                <div class="card-body">
                            <div class="alert alert-info">
                                Complete los valores para los análisis seleccionados. El cálculo de Boro disponible (mg/kg) es automático.
                            </div>
                            <table class="table table-bordered" id="items_ensayo_table">
                                <thead>
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Código interno</th>
                                        <th>Peso muestra (g)</th>
                                        <th>pW</th>
                                        <th>V. Extractante (mL)</th>
                                        <th>Lectura blanco</th>
                                        <th>Factor de dilución (fd)</th>
                                        <th>Boro disponible (mg/L)</th>
                                        <th>Boro disponible (mg/kg)</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingProcesses as $index => $process)
                                        @php
                                            $testItem = null;
                                            if (isset($boronAnalysis) && isset($boronAnalysis->test_items) && is_array($boronAnalysis->test_items) && isset($boronAnalysis->test_items[$index])) {
                                                $testItem = $boronAnalysis->test_items[$index];
                                            }
                                            // Debug: mostrar qué datos tenemos
                                            if (isset($boronAnalysis) && $boronAnalysis->review_status === 'rejected') {
                                                \Log::info('Debug test_items en vista:', [
                                                    'index' => $index,
                                                    'test_items_exists' => isset($boronAnalysis->test_items),
                                                    'test_items_type' => gettype($boronAnalysis->test_items),
                                                    'test_items_count' => is_array($boronAnalysis->test_items) ? count($boronAnalysis->test_items) : 'NO_ARRAY',
                                                    'current_testItem' => $testItem
                                                ]);
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $process->process_id }}</td>
                                            <td><input type="text" class="form-control" name="items_ensayo[{{$index}}][codigo_interno]" value="{{ $testItem['internal_code'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][peso_muestra]" value="{{ $testItem['sample_weight'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][pw]" value="{{ $testItem['pw'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][v_extractante]" value="{{ $testItem['extractant_volume'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][lectura_blanco]" value="{{ $testItem['blank_reading'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][factor_dilucion]" value="{{ $testItem['dilution_factor'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][boro_disponible_mg_l]" value="{{ $testItem['available_boron_mg_l'] ?? '' }}"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[{{$index}}][boro_disponible_mg_kg]" readonly value="{{ $testItem['available_boron_mg_kg'] ?? '' }}"></td>
                                            <td><input type="text" class="form-control" name="items_ensayo[{{$index}}][observaciones_item]" value="{{ $testItem['observations'] ?? '' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                            <!-- End of Items de Ensayo Tab -->
                        </div>
                        <!-- End of Tab Content -->

                        <button type="submit" class="btn btn-primary">
                            @if(isset($boronAnalysis) && $boronAnalysis->review_status === 'rejected')
                                <i class="fas fa-save"></i> Actualizar Análisis de Boro Rechazado
                            @else
                                <i class="fas fa-save"></i> Guardar Análisis de Boro (Lote)
                            @endif
                        </button>
                </form>
            @endif
        </div>
    </section>
</div>

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
        // Cálculo automático de Boro disponible (mg/kg)
        function calcularBoroEnsayo() {
            $('#items_ensayo_table tbody tr').each(function() {
                const boroMgL = parseFloat($(this).find('input[name$="[boro_disponible_mg_l]"]').val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat($(this).find('input[name$="[peso_muestra]"]').val().replace(',', '.')) || 0;
                const vExtractante = parseFloat($(this).find('input[name$="[v_extractante]"]').val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat($(this).find('input[name$="[factor_dilucion]"]').val().replace(',', '.')) || 0;
                const pw = parseFloat($(this).find('input[name$="[pw]"]').val().replace(',', '.')) || 0;
                
                let boroMgKg = "";
                if (boroMgL === 0) {
                    boroMgKg = "";
                } else {
                    // Fórmula exacta según Excel: ((H18*E18*G18)/C18*(100+D18)/100)
                    // Donde: H18=boroMgL, E18=vExtractante, G18=factorDilucion, C18=pesoMuestra, D18=pw
                    boroMgKg = ((boroMgL * vExtractante * factorDilucion) / pesoMuestra * (100 + pw) / 100);
                }
                $(this).find('input[name$="[boro_disponible_mg_kg]"]').val(boroMgKg === "" ? '' : boroMgKg.toFixed(2));
            });
        }
        $(document).on('input', '#items_ensayo_table input', calcularBoroEnsayo);
        calcularBoroEnsayo();

        // Cálculos automáticos para controles analíticos (exactamente dos filas)
        function calcularControlesAnaliticos() {
            // Para cada fila: error, recuperación y aceptabilidad
            for (let i = 0; i < 2; i++) {
                let row = $('#controles_analiticos_table tbody tr').eq(i);
                const valorEsperado = parseFloat(row.find('input[name$="[valor_esperado]"]').val().replace(',', '.')) || 0;
                const valorLeido = parseFloat(row.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
                // % de Error
                let porcentajeError = 0;
                if (valorEsperado !== 0) {
                    porcentajeError = Math.abs((valorLeido - valorEsperado) / valorEsperado) * 100;
                }
                row.find('input[name$="[porcentaje_error]"]').val(porcentajeError.toFixed(2));
                // Aceptabilidad (error)
                let aceptabilidadError = (porcentajeError <= 20) ? 'Aceptable' : 'No aceptable';
                row.find('input[name$="[aceptabilidad_error]"]').val(aceptabilidadError);
                // % Recuperación
                let porcentajeRecuperacion = 0;
                if (valorEsperado !== 0) {
                    porcentajeRecuperacion = (valorLeido / valorEsperado) * 100;
                }
                row.find('input[name$="[porcentaje_recuperacion]"]').val(porcentajeRecuperacion.toFixed(2));
                // Aceptabilidad (recuperación)
                let aceptabilidadRec = (porcentajeRecuperacion >= 80 && porcentajeRecuperacion <= 120) ? 'Aceptable' : 'No aceptable';
                row.find('input[name$="[aceptabilidad_recuperacion]"]').val(aceptabilidadRec);
            }
            // % DPR entre las dos filas
            let row0 = $('#controles_analiticos_table tbody tr').eq(0);
            let row1 = $('#controles_analiticos_table tbody tr').eq(1);
            const valorLeido0 = parseFloat(row0.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
            const valorLeido1 = parseFloat(row1.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
            let promedio = (valorLeido0 + valorLeido1) / 2;
            let dpr = 0;
            if (promedio !== 0) {
                dpr = Math.abs(valorLeido0 - valorLeido1) / promedio * 100;
            }
            row0.find('input[name$="[porcentaje_dpr]"]').val(dpr.toFixed(2));
            row1.find('input[name$="[porcentaje_dpr]"]').val(dpr.toFixed(2));
            let aceptabilidadDpr = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
            row0.find('input[name$="[aceptabilidad_dpr]"]').val(aceptabilidadDpr);
            row1.find('input[name$="[aceptabilidad_dpr]"]').val(aceptabilidadDpr);
        }
        $(document).on('input', '#controles_analiticos_table input', calcularControlesAnaliticos);
        calcularControlesAnaliticos();

        // Cálculo automático de % DPR y aceptabilidad para duplicados (curva)
        function calcularDPR() {
            const a = parseFloat($('#duplicado_a').val().replace(',', '.')) || 0;
            const b = parseFloat($('#duplicado_b').val().replace(',', '.')) || 0;
            let dpr = 0;
            let aceptabilidad = '';
            if ((a + b) !== 0) {
                let promedio = (a + b) / 2;
                dpr = Math.abs(a - b) / promedio * 100;
                aceptabilidad = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
            }
            $('#dpr_resultado').val(dpr.toFixed(2));
            $('#dpr_aceptabilidad').val(aceptabilidad);
        }
        $(document).on('input', '#duplicado_a, #duplicado_b', calcularDPR);
        calcularDPR();

        // Cálculo automático de % ERROR para curva de calibración
        function calcularErrorCurva() {
            const valorEsperado = 0.995; // Valor fijo de la curva
            const valorLeido = parseFloat($('#curva_valor_leido').val().replace(',', '.')) || 0;
            let errorPorcentaje = 0;
            let aceptabilidad = '';
            
            if (valorEsperado !== 0) {
                errorPorcentaje = Math.abs(valorLeido - valorEsperado) / valorEsperado * 100;
            }
            
            $('#curva_error_porcentaje').val(errorPorcentaje.toFixed(2));
            
            // Aceptabilidad para curva de calibración
            if (valorLeido < 0.995) {
                aceptabilidad = 'No aceptable';
            } else {
                aceptabilidad = 'Aceptable';
            }
            $('#curva_aceptabilidad').val(aceptabilidad);
        }
        $(document).on('input', '#curva_valor_leido', calcularErrorCurva);
        calcularErrorCurva();

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
