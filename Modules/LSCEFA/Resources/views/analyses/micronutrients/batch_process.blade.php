@extends('lscefa::layouts.technical')

@section('title', 'Procesamiento por Lotes - Micronutrientes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-flask"></i> Procesamiento por Lotes - Análisis de Micronutrientes
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('lscefa.technical.analyses.micronutrients.batch_store') }}" method="POST" id="batchMicronutrientsForm">
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
                                    <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-content" type="button" role="tab" aria-controls="items-content" aria-selected="true">
                                        <i class="fas fa-flask"></i> Items de Ensayo
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="controls-tab" data-bs-toggle="tab" data-bs-target="#controls-content" type="button" role="tab" aria-controls="controls-content" aria-selected="false">
                                        <i class="fas fa-check-circle"></i> Controles Analíticos
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="analysisTabContent">
                            <!-- Items de Ensayo Tab -->
                            <div class="tab-pane fade show active" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                                <!-- Procesos Seleccionados -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-list"></i> Procesos a Procesar</h4>
                                    </div>
                                    <div class="card-body">
                                @foreach($pendingProcesses as $process)
                                <div class="process-item mb-4">
                                    <div class="card">
                                        <div class="card-header py-2">
                                            <span class="process-title text-monospace">Proceso: {{ $process->process_id }}</span>
                                            <input type="hidden" name="process_ids[]" value="{{ $process->process_id }}">
                                        </div>
                                        <div class="card-body">
                                            <!-- Info superior simplificada removida por solicitud -->

                                            <!-- Items por cada servicio de micronutrientes pendiente en este proceso -->
                                            @php
                                                $micronutrientDetails = $process->serviceProcessDetails->filter(function($detail){
                                                    $d = strtolower($detail->service->descripcion ?? '');
                                                    return (
                                                        str_contains($d,'micronutrientes') ||
                                                        str_contains($d,'micronutrients') ||
                                                        str_contains($d,'zinc') ||
                                                        str_contains($d,'hierro') ||
                                                        str_contains($d,'manganeso') ||
                                                        str_contains($d,'cobre')
                                                    ) && ($detail->status === 'pending');
                                                });
                                            @endphp

                                            @foreach($micronutrientDetails as $detail)
                                                <div class="card items-card mb-4 border border-secondary-subtle">
                                                    <div class="card-header py-2 bg-white">
                                                        <h6 class="mb-0">Items de Ensayo — {{ $detail->service->descripcion }}</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <input type="hidden" name="processes[{{ $process->process_id }}][{{ $detail->service_id }}][process_id]" value="{{ $process->process_id }}">
                                                        <input type="hidden" name="processes[{{ $process->process_id }}][{{ $detail->service_id }}][service_id]" value="{{ $detail->service_id }}">
                                                        <div class="table-responsive items-table-responsive">
                                                            <table class="table table-sm table-bordered table-hover mb-0" style="min-width: 1800px;">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th colspan="5" class="text-center bg-light">Información de la muestra</th>
                                                                    <th colspan="3" class="text-center bg-light">Mn</th>
                                                                    <th colspan="3" class="text-center bg-light">Fe</th>
                                                                    <th colspan="3" class="text-center bg-light">Zn</th>
                                                                    <th colspan="3" class="text-center bg-light">Cu</th>
                                                                    <th rowspan="2" class="text-center bg-light">OBSERVACIONES</th>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-center">Proceso</th>
                                                                    <th class="text-center">Código interno</th>
                                                                    <th class="text-center">Peso (g) muestra</th>
                                                                    <th class="text-center">Humedad (pW)</th>
                                                                    <th class="text-center">Vol final (mL)</th>
                                                                    <th class="text-center">Lectura (mg/L)</th>
                                                                    <th class="text-center">Factor dilución</th>
                                                                    <th class="text-center">Resultados [mg/kg]</th>
                                                                    <th class="text-center">Lectura (mg/L)</th>
                                                                    <th class="text-center">Factor dilución</th>
                                                                    <th class="text-center">Resultados [mg/kg]</th>
                                                                    <th class="text-center">Lectura (mg/L)</th>
                                                                    <th class="text-center">Factor dilución</th>
                                                                    <th class="text-center">Resultados [mg/kg]</th>
                                                                    <th class="text-center">Lectura (mg/L)</th>
                                                                    <th class="text-center">Factor dilución</th>
                                                                    <th class="text-center">Resultados [mg/kg]</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr class="fila-muestra">
                                                                    <td><input type="text" class="form-control form-control-sm text-monospace" style="min-width:100px; font-size: 11px;" value="{{ $process->process_id }}" readonly></td>
                                                                    <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][codigo_interno]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][peso_muestra]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][humedad]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][volumen_final]"></td>
                                                                    <!-- Mn -->
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][mn_lectura]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][mn_factor]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][mn_resultado]" readonly></td>
                                                                    <!-- Fe -->
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][fe_lectura]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][fe_factor]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][fe_resultado]" readonly></td>
                                                                    <!-- Zn -->
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][zn_lectura]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][zn_factor]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][zn_resultado]" readonly></td>
                                                                    <!-- Cu -->
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][cu_lectura]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][cu_factor]"></td>
                                                                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][cu_resultado]" readonly></td>
                                                                    <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[{{ $process->process_id }}][{{ $detail->service_id }}][0][observaciones]"></td>
                                                                </tr>
                                                            </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                            </div>
                            <!-- End of Items de Ensayo Tab -->

                            <!-- Controles Analíticos Tab -->
                            <div class="tab-pane fade" id="controls-content" role="tabpanel" aria-labelledby="controls-tab">
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
                                                        <td><input type="text" class="form-control" name="blanco_metodo[0][identificacion]" value="Blanco Zn"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[0][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[0][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[0][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[1][identificacion]" value="Blanco Fe"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[1][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[1][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[1][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[2][identificacion]" value="Blanco Mn"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-resultado" name="blanco_metodo[2][resultado]"></td>
                                                        <td><input type="number" step="any" class="form-control blanco-lcm" name="blanco_metodo[2][lcm]"></td>
                                                        <td><input type="text" class="form-control blanco-aceptabilidad" name="blanco_metodo[2][aceptabilidad]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="blanco_metodo[3][identificacion]" value="Blanco Cu"></td>
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
                                                         <th class="text-center">Aceptable/no aceptable</th>
                                                     </tr>
                                                 </thead>
                                                 <tbody>
                                                     <tr class="duplicado-row">
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[0][identificacion_muestra]" value="Mn" readonly></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[0][replica_1]"></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[0][replica_2]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="duplicado_muestra[0][dpr]" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[0][elemento]" value="Mn" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[0][aceptabilidad]" readonly></td>
                                                     </tr>
                                                     <tr class="duplicado-row">
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[1][identificacion_muestra]" value="Fe" readonly></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[1][replica_1]"></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[1][replica_2]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="duplicado_muestra[1][dpr]" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[1][elemento]" value="Fe" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[1][aceptabilidad]" readonly></td>
                                                     </tr>
                                                     <tr class="duplicado-row">
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[2][identificacion_muestra]" value="Zn" readonly></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[2][replica_1]"></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[2][replica_2]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="duplicado_muestra[2][dpr]" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[2][elemento]" value="Zn" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[2][aceptabilidad]" readonly></td>
                                                     </tr>
                                                     <tr class="duplicado-row">
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[3][identificacion_muestra]" value="Cu" readonly></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[3][replica_1]"></td>
                                                         <td><input type="number" step="any" class="form-control duplicado-replica" name="duplicado_muestra[3][replica_2]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="duplicado_muestra[3][dpr]" readonly></td>
                                                         <td><input type="text" class="form-control" name="duplicado_muestra[3][elemento]" value="Cu" readonly></td>
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
                                                         <th class="text-center">Controles de calidad (Exactitud)</th>
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
                                                         <td><input type="text" class="form-control" name="controles_calidad[0][controles_calidad]" value="Material de Referencia o MRC Mn"></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[0][identificacion]" value="Mn"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[0][valor_esperado]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[0][valor_leido]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[0][porcentaje_recuperacion]" readonly></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[0][aceptabilidad]" readonly></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[0][observaciones]"></td>
                                                     </tr>
                                                     <tr>
                                                         <td><input type="text" class="form-control" name="controles_calidad[1][controles_calidad]" value="Material de Referencia o MRC Fe"></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[1][identificacion]" value="Fe"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[1][valor_esperado]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[1][valor_leido]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[1][porcentaje_recuperacion]" readonly></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[1][aceptabilidad]" readonly></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[1][observaciones]"></td>
                                                     </tr>
                                                     <tr>
                                                         <td><input type="text" class="form-control" name="controles_calidad[2][controles_calidad]" value="Material de Referencia o MRC Zn"></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[2][identificacion]" value="Zn"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[2][valor_esperado]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[2][valor_leido]"></td>
                                                         <td><input type="number" step="any" class="form-control" name="controles_calidad[2][porcentaje_recuperacion]" readonly></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[2][aceptabilidad]" readonly></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[2][observaciones]"></td>
                                                     </tr>
                                                     <tr>
                                                         <td><input type="text" class="form-control" name="controles_calidad[3][controles_calidad]" value="Material de Referencia o MRC Cu"></td>
                                                         <td><input type="text" class="form-control" name="controles_calidad[3][identificacion]" value="Cu"></td>
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
                                                        <td><input type="text" class="form-control" name="control_estandar[0][estandar]" value="Zn"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[0][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[0][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[0][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[0][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[0][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[1][estandar]" value="Fe"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[1][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[1][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[1][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[1][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[1][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[2][estandar]" value="Mn"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[2][concentracion]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[2][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="control_estandar[2][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[2][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="control_estandar[2][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="control_estandar[3][estandar]" value="Cu"></td>
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
                                                        <td><input type="text" class="form-control" name="curva_calibracion[0][elemento]" value="Zn"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[0][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[0][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[0][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[0][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[1][elemento]" value="Fe"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[1][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[1][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[1][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[1][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[2][elemento]" value="Mn"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[2][r2_obtenido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="curva_calibracion[2][r2_esperado]" value="0.995" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[2][aceptabilidad]" readonly></td>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[2][observaciones]"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="curva_calibracion[3][elemento]" value="Cu"></td>
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
                        </div>
                        <!-- End of Tab Content -->

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Análisis por Lotes
                                </button>
                                <a href="{{ route('lscefa.technical.analyses.micronutrients.index') }}" class="btn btn-secondary">
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
// Cálculos automáticos para micronutrientes
    document.addEventListener('DOMContentLoaded', function() {
        // Cálculos para items de micronutrientes
        document.addEventListener('input', function(e) {
            if (e.target.name && (e.target.name.includes('zn_lectura') || e.target.name.includes('zn_factor') ||
                              e.target.name.includes('fe_lectura') || e.target.name.includes('fe_factor') ||
                              e.target.name.includes('mn_lectura') || e.target.name.includes('mn_factor') ||
                              e.target.name.includes('cu_lectura') || e.target.name.includes('cu_factor') ||
                              e.target.name.includes('peso_muestra') || e.target.name.includes('humedad') || e.target.name.includes('volumen_final'))) {
            
            const itemRow = e.target.closest('tr');
            
            // Obtener valores comunes
            const pesoMuestra = parseFloat(itemRow.querySelector('input[name*="peso_muestra"]').value) || 0;
            const humedad = parseFloat(itemRow.querySelector('input[name*="humedad"]').value) || 0;
            const volumenFinal = parseFloat(itemRow.querySelector('input[name*="volumen_final"]').value) || 0;
            
            // Calcular cada micronutriente con la misma fórmula
            const calculos = {
                'mn': { formula: '(Lectura × Factor dilución × Vol final) / Peso muestra × (100 + Humedad) / 100' },
                'fe': { formula: '(Lectura × Factor dilución × Vol final) / Peso muestra × (100 + Humedad) / 100' },
                'zn': { formula: '(Lectura × Factor dilución × Vol final) / Peso muestra × (100 + Humedad) / 100' },
                'cu': { formula: '(Lectura × Factor dilución × Vol final) / Peso muestra × (100 + Humedad) / 100' }
            };

            ['mn', 'fe', 'zn', 'cu'].forEach(function(nutriente) {
                const lecturaInput = itemRow.querySelector(`input[name*="${nutriente}_lectura"]`);
                const factorInput = itemRow.querySelector(`input[name*="${nutriente}_factor"]`);
                const resultadoInput = itemRow.querySelector(`input[name*="${nutriente}_resultado"]`);
                
                if (lecturaInput && factorInput && resultadoInput) {
                    const lectura = parseFloat(lecturaInput.value) || 0;
                    const factor = parseFloat(factorInput.value) || 0;
                    
                    if (pesoMuestra > 0 && lectura > 0) {
                        // Fórmula: (Lectura × Factor dilución × Vol final) / Peso muestra × (100 + Humedad) / 100
                        const resultado = (lectura * factor * volumenFinal) / pesoMuestra * (100 + humedad) / 100;
                        resultadoInput.value = resultado.toFixed(2);
                    } else {
                        resultadoInput.value = '';
                    }
                }
            });
        }
    });

    // Cálculo automático de aceptabilidad para blanco del método
    function calcularAceptabilidadBlanco() {
        try {
            document.querySelectorAll('.blanco-resultado, .blanco-lcm').forEach(function(input) {
                const row = input.closest('tr');
                const resultado = parseFloat(row.querySelector('.blanco-resultado').value) || 0;
                const lcm = parseFloat(row.querySelector('.blanco-lcm').value) || 0;
                
                let aceptabilidad = '';
                if (resultado >= lcm) {
                    aceptabilidad = 'Aceptable';
                } else {
                    aceptabilidad = 'No aceptable';
                }
                
                row.querySelector('.blanco-aceptabilidad').value = aceptabilidad;
            });
        } catch (error) {
            console.log('Error en calcularAceptabilidadBlanco:', error);
        }
    }
    
    // Ejecutar al cambiar cualquier input del blanco del método
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('blanco-resultado') || e.target.classList.contains('blanco-lcm')) {
            calcularAceptabilidadBlanco();
        }
    });
    calcularAceptabilidadBlanco();

    // Cálculo automático de %DPR para duplicado muestra
        function calcularDPRDuplicado() {
         try {
             document.querySelectorAll('.duplicado-replica').forEach(function(input) {
                 const row = input.closest('tr');
                 const replica1 = parseFloat(row.querySelector('input[name*="[replica_1]"]').value) || 0;
                 const replica2 = parseFloat(row.querySelector('input[name*="[replica_2]"]').value) || 0;
                 
                 let dpr = 0;
                 let aceptabilidad = '';
                 
                 if (replica1 > 0 || replica2 > 0) {
                     const promedio = (replica1 + replica2) / 2;
                     if (promedio > 0) {
                         // Fórmula: %DPR = ( |Réplicas 1 - Réplicas 2| / Promedio de Réplicas ) × 100
                         dpr = Math.abs(replica1 - replica2) / promedio * 100;
                         aceptabilidad = (dpr < 25) ? 'Aceptable' : 'No aceptable';
                     }
                 }
                 
                 // Siempre mostrar el cálculo, incluso si es 0.00
                 row.querySelector('input[name*="[dpr]"]').value = dpr.toFixed(2);
                 row.querySelector('input[name*="[aceptabilidad]"]').value = aceptabilidad;
             });
         } catch (error) {
             console.log('Error en calcularDPRDuplicado:', error);
         }
     }
    
    // Ejecutar al cambiar cualquier input de réplicas
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('duplicado-replica')) {
            calcularDPRDuplicado();
        }
    });
    calcularDPRDuplicado();

    // Cálculo automático de % Recuperación para controles de calidad
    function calcularRecuperacionCalidad() {
        try {
            document.querySelectorAll('input[name*="[valor_esperado]"], input[name*="[valor_leido]"]').forEach(function(input) {
                const row = input.closest('tr');
                const valorEsperado = parseFloat(row.querySelector('input[name*="[valor_esperado]"]').value) || 0;
                const valorLeido = parseFloat(row.querySelector('input[name*="[valor_leido]"]').value) || 0;
                
                let porcentajeRecuperacion = 0;
                let aceptabilidad = '';
                
                if (valorEsperado > 0) {
                    // Fórmula: % Recuperación = (Valor leído / Valor esperado) × 100
                    porcentajeRecuperacion = (valorLeido / valorEsperado) * 100;
                    
                    // Criterio de aceptabilidad: 80% ≤ % Recuperación ≤ 120%
                    if (porcentajeRecuperacion >= 80 && porcentajeRecuperacion <= 120) {
                        aceptabilidad = 'Aceptable';
                    } else {
                        aceptabilidad = 'No Aceptable';
                    }
                }
                
                row.querySelector('input[name*="[porcentaje_recuperacion]"]').value = porcentajeRecuperacion > 0 ? porcentajeRecuperacion.toFixed(1) : '';
                row.querySelector('input[name*="[aceptabilidad]"]').value = aceptabilidad;
            });
        } catch (error) {
            console.log('Error en calcularRecuperacionCalidad:', error);
        }
    }
    
    // Ejecutar al cambiar cualquier input de valor esperado o valor leído
    document.addEventListener('input', function(e) {
        if (e.target.name && (e.target.name.includes('[valor_esperado]') || e.target.name.includes('[valor_leido]'))) {
            calcularRecuperacionCalidad();
        }
    });
    calcularRecuperacionCalidad();

    // Cálculo automático de % ERROR para control de estándar
    function calcularErrorEstandar() {
        try {
            document.querySelectorAll('input[name*="[concentracion]"], input[name*="[valor_leido]"]').forEach(function(input) {
                const row = input.closest('tr');
                const concentracion = parseFloat(row.querySelector('input[name*="[concentracion]"]').value) || 0;
                const valorLeido = parseFloat(row.querySelector('input[name*="[valor_leido]"]').value) || 0;
                
                let porcentajeError = 0;
                let aceptabilidad = '';
                
                if (concentracion > 0) {
                    // Fórmula: % Error = |(Valor leído - Concentración) / Concentración| × 100
                    porcentajeError = Math.abs((valorLeido - concentracion) / concentracion) * 100;
                    
                    // Criterio de aceptabilidad: % Error ≤ 10%
                    if (porcentajeError <= 10) {
                        aceptabilidad = 'Aceptable';
                    } else {
                        aceptabilidad = 'No Aceptable';
                    }
                }
                
                row.querySelector('input[name*="[porcentaje_error]"]').value = porcentajeError > 0 ? porcentajeError.toFixed(2) : '';
                row.querySelector('input[name*="[aceptabilidad]"]').value = aceptabilidad;
            });
        } catch (error) {
            console.log('Error en calcularErrorEstandar:', error);
        }
    }
    
    // Ejecutar al cambiar cualquier input de concentración o valor leído
    document.addEventListener('input', function(e) {
        if (e.target.name && (e.target.name.includes('[concentracion]') || e.target.name.includes('[valor_leido]'))) {
            calcularErrorEstandar();
        }
    });
    calcularErrorEstandar();

    // Cálculo automático de aceptabilidad para curva de calibración
    function calcularAceptabilidadCurva() {
        try {
            document.querySelectorAll('input[name*="[r2_obtenido]"]').forEach(function(input) {
                const row = input.closest('tr');
                const r2Obtenido = parseFloat(input.value) || 0;
                const r2Esperado = parseFloat(row.querySelector('input[name*="[r2_esperado]"]').value) || 0.995;
                
                let aceptabilidad = '';
                if (r2Obtenido >= r2Esperado) {
                    aceptabilidad = 'Aceptable';
                } else if (r2Obtenido > 0) {
                    aceptabilidad = 'No aceptable';
                }
                
                row.querySelector('input[name*="[aceptabilidad]"]').value = aceptabilidad;
            });
        } catch (error) {
            console.log('Error en calcularAceptabilidadCurva:', error);
        }
    }
    
    // Ejecutar al cambiar cualquier input de R² obtenido
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('[r2_obtenido]')) {
            calcularAceptabilidadCurva();
        }
    });
    calcularAceptabilidadCurva();

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
/* Evitar superposición entre tarjetas de items */
.items-card { margin-top: 12px; position: relative; z-index: 1; }
.items-table-responsive { overflow-x: auto; min-width: 100%; position: relative; z-index: 0; }

/* Separadores visuales */
.items-card .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
.items-card .card-body { padding: 0.75rem; }
.items-card + .items-card { margin-top: 16px; }

/* Mantener barras horizontales independientes por tabla */
.items-table-responsive::-webkit-scrollbar { height: 8px; }
.items-table-responsive::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 4px; }
/* Ajuste para input de proceso */
.text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
/* Título de proceso pequeño */
.process-title { font-size: 0.95rem; font-weight: 600; }
</style>
@endsection
