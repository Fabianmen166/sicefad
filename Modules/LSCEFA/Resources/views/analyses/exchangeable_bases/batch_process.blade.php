@extends('lscefa::layouts.technical')

@section('title', 'Procesamiento por Lotes - Bases Cambiables')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-flask"></i> Procesamiento por Lotes - Análisis de Bases Cambiables
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('lscefa.technical.analyses.exchangeable_bases.batch_store') }}" method="POST" id="batchExchangeableBasesForm">
                        @csrf
                        
                        <!-- Datos del Análisis -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Datos del Análisis</h3>
                            </div>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless align-middle" id="datos_analisis_excel" style="background: #f8f9fa; border-radius: 8px;">
                                    <tr>
                                        <td class="fw-bold" style="width: 10%; background-color: #e9ecef;">Consecutivo No:</td>
                                        <td style="width: 18%"><input type="text" class="form-control" name="consecutivo_no" value="{{ old('consecutivo_no', '1') }}"></td>
                                        <td class="fw-bold" style="width: 16%; background-color: #e9ecef;">Método:</td>
                                        <td style="width: 18%"><input type="text" class="form-control" name="metodo" value="{{ old('metodo') }}"></td>
                                        <td class="fw-bold" style="width: 10%; background-color: #e9ecef;">Intervalo:</td>
                                        <td style="width: 16%"><input type="text" class="form-control" name="intervalo_metodo" value="{{ old('intervalo_metodo') }}"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold" style="background-color: #e9ecef;">Fecha del análisis:</td>
                                        <td><input type="date" class="form-control" name="fecha_analisis" value="{{ old('fecha_analisis', now()->format('Y-m-d')) }}"></td>
                                        <td class="fw-bold" style="background-color: #e9ecef;">Equipo utilizado:</td>
                                        <td><input type="text" class="form-control" name="equipo_utilizado" value="{{ old('equipo_utilizado') }}"></td>
                                        <td class="fw-bold" style="background-color: #e9ecef;">Nombre Analista:</td>
                                        <td><input type="text" class="form-control" name="nombre_analista" value="{{ old('nombre_analista', Auth::user()->name) }}"></td>
                                    </tr>
                                </table>
                            </div>
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
                                <!-- Controles Analíticos -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-flask mr-2" style="color: #28a745;"></i>Controles Analíticos</h4>
                                    </div>
                                    <div class="card-body">

                                <!-- 1. Blanco del método -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-vial mr-2"></i>Blanco del método</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">Blanco del Método</th>
                                                        <th class="text-center">Resultado mg/kg</th>
                                                        <th class="text-center">LCM</th>
                                                        <th class="text-center">Aceptable/no aceptable</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[0][identificacion]" value="Blanco Na"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[0][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[0][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[0][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[1][identificacion]" value="Blanco K"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[1][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[1][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[1][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[2][identificacion]" value="Blanco Ca"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[2][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[2][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[2][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[3][identificacion]" value="Blanco Mg"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[3][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[3][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[3][aceptabilidad]" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Duplicado muestra -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-copy mr-2"></i>Duplicado muestra</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="duplicado-table">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">Identificación muestra</th>
                                                        <th class="text-center">Replica 1</th>
                                                        <th class="text-center">Replica 2</th>
                                                        <th class="text-center">% DPR</th>
                                                        <th class="text-center">Elemento</th>
                                                        <th class="text-center">% DPR</th>
                                                        <th class="text-center">Aceptable/no aceptable</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[0][identificacion_muestra]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[0][replica_1]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[0][replica_2]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[0][dpr_1]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[0][elemento]" value="Na"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[0][dpr_2]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[0][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[1][identificacion_muestra]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[1][replica_1]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[1][replica_2]"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[1][dpr_1]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[1][elemento]" value="K"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[1][dpr_2]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[1][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[2][identificacion_muestra]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[2][replica_1]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[2][replica_2]"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[2][dpr_1]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[2][elemento]" value="Ca"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[2][dpr_2]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[2][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[3][identificacion_muestra]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[3][replica_1]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="duplicado_muestra[3][replica_2]"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[3][dpr_1]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[3][elemento]" value="Mg"></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[3][dpr_2]" readonly></td>
                                                        <td><input type="text" class="form-control" name="duplicado_muestra[3][aceptabilidad]" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Controles de calidad (Exactitud) -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-check-circle mr-2"></i>Controles de calidad (Exactitud)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">Identificación</th>
                                                        <th class="text-center">Valor esperado mg/kg</th>
                                                        <th class="text-center">Valor leído mg/kg</th>
                                                        <th class="text-center">% Recuperación</th>
                                                        <th class="text-center">Aceptable/no aceptable</th>
                                                        <th class="text-center">OBSERVACIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="controles_calidad[0][identificacion]" value="Material de Referencia o MRC Na"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[0][valor_esperado]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[0][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[0][porcentaje_recuperacion]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[0][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[0][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="controles_calidad[1][identificacion]" value="Material de Referencia o MRC K"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[1][valor_esperado]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[1][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[1][porcentaje_recuperacion]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[1][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[1][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="controles_calidad[2][identificacion]" value="Material de Referencia o MRC Ca"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[2][valor_esperado]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[2][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[2][porcentaje_recuperacion]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[2][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[2][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="controles_calidad[3][identificacion]" value="Material de Referencia o MRC Mg"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[3][valor_esperado]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[3][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[3][porcentaje_recuperacion]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[3][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_calidad[3][observaciones]"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Control de estándar (Exactitud) -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-star mr-2"></i>Control de estándar (Exactitud)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">Estandar</th>
                                                        <th class="text-center">Concentración mg/L</th>
                                                        <th class="text-center">Valor Leído mg/L</th>
                                                        <th class="text-center">% Error</th>
                                                        <th class="text-center">Aceptable / No Aceptable</th>
                                                        <th class="text-center">Observaciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[0][estandar]" value="Na"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[0][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[0][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[0][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[0][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[0][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[1][estandar]" value="K"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[1][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[1][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[1][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[1][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[1][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[2][estandar]" value="Ca"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[2][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[2][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[2][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[2][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[2][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[3][estandar]" value="Mg"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[3][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[3][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[3][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[3][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[3][observaciones]"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Curva de calibración -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Curva de calibración</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">Elemento</th>
                                                        <th class="text-center">R² Obtenido</th>
                                                        <th class="text-center">R² Esperado</th>
                                                        <th class="text-center">Aceptable / No Aceptable</th>
                                                        <th class="text-center">Observaciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[0][elemento]" value="Na"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[0][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[0][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[0][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[0][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[1][elemento]" value="K"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[1][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[1][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[1][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[1][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[2][elemento]" value="Ca"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[2][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[2][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[2][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[2][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[3][elemento]" value="Mg"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[3][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[3][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[3][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[3][observaciones]"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                            </div>
                            <!-- End of Controles Analíticos Tab -->

                            <!-- Items de Ensayo Tab -->
                            <div class="tab-pane fade" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                                <!-- Cotizaciones Seleccionadas -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-list"></i> Cotizaciones a Procesar</h4>
                                    </div>
                                    <div class="card-body">
                                @foreach($pendingProcesses as $process)
                                <div class="process-item mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Cotización: {{ $process->quote_id }}</h5>
                                            <input type="hidden" name="process_ids[]" value="{{ $process->process_id }}">
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Cliente:</strong> {{ $process->customer->nombre ?? 'N/A' }}</p>
                                                    <p><strong>Servicio:</strong> 
                                                        @foreach($process->serviceProcessDetails as $detail)
                                                            @if(str_contains(strtolower($detail->service->descripcion), 'bases cambiables') || 
                                                                str_contains(strtolower($detail->service->descripcion), 'exchangeable bases') ||
                                                                str_contains(strtolower($detail->service->descripcion), 'intercambio cationico') ||
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

                                            <!-- Items para esta cotización -->
                                            <div class="items-container">
                                                <h6>Items de Ensayo</h6>
                                                <div class="table-responsive" style="overflow-x: auto; min-width: 100%;">
                                                    <table class="table table-bordered table-hover" id="items_ensayo_table" style="min-width: 1800px;">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th rowspan="2" class="text-center">#</th>
                                                                <th rowspan="2" class="text-center">Código interno</th>
                                                                <th rowspan="2" class="text-center">Peso muestra (g)</th>
                                                                <th rowspan="2" class="text-center">Humedad (%)</th>
                                                                <th rowspan="2" class="text-center">Volumen final (mL)</th>
                                                                <th colspan="4" class="text-center">Na</th>
                                                                <th colspan="4" class="text-center">K</th>
                                                                <th colspan="4" class="text-center">Ca</th>
                                                                <th colspan="4" class="text-center">Mg</th>
                                                                <th rowspan="2" class="text-center">Observaciones</th>
                                                                <th rowspan="2" class="text-center">Acciones</th>
                                                            </tr>
                                                            <tr>
                                                                <th class="text-center">Lectura (mg/L)</th>
                                                                <th class="text-center">Blanco</th>
                                                                <th class="text-center">Factor dilución</th>
                                                                <th class="text-center">Resultados cmol(+)/kg</th>
                                                                <th class="text-center">Lectura (mg/L)</th>
                                                                <th class="text-center">Blanco</th>
                                                                <th class="text-center">Factor dilución</th>
                                                                <th class="text-center">Resultados cmol(+)/kg</th>
                                                                <th class="text-center">Lectura (mg/L)</th>
                                                                <th class="text-center">Blanco</th>
                                                                <th class="text-center">Factor dilución</th>
                                                                <th class="text-center">Resultados cmol(+)/kg</th>
                                                                <th class="text-center">Lectura (mg/L)</th>
                                                                <th class="text-center">Blanco</th>
                                                                <th class="text-center">Factor dilución</th>
                                                                <th class="text-center">Resultados cmol(+)/kg</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="fila-muestra">
                                                                <td class="numero-fila text-center">1</td>
                                                                <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[{{ $process->process_id }}][0][codigo_interno]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][peso_muestra]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="items_ensayo[{{ $process->process_id }}][0][humedad]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="items_ensayo[{{ $process->process_id }}][0][volumen_final]"></td>
                                                                <!-- Na -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][na_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][na_blanco]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][na_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][na_resultado]" readonly></td>
                                                                <!-- K -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][k_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][k_blanco]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][k_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][k_resultado]" readonly></td>
                                                                <!-- Ca -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][ca_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][ca_blanco]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][ca_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][ca_resultado]" readonly></td>
                                                                <!-- Mg -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][mg_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][mg_blanco]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][mg_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][mg_resultado]" readonly></td>
                                                                <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[{{ $process->process_id }}][0][observaciones]"></td>
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
                            <!-- End of Items de Ensayo Tab -->
                        </div>
                        <!-- End of Tab Content -->

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Análisis por Lotes
                                </button>
                                <a href="{{ route('lscefa.technical.analyses.exchangeable_bases.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

<script>
let itemIndices = {};
let duplicadoIndex = {{ count($pendingProcesses) }};

// Inicializar índices de items para cada cotización
@foreach($pendingProcesses as $process)
    itemIndices['{{ $process->process_id }}'] = 1;
@endforeach

function addItemToProcess(processId) {
    const tbody = document.querySelector(`input[name="items_ensayo[${processId}][0][codigo_interno]"]`).closest('tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'fila-muestra';
    newRow.innerHTML = `
        <td class="numero-fila text-center">${itemIndices[processId] + 1}</td>
        <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[${processId}][${itemIndices[processId]}][codigo_interno]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][peso_muestra]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="items_ensayo[${processId}][${itemIndices[processId]}][humedad]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="items_ensayo[${processId}][${itemIndices[processId]}][volumen_final]"></td>
        <!-- Na -->
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][na_lectura]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][na_blanco]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][na_factor]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[${processId}][${itemIndices[processId]}][na_resultado]" readonly></td>
        <!-- K -->
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][k_lectura]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][k_blanco]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][k_factor]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[${processId}][${itemIndices[processId]}][k_resultado]" readonly></td>
        <!-- Ca -->
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][ca_lectura]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][ca_blanco]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][ca_factor]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[${processId}][${itemIndices[processId]}][ca_resultado]" readonly></td>
        <!-- Mg -->
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][mg_lectura]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][mg_blanco]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[${processId}][${itemIndices[processId]}][mg_factor]"></td>
        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[${processId}][${itemIndices[processId]}][mg_resultado]" readonly></td>
        <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[${processId}][${itemIndices[processId]}][observaciones]"></td>
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

// Se eliminó la función addDuplicadoRow porque la tabla no necesita agregar filas dinámicamente en batch

// Event listener para remover filas de duplicado
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-duplicado-row') || e.target.closest('.remove-duplicado-row')) {
        const button = e.target.classList.contains('remove-duplicado-row') ? e.target : e.target.closest('.remove-duplicado-row');
        button.closest('tr').remove();
    }
});

// Cálculos automáticos
document.addEventListener('DOMContentLoaded', function() {
    // Cálculos para blanco del método
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('blanco-resultado') || e.target.classList.contains('blanco-lcm')) {
            const row = e.target.closest('tr');
            const resultado = parseFloat(row.querySelector('.blanco-resultado').value) || 0;
            const lcm = parseFloat(row.querySelector('.blanco-lcm').value) || 0;
            const aceptabilidad = row.querySelector('.blanco-aceptabilidad');
            
            if (resultado < lcm) {
                aceptabilidad.value = 'Aceptable';
            } else {
                aceptabilidad.value = 'No aceptable';
            }
        }
    });

    // Cálculos para duplicado de muestra (igual que en process)
    function calcularDPRDuplicado() {
        try {
            document.querySelectorAll('#duplicado-table tbody tr').forEach(function(row){
                const replica1 = parseFloat((row.querySelector('input[name*="[replica_1]"]')?.value || '').replace(',', '.')) || 0;
                const replica2 = parseFloat((row.querySelector('input[name*="[replica_2]"]')?.value || '').replace(',', '.')) || 0;
                const dpr1Input = row.querySelector('input[name*="[dpr_1]"]');
                const dpr2Input = row.querySelector('input[name*="[dpr_2]"]');
                const aceptabilidadInput = row.querySelector('input[name*="[aceptabilidad]"]');

                let dpr = 0;
                let aceptabilidad = '';
                if (replica1 > 0 || replica2 > 0) {
                    const promedio = (replica1 + replica2) / 2;
                    if (promedio > 0) {
                        dpr = Math.abs(replica1 - replica2) / promedio * 100;
                        aceptabilidad = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
                    }
                }
                if (dpr1Input) dpr1Input.value = dpr.toFixed(2);
                if (dpr2Input) dpr2Input.value = dpr.toFixed(2);
                if (aceptabilidadInput) aceptabilidadInput.value = aceptabilidad;
            });
        } catch (err) {
            console.log('Error calcularDPRDuplicado (batch):', err);
        }
    }

    document.addEventListener('input', function(e){
        if (e.target && (e.target.name?.includes('[replica_1]') || e.target.name?.includes('[replica_2]'))) {
            calcularDPRDuplicado();
        }
    });
    calcularDPRDuplicado();

    // Cálculos para controles de calidad (Exactitud)
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('controles_calidad') && (e.target.name.includes('valor_esperado') || e.target.name.includes('valor_leido'))) {
            const row = e.target.closest('tr');
            const valorEsperado = parseFloat(row.querySelector('input[name*="valor_esperado"]').value) || 0;
            const valorLeido = parseFloat(row.querySelector('input[name*="valor_leido"]').value) || 0;
            const recuperacionInput = row.querySelector('input[name*="porcentaje_recuperacion"]');
            const aceptabilidadInput = row.querySelector('input[name*="aceptabilidad"]');
            
            if (valorEsperado > 0) {
                const recuperacion = (valorLeido / valorEsperado) * 100;
                recuperacionInput.value = recuperacion.toFixed(2);
                
                if (recuperacion >= 90 && recuperacion <= 110) {
                    aceptabilidadInput.value = 'Aceptable';
                } else {
                    aceptabilidadInput.value = 'No aceptable';
                }
            }
        }
    });

    // Cálculos para control de estándar (Exactitud)
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('control_estandar') && (e.target.name.includes('concentracion') || e.target.name.includes('valor_leido'))) {
            const row = e.target.closest('tr');
            const concentracion = parseFloat(row.querySelector('input[name*="concentracion"]').value) || 0;
            const valorLeido = parseFloat(row.querySelector('input[name*="valor_leido"]').value) || 0;
            const errorInput = row.querySelector('input[name*="porcentaje_error"]');
            const aceptabilidadInput = row.querySelector('input[name*="aceptabilidad"]');
            
            if (concentracion > 0) {
                const error = Math.abs((valorLeido - concentracion) / concentracion) * 100;
                errorInput.value = error.toFixed(2);
                
                if (error <= 10) {
                    aceptabilidadInput.value = 'Aceptable';
                } else {
                    aceptabilidadInput.value = 'No aceptable';
                }
            }
        }
    });

    // Cálculos para curva de calibración
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('curva_calibracion') && e.target.name.includes('r2_obtenido')) {
            const row = e.target.closest('tr');
            const r2Obtenido = parseFloat(e.target.value) || 0;
            const r2Esperado = parseFloat(row.querySelector('input[name*="r2_esperado"]').value) || 0.995;
            const aceptabilidadInput = row.querySelector('input[name*="aceptabilidad"]');
            
            if (r2Obtenido >= r2Esperado) {
                aceptabilidadInput.value = 'Aceptable';
            } else if (r2Obtenido > 0) {
                aceptabilidadInput.value = 'No aceptable';
            }
        }
    });

    // Cálculos para items de bases intercambiables
    document.addEventListener('input', function(e) {
        if (e.target.name && (e.target.name.includes('na_lectura') || e.target.name.includes('na_blanco') || e.target.name.includes('na_factor') ||
                              e.target.name.includes('k_lectura') || e.target.name.includes('k_blanco') || e.target.name.includes('k_factor') ||
                              e.target.name.includes('ca_lectura') || e.target.name.includes('ca_blanco') || e.target.name.includes('ca_factor') ||
                              e.target.name.includes('mg_lectura') || e.target.name.includes('mg_blanco') || e.target.name.includes('mg_factor') ||
                              e.target.name.includes('peso_muestra') || e.target.name.includes('humedad') || e.target.name.includes('volumen_final'))) {
            
            const itemRow = e.target.closest('tr');
            
            // Obtener valores comunes
            const pesoMuestra = parseFloat(itemRow.querySelector('input[name*="peso_muestra"]').value) || 0;
            const humedad = parseFloat(itemRow.querySelector('input[name*="humedad"]').value) || 0;
            const volumenFinal = parseFloat(itemRow.querySelector('input[name*="volumen_final"]').value) || 0;
            
            // Equivalentes de conversión para cada catión
            const equivalentes = {
                'Na': 23,
                'K': 39,
                'Ca': 20,
                'Mg': 12.16
            };
            
            // Calcular para cada catión
            ['na', 'k', 'ca', 'mg'].forEach(function(cation) {
                const lecturaInput = itemRow.querySelector(`input[name*="${cation}_lectura"]`);
                const blancoInput = itemRow.querySelector(`input[name*="${cation}_blanco"]`);
                const factorInput = itemRow.querySelector(`input[name*="${cation}_factor"]`);
                const resultadoInput = itemRow.querySelector(`input[name*="${cation}_resultado"]`);
                
                if (lecturaInput && blancoInput && factorInput && resultadoInput) {
                    const lectura = parseFloat((lecturaInput.value || '').replace(',', '.')) || 0;
                    const blanco = parseFloat((blancoInput.value || '').replace(',', '.')) || 0;
                    const factor = parseFloat((factorInput.value || '').replace(',', '.')) || 0;
                    const key = cation.charAt(0).toUpperCase() + cation.slice(1);
                    const equivalente = equivalentes[key];
                    
                    if (pesoMuestra > 0 && lectura > 0 && equivalente > 0) {
                        // Formula: (Lectura × Volumen final × (100 + Humedad) × Factor dilución) / (Peso húmedo × 1000 × Equivalente)
                        const resultado = (lectura * volumenFinal * (100 + humedad) * factor) / (pesoMuestra * 1000 * equivalente);
                        resultadoInput.value = resultado.toFixed(4);
                    } else {
                        resultadoInput.value = '';
                    }
                }
            });
        }
    });
    
    // Trigger inicial por si hay valores precargados
    document.querySelectorAll('#items_ensayo_table input').forEach(function(inp){
        inp.dispatchEvent(new Event('input'));
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

<style>
/* Estilos consistentes con Micronutrientes - Items de Ensayo y Duplicado */
#items_ensayo_table, #duplicado-table {
    border-collapse: collapse !important;
    background: #ffffff !important;
}
#items_ensayo_table thead th, #duplicado-table thead th {
    background-color: #f8f9fa !important;
    color: #212529 !important;
    border: 1px solid #dee2e6 !important;
    vertical-align: middle;
    text-align: center;
}
#items_ensayo_table th, #items_ensayo_table td, #duplicado-table th, #duplicado-table td {
    border: 1px solid #dee2e6 !important;
}
#items_ensayo_table input.form-control, #duplicado-table input.form-control, #duplicado-table select.form-control {
    background-color: #ffffff;
    height: 38px;
    padding: 6px 10px;
}
#items_ensayo_table input[readonly], #duplicado-table input[readonly] {
    background-color: #f8f9fa !important;
    font-weight: 600;
}
</style>
@endsection 