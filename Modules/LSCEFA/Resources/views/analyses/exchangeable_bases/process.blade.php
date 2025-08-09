@extends('lscefa::layouts.technical_no_navbar')

@section('title', 'Procesar Análisis de Bases Cambiables')

@section('content')
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Proceso</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID Proceso:</strong> {{ $process->process_id }}</p>
                            <p><strong>Cliente:</strong> {{ $process->customer->nombre ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Solicitud:</strong> {{ $process->created_at->format('d/m/Y') }}</p>
                            <p><strong>Servicio:</strong> {{ $service->descripcion }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('lscefa.technical.analyses.exchangeable_bases.store') }}" method="POST" class="mb-5">
                @csrf
                <input type="hidden" name="process_id" value="{{ $process->process_id }}">
                <input type="hidden" name="service_id" value="{{ $service->services_id }}">
                
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
                                <td></td>
                                <td class="fw-bold" style="background-color: #e9ecef;">Nombre Analista:</td>
                                <td><input type="text" class="form-control" name="analista" value="{{ old('analista', Auth::user()->name) }}"></td>
                            </tr>
                        </table>
                    </div>

                            <h4 class="mb-3">
                                <i class="fas fa-list-alt mr-2" style="color: #28a745;"></i>Ítems de Ensayo
                            </h4>
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
                                            <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items[0][codigo_interno]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][peso_muestra]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="items[0][humedad]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="items[0][volumen_final]"></td>
                                            <!-- Na -->
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][na_lectura]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][na_blanco]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][na_factor]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items[0][na_resultado]" readonly></td>
                                            <!-- K -->
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][k_lectura]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][k_blanco]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][k_factor]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items[0][k_resultado]" readonly></td>
                                            <!-- Ca -->
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][ca_lectura]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][ca_blanco]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][ca_factor]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items[0][ca_resultado]" readonly></td>
                                            <!-- Mg -->
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][mg_lectura]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][mg_blanco]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[0][mg_factor]"></td>
                                            <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #e9ecef;" name="items[0][mg_resultado]" readonly></td>
                                            <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items[0][observaciones]"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="add_item_row">
                                    <i class="fas fa-plus mr-1" style="color: #28a745;"></i>Agregar muestra
                                </button>
                            </div>

                            <hr class="my-4">
                            <h4 class="mb-3">
                                <i class="fas fa-flask mr-2" style="color: #28a745;"></i>Controles Analíticos
                            </h4>
                            


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
                                        <table class="table table-bordered table-hover">
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
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1" style="color: #28a745;"></i>Guardar Análisis
                        </button>
                        <a href="{{ route('lscefa.technical.analyses.phosphorus.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1" style="color: #28a745;"></i>Cancelar
                        </a>
                    </div>
                </div>
            </form>
            <div style="height: 72px;"></div>
@endsection

