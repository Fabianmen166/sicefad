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

                        <!-- Procesos Seleccionados -->
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
                                                            @if(str_contains(strtolower($detail->service->descripcion), 'micronutrientes') || 
                                                                str_contains(strtolower($detail->service->descripcion), 'micronutrients') ||
                                                                str_contains(strtolower($detail->service->descripcion), 'zinc') ||
                                                                str_contains(strtolower($detail->service->descripcion), 'hierro') ||
                                                                str_contains(strtolower($detail->service->descripcion), 'manganeso') ||
                                                                str_contains(strtolower($detail->service->descripcion), 'cobre'))
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
                                                <div class="table-responsive" style="overflow-x: auto; min-width: 100%;">
                                                    <table class="table table-bordered table-hover" style="min-width: 1800px;">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th colspan="4" class="text-center bg-light">Información de la muestra</th>
                                                                <th colspan="3" class="text-center bg-light">Mn</th>
                                                                <th colspan="3" class="text-center bg-light">Fe</th>
                                                                <th colspan="3" class="text-center bg-light">Zn</th>
                                                                <th colspan="3" class="text-center bg-light">Cu</th>
                                                                <th rowspan="2" class="text-center bg-light">OBSERVACIONES</th>
                                                            </tr>
                                                            <tr>
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
                                                                <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[{{ $process->process_id }}][0][codigo_interno]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][peso_muestra]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="items_ensayo[{{ $process->process_id }}][0][humedad]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="items_ensayo[{{ $process->process_id }}][0][volumen_final]"></td>
                                                                <!-- Mn -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][mn_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][mn_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][mn_resultado]" readonly></td>
                                                                <!-- Fe -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][fe_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][fe_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][fe_resultado]" readonly></td>
                                                                <!-- Zn -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][zn_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][zn_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][zn_resultado]" readonly></td>
                                                                <!-- Cu -->
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][cu_lectura]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items_ensayo[{{ $process->process_id }}][0][cu_factor]"></td>
                                                                <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items_ensayo[{{ $process->process_id }}][0][cu_resultado]" readonly></td>
                                                                <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items_ensayo[{{ $process->process_id }}][0][observaciones]"></td>
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


});
</script>
@endsection