<style>
    /* Estilos consistentes con Micronutrientes - Items de Ensayo */
    #items_ensayo_table {
        border-collapse: collapse !important;
        background: #ffffff !important;
    }
    #items_ensayo_table thead th {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border: 1px solid #dee2e6 !important;
        vertical-align: middle;
        text-align: center;
    }
    #items_ensayo_table th, #items_ensayo_table td {
        border: 1px solid #dee2e6 !important;
    }
    #items_ensayo_table input.form-control {
        background-color: #ffffff;
        height: 38px;
        padding: 6px 10px;
    }
    #items_ensayo_table input[readonly] {
        background-color: #f8f9fa !important;
        font-weight: 600;
    }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        let reporteRowIndex = 0;
        $('#add_reporte_row').click(function() {
            reporteRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="reporte_resultados[${reporteRowIndex}][codigo_interno]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][peso_muestra]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][pW]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][Ve]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][LBP]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][factor_dilucion]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][lectura_abs]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][fosforo_disponible_mgL]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][fosforo_disponible_mgKg]" readonly></td>
                    <td><input type="text" class="form-control" name="reporte_resultados[${reporteRowIndex}][observaciones_reporte]"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#reporte_resultados_table tbody').append(newRow);
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        let controlRowIndex = 0;
        $('#add_control_row').click(function() {
            controlRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="controles_calidad[${controlRowIndex}][identificacion]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][valor_esperado]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][valor_leido]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_error]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_recuperacion]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_dpr]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_calidad[${controlRowIndex}][aceptabilidad]" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#controles_calidad_table tbody').append(newRow);
        });

        // Función para recopilar los datos de la tabla y convertirlos a JSON
        function getTableDataAsJson(tableId) {
            let data = [];
            $(`#${tableId} tbody tr`).each(function() {
                let row = {};
                $(this).find('input, select, textarea').each(function() {
                    let name = $(this).attr('name');
                    let matches = name.match(/^(.*?)\\[(\\d+)\\]\\[(.*)\\]$/);
                    if (matches) {
                        let fieldName = matches[3];
                        row[fieldName] = $(this).val();
                    }
                });
                data.push(row);
            });
            return JSON.stringify(data);
        }

        // Calcular Fósforo disponible (mg/L) y Fósforo disponible (mg/Kg - ppm)
        function calculatePhosphorus() {
            const regresionStr = $('#regresion').val();
            const coeficientesStr = $('#coeficientes_calculados').val();
            let m = 0;
            let b = 0;

            console.log('Coeficientes String:', coeficientesStr);

            if (coeficientesStr) {
                const coefMatches = coeficientesStr.match(/m=(-?\d*\.?\d+), b=(-?\d*\.?\d+)/);
                if (coefMatches && coefMatches.length === 3) {
                    m = parseFloat(coefMatches[1]);
                    b = parseFloat(coefMatches[2]);
                    console.log('m extraído:', m, 'b extraído:', b);
                } else {
                    console.warn("Formato de 'Coeficiente calculados' inválido. Asegúrese de usar 'm=X, b=Y'.");
                    console.log('Coeficientes Match:', coefMatches);
                }
            } else {
                console.warn("'Coeficiente calculados' está vacío.");
            }

            $('#reporte_resultados_table tbody tr').each(function() {
                const lecturaAbsInput = $(this).find('input[name$="[lectura_abs]"]');
                const pesoMuestraInput = $(this).find('input[name$="[peso_muestra]"]');
                const factorDilucionInput = $(this).find('input[name$="[factor_dilucion]"]');
                const VeInput = $(this).find('input[name$="[Ve]"]');

                const lecturaAbs = parseFloat(lecturaAbsInput.val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat(pesoMuestraInput.val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat(factorDilucionInput.val().replace(',', '.')) || 0;
                const Ve = parseFloat(VeInput.val().replace(',', '.')) || 0;

                console.log('Fila de Reporte - lecturaAbs:', lecturaAbs, 'pesoMuestra:', pesoMuestra, 'factorDilucion:', factorDilucion, 'Ve:', Ve);

                // Calcular Fósforo disponible (mg/L) -> x = (y - b) / m
                let fosforoMgL = 0;
                if (m !== 0) {
                    fosforoMgL = (lecturaAbs - b) / m;
                } else if (lecturaAbs === b) {
                    fosforoMgL = 0; 
                } else {
                    fosforoMgL = NaN; 
                }
                console.log('Fósforo disponible (mg/L) calculado:', fosforoMgL);
                console.log('Intentando setear fosforo_disponible_mgL con:', fosforoMgL.toFixed(4));
                $(this).find('input[name$="[fosforo_disponible_mgL]"]').val(isNaN(fosforoMgL) ? 'Error' : fosforoMgL.toFixed(4));
                console.log('Valor seteado en fosforo_disponible_mgL:', $(this).find('input[name$="[fosforo_disponible_mgL]"]').val());

                // Calcular Fósforo disponible (mg/Kg - ppm) -> (Fósforo disponible (mg/L) * Ve (mL) * factor de dilución) / Peso muestra (g)
                let fosforoMgKg = 0;
                if (!isNaN(fosforoMgL) && pesoMuestra !== 0) {
                    fosforoMgKg = (fosforoMgL * Ve * factorDilucion) / pesoMuestra;
                } else {
                    fosforoMgKg = NaN; 
                }
                console.log('Fósforo disponible (mg/Kg - ppm) calculado:', fosforoMgKg);
                console.log('Intentando setear fosforo_disponible_mgKg con:', fosforoMgKg.toFixed(4));
                $(this).find('input[name$="[fosforo_disponible_mgKg]"]').val(isNaN(fosforoMgKg) ? 'Error' : fosforoMgKg.toFixed(4));
                console.log('Valor seteado en fosforo_disponible_mgKg:', $(this).find('input[name$="[fosforo_disponible_mgKg]"]').val());
            });
        }

        // Calcular % de error
        function calculateErrorPercentage() {
            $('#controles_calidad_table tbody tr').each(function() {
                const valorEsperadoInput = $(this).find('input[name$="[valor_esperado]"]');
                const valorLeidoInput = $(this).find('input[name$="[valor_leido]"]');

                const valorEsperado = parseFloat(valorEsperadoInput.val().replace(',', '.')) || 0;
                const valorLeido = parseFloat(valorLeidoInput.val().replace(',', '.')) || 0;
                let porcentajeError = 0;
                if (valorEsperado !== 0) {
                    porcentajeError = ((valorLeido - valorEsperado) / valorEsperado) * 100;
                }
                console.log('Intentando setear porcentaje_error con:', porcentajeError.toFixed(2));
                $(this).find('input[name$="[porcentaje_error]"]').val(porcentajeError.toFixed(2));
                console.log('Valor seteado en porcentaje_error:', $(this).find('input[name$="[porcentaje_error]"]').val());
            });
        }

        // Monitorear cambios en los campos relevantes para el cálculo
        $(document).on('input', '#reporte_resultados_table input, #regresion, #coeficientes_calculados', calculatePhosphorus);
        $(document).on('input', '#controles_calidad_table input', calculateErrorPercentage);

        // Inicializar cálculos al cargar la página si hay valores previos
        calculatePhosphorus();
        calculateErrorPercentage();

        // Eliminar scripts de controles de calidad y gráfica
        // Agregar scripts para agregar/eliminar filas de ítems de ensayo
        let itemRowIndex = 0;
        $('#add_item_row').click(function() {
            itemRowIndex++;
            let newRow = `
                <tr class="fila-muestra">
                    <td class="numero-fila text-center">${itemRowIndex + 1}</td>
                    <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items[${itemRowIndex}][codigo_interno]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][peso_muestra]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="items[${itemRowIndex}][humedad]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="items[${itemRowIndex}][volumen_final]"></td>
                    <!-- Na -->
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][na_lectura]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][na_blanco]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][na_factor]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #f8f9fa;" name="items[${itemRowIndex}][na_resultado]" readonly></td>
                    <!-- K -->
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][k_lectura]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][k_blanco]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][k_factor]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #f8f9fa;" name="items[${itemRowIndex}][k_resultado]" readonly></td>
                    <!-- Ca -->
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][ca_lectura]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][ca_blanco]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][ca_factor]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #f8f9fa;" name="items[${itemRowIndex}][ca_resultado]" readonly></td>
                    <!-- Mg -->
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][mg_lectura]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][mg_blanco]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="items[${itemRowIndex}][mg_factor]"></td>
                    <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px; background-color: #f8f9fa;" name="items[${itemRowIndex}][mg_resultado]" readonly></td>
                    <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="items[${itemRowIndex}][observaciones]"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#items_ensayo_table tbody').append(newRow);
            // Re-enumerar filas después de agregar
            reenumerarFilas();
        });
        
        // Función para re-enumerar filas
        function reenumerarFilas() {
            $('#items_ensayo_table tbody tr').each(function(index) {
                $(this).find('.numero-fila').text(index + 1);
            });
        }
        $(document).on('click', '.remove-row', function() {
            if ($('#items_ensayo_table tbody tr').length > 1) {
                $(this).closest('tr').remove();
                reenumerarFilas();
            }
        });

        // Script para agregar/eliminar filas de controles analíticos
        let controlAnaliticoRowIndex = 0;
        $('#add_control_analitico_row').click(function() {
            controlAnaliticoRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][identificacion]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][valor_esperado]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][valor_leido]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][porcentaje_error]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][aceptabilidad_error]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][porcentaje_recuperacion]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][aceptabilidad_recuperacion]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][porcentaje_dpr]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][aceptabilidad_dpr]" readonly></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#controles_analiticos_table tbody').append(newRow);
        });
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Cálculos automáticos para controles analíticos (exactamente dos filas)
        function calcularControlesAnaliticos() {
            try {
                // Para cada fila: error, recuperación y aceptabilidad
                for (let i = 0; i < 2; i++) {
                    let row = $('#controles_analiticos_table tbody tr').eq(i);
                    const valorEsperado = parseFloat((row.find('input[name$="[valor_esperado]"]').val() || '').replace(',', '.')) || 0;
                    const valorLeido = parseFloat((row.find('input[name$="[valor_leido]"]').val() || '').replace(',', '.')) || 0;
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
                const valorLeido0 = parseFloat((row0.find('input[name$="[valor_leido]"]').val() || '').replace(',', '.')) || 0;
                const valorLeido1 = parseFloat((row1.find('input[name$="[valor_leido]"]').val() || '').replace(',', '.')) || 0;
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
            } catch (error) {
                console.log('Error en calcularControlesAnaliticos:', error);
            }
        }
        $(document).on('input', '#controles_analiticos_table input', calcularControlesAnaliticos);
        calcularControlesAnaliticos();

        // Cálculo automático de % DPR y aceptabilidad para duplicados
        function calcularDPR() {
            try {
                const a = parseFloat(($('#duplicado_a').val() || '').replace(',', '.')) || 0;
                const b = parseFloat(($('#duplicado_b').val() || '').replace(',', '.')) || 0;
                let dpr = 0;
                let aceptabilidad = '';
                if ((a + b) !== 0) {
                    let promedio = (a + b) / 2;
                    dpr = Math.abs(a - b) / promedio * 100;
                    aceptabilidad = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
                }
                $('#dpr_resultado').val(dpr.toFixed(2));
                $('#dpr_aceptabilidad').val(aceptabilidad);
            } catch (error) {
                console.log('Error en calcularDPR:', error);
            }
        }
        $(document).on('input', '#duplicado_a, #duplicado_b', calcularDPR);
        calcularDPR();

        // Cálculo automático de % ERROR para curva de calibración
        function calcularErrorCurva() {
            try {
                const valorEsperado = 0.995; // Valor fijo de la curva
                const valorLeido = parseFloat(($('#curva_valor_leido').val() || '').replace(',', '.')) || 0;
                let errorPorcentaje = 0;
                
                if (valorEsperado !== 0) {
                    errorPorcentaje = Math.abs(valorLeido - valorEsperado) / valorEsperado * 100;
                }
                
                $('#curva_error_porcentaje').val(errorPorcentaje.toFixed(2));
            } catch (error) {
                console.log('Error en calcularErrorCurva:', error);
            }
        }
        $(document).on('input', '#curva_valor_leido', calcularErrorCurva);
        calcularErrorCurva();

        // Cálculo automático de aceptabilidad para blanco del método
        function calcularAceptabilidadBlanco() {
            try {
                $('.blanco-resultado, .blanco-lcm').each(function() {
                    const row = $(this).closest('tr');
                    const resultado = parseFloat(row.find('.blanco-resultado').val()) || 0;
                    const lcm = parseFloat(row.find('.blanco-lcm').val()) || 0;
                    
                    let aceptabilidad = '';
                    if (resultado <= lcm) {
                        aceptabilidad = 'Aceptable';
                    } else {
                        aceptabilidad = 'No aceptable';
                    }
                    
                    row.find('.blanco-aceptabilidad').val(aceptabilidad);
                });
            } catch (error) {
                console.log('Error en calcularAceptabilidadBlanco:', error);
            }
        }
        
        // Ejecutar al cambiar cualquier input del blanco del método
        $(document).on('input', '.blanco-resultado, .blanco-lcm', calcularAceptabilidadBlanco);
        calcularAceptabilidadBlanco();

        // Cálculo automático de %DPR para duplicado muestra
        function calcularDPRDuplicado() {
            try {
                $('input[name*="[replica_1]"], input[name*="[replica_2]"]').each(function() {
                    const row = $(this).closest('tr');
                    const replica1 = parseFloat(row.find('input[name*="[replica_1]"]').val()) || 0;
                    const replica2 = parseFloat(row.find('input[name*="[replica_2]"]').val()) || 0;
                    
                    let dpr = 0;
                    let aceptabilidad = '';
                    
                    if (replica1 > 0 || replica2 > 0) {
                        const promedio = (replica1 + replica2) / 2;
                        if (promedio > 0) {
                            // Fórmula: %DPR = ( |Réplicas 1 - Réplicas 2| / Promedio de Réplicas ) × 100
                            dpr = Math.abs(replica1 - replica2) / promedio * 100;
                            aceptabilidad = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
                        }
                    }
                    
                    // Siempre mostrar el cálculo, incluso si es 0.00
                    row.find('input[name*="[dpr_1]"]').val(dpr.toFixed(2));
                    row.find('input[name*="[dpr_2]"]').val(dpr.toFixed(2));
                    row.find('input[name*="[aceptabilidad]"]').val(aceptabilidad);
                });
            } catch (error) {
                console.log('Error en calcularDPRDuplicado:', error);
            }
        }
        
        // Ejecutar al cambiar cualquier input de réplicas
        $(document).on('input', 'input[name*="[replica_1]"], input[name*="[replica_2]"]', calcularDPRDuplicado);
        calcularDPRDuplicado();

        // Cálculo automático de % Recuperación para controles de calidad
        function calcularRecuperacionCalidad() {
            try {
                $('input[name*="[valor_esperado]"], input[name*="[valor_leido]"]').each(function() {
                    const row = $(this).closest('tr');
                    const valorEsperado = parseFloat(row.find('input[name*="[valor_esperado]"]').val()) || 0;
                    const valorLeido = parseFloat(row.find('input[name*="[valor_leido]"]').val()) || 0;
                    
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
                    
                    row.find('input[name*="[porcentaje_recuperacion]"]').val(porcentajeRecuperacion > 0 ? porcentajeRecuperacion.toFixed(1) : '');
                    row.find('input[name*="[aceptabilidad]"]').val(aceptabilidad);
                });
            } catch (error) {
                console.log('Error en calcularRecuperacionCalidad:', error);
            }
        }
        
        // Ejecutar al cambiar cualquier input de valor esperado o valor leído
        $(document).on('input', 'input[name*="[valor_esperado]"], input[name*="[valor_leido]"]', calcularRecuperacionCalidad);
        calcularRecuperacionCalidad();

        // Equivalentes de conversión para cada catión según las fórmulas específicas
        const equivalentes = {
            'Na': 23,
            'K': 39,
            'Ca': 20,
            'Mg': 12.16
        };

        function calcularBasesCambiables() {
            console.log('Calculando bases cambiables...');
            $('#items_ensayo_table tbody tr').each(function(index) {
                const row = $(this);
                
                // Obtener valores de la fila
                const pesoHumedo = parseFloat(row.find(`input[name="items[${index}][peso_muestra]"]`).val()) || 0;
                const humedad = parseFloat(row.find(`input[name="items[${index}][humedad]"]`).val()) || 0;
                const volumenFinal = parseFloat(row.find(`input[name="items[${index}][volumen_final]"]`).val()) || 0;
                
                console.log('Fila', index, 'Peso húmedo:', pesoHumedo, 'Humedad:', humedad, 'Volumen final:', volumenFinal);
                
                // Calcular peso seco
                const pesoSeco = pesoHumedo * (1 - humedad / 100);
                console.log('Peso seco calculado:', pesoSeco);
                
                // Calcular para cada catión (Na, K, Ca, Mg)
                ['na', 'k', 'ca', 'mg'].forEach(function(cation) {
                    const lectura = parseFloat(row.find(`input[name="items[${index}][${cation}_lectura]"]`).val()) || 0;
                    const blanco = parseFloat(row.find(`input[name="items[${index}][${cation}_blanco]"]`).val()) || 0;
                    const factorDilucion = parseFloat(row.find(`input[name="items[${index}][${cation}_factor]"]`).val()) || 0;
                    
                    console.log(`${cation.toUpperCase()}:`, 'Lectura:', lectura, 'Blanco:', blanco, 'Factor:', factorDilucion);
                    
                    // Obtener el equivalente según el catión
                    const cationUpper = cation.toUpperCase();
                    const equivalente = equivalentes[cationUpper];
                    
                    let resultado = 0;
                    if (pesoHumedo > 0 && equivalente > 0 && lectura > 0) {
                        // Fórmula Excel Na: =(E10*D10*(100+C10)*G10)/(B10*1000*23)
                        // Donde: E=lectura, D=volumen_final, C=humedad, G=factor_dilucion, B=peso_muestra
                        resultado = (lectura * volumenFinal * (100 + humedad) * factorDilucion) / (pesoHumedo * 1000 * equivalente);
                        console.log(`${cation.toUpperCase()} resultado:`, resultado);
                    }
                    
                    // Actualizar el campo de resultado
                    const resultadoField = row.find(`input[name="items[${index}][${cation}_resultado]"]`);
                    resultadoField.val(resultado > 0 ? resultado.toFixed(4) : '');
                    console.log(`Campo ${cation}_resultado actualizado:`, resultadoField.val());
                });
            });
        }

        // Ejecutar al cambiar cualquier input relevante
        $(document).on('input', '#items_ensayo_table input', calcularBasesCambiables);
            
        // Eliminado botón de prueba de cálculo
        
        // Ejecutar al cargar la página
        calcularBasesCambiables();
        
        // Script adicional para asegurar que funcione
        document.addEventListener('input', function () {
            document.querySelectorAll('#items_ensayo_table tbody tr').forEach((row, index) => {
                // Obtener valores básicos
                const pesoHumedo = parseFloat(row.querySelector(`input[name="items[${index}][peso_muestra]"]`)?.value) || 0;
                const humedad = parseFloat(row.querySelector(`input[name="items[${index}][humedad]"]`)?.value) || 0;
                const volumenFinal = parseFloat(row.querySelector(`input[name="items[${index}][volumen_final]"]`)?.value) || 0;
                
                // Calcular peso seco
                const pesoSeco = pesoHumedo * (1 - humedad / 100);
                
                // Calcular para Na
                const naLectura = parseFloat(row.querySelector(`input[name="items[${index}][na_lectura]"]`)?.value) || 0;
                const naBlanco = parseFloat(row.querySelector(`input[name="items[${index}][na_blanco]"]`)?.value) || 0;
                const naFactor = parseFloat(row.querySelector(`input[name="items[${index}][na_factor]"]`)?.value) || 0;
                
                if (pesoHumedo > 0 && naLectura > 0) {
                    // Fórmula Excel Na: =(E10*D10*(100+C10)*G10)/(B10*1000*23)
                    const naResultado = (naLectura * volumenFinal * (100 + humedad) * naFactor) / (pesoHumedo * 1000 * 23);
                    const naField = row.querySelector(`input[name="items[${index}][na_resultado]"]`);
                    if (naField) naField.value = naResultado.toFixed(4);
                }
                
                // Calcular para K
                const kLectura = parseFloat(row.querySelector(`input[name="items[${index}][k_lectura]"]`)?.value) || 0;
                const kBlanco = parseFloat(row.querySelector(`input[name="items[${index}][k_blanco]"]`)?.value) || 0;
                const kFactor = parseFloat(row.querySelector(`input[name="items[${index}][k_factor]"]`)?.value) || 0;
                
                if (pesoHumedo > 0 && kLectura > 0) {
                    // Fórmula Excel K: =(E10*D10*(100+C10)*G10)/(B10*1000*39)
                    const kResultado = (kLectura * volumenFinal * (100 + humedad) * kFactor) / (pesoHumedo * 1000 * 39);
                    const kField = row.querySelector(`input[name="items[${index}][k_resultado]"]`);
                    if (kField) kField.value = kResultado.toFixed(4);
                }
                
                // Calcular para Ca
                const caLectura = parseFloat(row.querySelector(`input[name="items[${index}][ca_lectura]"]`)?.value) || 0;
                const caBlanco = parseFloat(row.querySelector(`input[name="items[${index}][ca_blanco]"]`)?.value) || 0;
                const caFactor = parseFloat(row.querySelector(`input[name="items[${index}][ca_factor]"]`)?.value) || 0;
                
                if (pesoHumedo > 0 && caLectura > 0) {
                    // Fórmula Ca: (Lectura × Volumen final × (100 + Humedad) × Factor dilución) / (Peso húmedo × 1000 × 20)
                    const caResultado = (caLectura * volumenFinal * (100 + humedad) * caFactor) / (pesoHumedo * 1000 * 20);
                    const caField = row.querySelector(`input[name="items[${index}][ca_resultado]"]`);
                    if (caField) caField.value = caResultado.toFixed(4);
                }
                
                // Calcular para Mg
                const mgLectura = parseFloat(row.querySelector(`input[name="items[${index}][mg_lectura]"]`)?.value) || 0;
                const mgBlanco = parseFloat(row.querySelector(`input[name="items[${index}][mg_blanco]"]`)?.value) || 0;
                const mgFactor = parseFloat(row.querySelector(`input[name="items[${index}][mg_factor]"]`)?.value) || 0;
                
                if (pesoHumedo > 0 && mgLectura > 0) {
                    // Fórmula Mg: (Lectura × Volumen final × (100 + Humedad) × Factor dilución) / (Peso húmedo × 1000 × 12.16)
                    const mgResultado = (mgLectura * volumenFinal * (100 + humedad) * mgFactor) / (pesoHumedo * 1000 * 12.16);
                    const mgField = row.querySelector(`input[name="items[${index}][mg_resultado]"]`);
                    if (mgField) mgField.value = mgResultado.toFixed(4);
                }
            });
        });
    });
</script>
@endpush 
<style>
    /* Ocultar copyright/footers en esta vista */
    .main-footer, footer { display: none !important; }
    /* Ocultar el header de plantilla con el título */
    .content-header { display: none !important; }
    /* Ocultar la barra de navegación superior (usuario/cerrar sesión) solo en esta vista */
    .main-header.navbar { display: none !important; }
    /* Dar espacio inferior para evitar que el debugbar tape los botones */
    section.content { padding-bottom: 120px !important; }
</style>