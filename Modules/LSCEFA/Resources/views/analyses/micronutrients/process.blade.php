@extends('lscefa::layouts.technical')

@section('title', '')

@section('content')
<div class="container-fluid" style="margin-left: 250px; margin-top: 20px;">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-flask"></i> Procesamiento - Análisis de Micronutrientes
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('lscefa.technical.analyses.micronutrients.store') }}" method="POST" id="micronutrientsForm">
                        @csrf
                        <input type="hidden" name="process_id" value="{{ $process->process_id }}">
                        <input type="hidden" name="service_id" value="{{ $service->services_id }}">
                        
                        <!-- Información del Proceso -->
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

                        <!-- Datos del Análisis -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Datos del Análisis</h3>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless align-middle" id="datos_analisis_excel" style="background: #f8f9fa; border-radius: 8px;">
                            <tr>
                                        <td class="fw-bold" style="width: 15%; background-color: #e9ecef;">Consecutivo No:</td>
                                        <td style="width: 15%"><input type="text" class="form-control" name="consecutivo_no" value="{{ old('consecutivo_no', '1') }}"></td>
                                        <td class="fw-bold" style="width: 15%; background-color: #e9ecef;">Método:</td>
                                        <td style="width: 15%"><input type="text" class="form-control" name="metodo" value="{{ old('metodo') }}"></td>
                                        <td class="fw-bold" style="width: 15%; background-color: #e9ecef;">Intervalo:</td>
                                        <td style="width: 15%"><input type="text" class="form-control" name="intervalo_metodo" value="{{ old('intervalo_metodo') }}"></td>
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

                        <!-- Items de Ensayo -->
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-flask"></i> Items de Ensayo</h4>
                            </div>
                    <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="items_ensayo_table">
                                <thead class="thead-light">
                                    <tr>
                                                <th colspan="4" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">Información de la muestra</th>
                                                <th colspan="3" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">Mn</th>
                                                <th colspan="3" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">Fe</th>
                                                <th colspan="3" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">Zn</th>
                                                <th colspan="3" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">Cu</th>
                                                <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">OBSERVACIONES</th>
                                                <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">ACCIÓN</th>
                                    </tr>
                                    <tr>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 120px;">Código interno</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 120px;">Peso (g) muestra</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 120px;">Humedad (pW)</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 120px;">Vol final (mL)</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Lectura (mg/L)</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Factor dilución</th>
                                                <th class="text-center" style="background-color: #d1d3d4; border: 1px solid #dee2e6; min-width: 120px;">Resultados [mg/kg]</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Lectura (mg/L)</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Factor dilución</th>
                                                <th class="text-center" style="background-color: #d1d3d4; border: 1px solid #dee2e6; min-width: 120px;">Resultados [mg/kg]</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Lectura (mg/L)</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Factor dilución</th>
                                                <th class="text-center" style="background-color: #d1d3d4; border: 1px solid #dee2e6; min-width: 120px;">Resultados [mg/kg]</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Lectura (mg/L)</th>
                                                <th class="text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; min-width: 100px;">Factor dilución</th>
                                                <th class="text-center" style="background-color: #d1d3d4; border: 1px solid #dee2e6; min-width: 120px;">Resultados [mg/kg]</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="fila-muestra">
                                        <td class="numero-fila text-center">1</td>
                                                <td><input type="text" class="form-control" name="items[0][codigo_interno]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][peso_muestra]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][humedad]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][volumen_final]"></td>
                                        <!-- Mn -->
                                                <td><input type="number" step="any" class="form-control" name="items[0][mn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][mn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[0][mn_resultado]" readonly></td>
                                        <!-- Fe -->
                                                <td><input type="number" step="any" class="form-control" name="items[0][fe_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][fe_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[0][fe_resultado]" readonly></td>
                                        <!-- Zn -->
                                                <td><input type="number" step="any" class="form-control" name="items[0][zn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][zn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[0][zn_resultado]" readonly></td>
                                        <!-- Cu -->
                                                <td><input type="number" step="any" class="form-control" name="items[0][cu_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[0][cu_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[0][cu_resultado]" readonly></td>
                                                <td><input type="text" class="form-control" name="items[0][observaciones]"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="fila-muestra">
                                                <td class="numero-fila text-center">2</td>
                                                <td><input type="text" class="form-control" name="items[1][codigo_interno]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][peso_muestra]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][humedad]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][volumen_final]"></td>
                                                <!-- Mn -->
                                                <td><input type="number" step="any" class="form-control" name="items[1][mn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][mn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[1][mn_resultado]" readonly></td>
                                                <!-- Fe -->
                                                <td><input type="number" step="any" class="form-control" name="items[1][fe_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][fe_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[1][fe_resultado]" readonly></td>
                                                <!-- Zn -->
                                                <td><input type="number" step="any" class="form-control" name="items[1][zn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][zn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[1][zn_resultado]" readonly></td>
                                                <!-- Cu -->
                                                <td><input type="number" step="any" class="form-control" name="items[1][cu_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[1][cu_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[1][cu_resultado]" readonly></td>
                                                <td><input type="text" class="form-control" name="items[1][observaciones]"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="fila-muestra">
                                                <td class="numero-fila text-center">3</td>
                                                <td><input type="text" class="form-control" name="items[2][codigo_interno]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][peso_muestra]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][humedad]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][volumen_final]"></td>
                                                <!-- Mn -->
                                                <td><input type="number" step="any" class="form-control" name="items[2][mn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][mn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[2][mn_resultado]" readonly></td>
                                                <!-- Fe -->
                                                <td><input type="number" step="any" class="form-control" name="items[2][fe_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][fe_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[2][fe_resultado]" readonly></td>
                                                <!-- Zn -->
                                                <td><input type="number" step="any" class="form-control" name="items[2][zn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][zn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[2][zn_resultado]" readonly></td>
                                                <!-- Cu -->
                                                <td><input type="number" step="any" class="form-control" name="items[2][cu_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[2][cu_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[2][cu_resultado]" readonly></td>
                                                <td><input type="text" class="form-control" name="items[2][observaciones]"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="fila-muestra">
                                                <td class="numero-fila text-center">4</td>
                                                <td><input type="text" class="form-control" name="items[3][codigo_interno]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][peso_muestra]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][humedad]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][volumen_final]"></td>
                                                <!-- Mn -->
                                                <td><input type="number" step="any" class="form-control" name="items[3][mn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][mn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[3][mn_resultado]" readonly></td>
                                                <!-- Fe -->
                                                <td><input type="number" step="any" class="form-control" name="items[3][fe_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][fe_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[3][fe_resultado]" readonly></td>
                                                <!-- Zn -->
                                                <td><input type="number" step="any" class="form-control" name="items[3][zn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][zn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[3][zn_resultado]" readonly></td>
                                                <!-- Cu -->
                                                <td><input type="number" step="any" class="form-control" name="items[3][cu_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[3][cu_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[3][cu_resultado]" readonly></td>
                                                <td><input type="text" class="form-control" name="items[3][observaciones]"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="fila-muestra">
                                                <td class="numero-fila text-center">5</td>
                                                <td><input type="text" class="form-control" name="items[4][codigo_interno]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][peso_muestra]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][humedad]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][volumen_final]"></td>
                                                <!-- Mn -->
                                                <td><input type="number" step="any" class="form-control" name="items[4][mn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][mn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[4][mn_resultado]" readonly></td>
                                                <!-- Fe -->
                                                <td><input type="number" step="any" class="form-control" name="items[4][fe_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][fe_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[4][fe_resultado]" readonly></td>
                                                <!-- Zn -->
                                                <td><input type="number" step="any" class="form-control" name="items[4][zn_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][zn_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[4][zn_resultado]" readonly></td>
                                                <!-- Cu -->
                                                <td><input type="number" step="any" class="form-control" name="items[4][cu_lectura]"></td>
                                                <td><input type="number" step="any" class="form-control" name="items[4][cu_factor]"></td>
                                                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[4][cu_resultado]" readonly></td>
                                                <td><input type="text" class="form-control" name="items[4][observaciones]"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success" id="add_item_row">
                                        <i class="fas fa-plus"></i> Agregar muestra
                            </button>
                                </div>
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
                                    <i class="fas fa-save"></i> Guardar Análisis
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

<style>
    /* Asegurar que el contenido no se superponga con la barra lateral */
    .container-fluid {
        padding-left: 20px;
        padding-right: 20px;
        padding-bottom: 20px;
        width: calc(100% - 250px) !important;
        max-width: calc(100% - 250px) !important;
    }
    
    /* Responsive para pantallas pequeñas */
    @media (max-width: 768px) {
        .container-fluid {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 10px;
            padding-right: 10px;
        }
    }
    
    /* Estilos para mejorar la apariencia de la tabla */
    #items_ensayo_table th {
        background-color: #6c757d !important;
        color: white !important;
        border: 2px solid #495057 !important;
        font-size: 0.9rem;
    }
    
    #items_ensayo_table td {
        border: 1px solid #dee2e6 !important;
        vertical-align: middle;
    }
    
    /* Bordes más gruesos para separar las secciones de micronutrientes */
    #items_ensayo_table td:nth-child(6),
    #items_ensayo_table td:nth-child(10),
    #items_ensayo_table td:nth-child(14),
    #items_ensayo_table td:nth-child(18) {
        border-left: 3px solid #495057 !important;
    }
    
    #items_ensayo_table td:nth-child(9),
    #items_ensayo_table td:nth-child(13),
    #items_ensayo_table td:nth-child(17),
    #items_ensayo_table td:nth-child(21) {
        border-right: 3px solid #495057 !important;
    }
    
    /* Fondo más gris para campos calculados */
    #items_ensayo_table input[readonly] {
        background-color: #e9ecef !important;
        font-weight: bold !important;
    }
    
    /* Mejorar el espaciado de los inputs */
    #items_ensayo_table input {
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
    }
    
    /* Estilos para los controles analíticos */
    /* Removed custom card-header styling to match batch_process appearance */
    
    /* Asegurar que las tarjetas tengan el ancho correcto */
    .card {
        margin-bottom: 20px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Mejorar la legibilidad del formulario */
    .form-control {
        border-radius: 0.375rem;
    }
    
    .btn {
        border-radius: 0.375rem;
    }
    
    /* Ocultar la barra de navegación superior */
    .main-header {
        display: none !important;
    }
    
    /* Ajustar el contenido para que no tenga margen superior */
    .content-wrapper {
        margin-top: 0 !important;
        margin-left: 250px !important;
        width: calc(100% - 250px) !important;
    }
    
    /* Ajustar el contenedor principal */
    .container-fluid {
        margin-left: 0;
        padding-left: 20px;
        padding-right: 20px;
        padding-bottom: 20px;
        margin-top: 20px;
    }
    
    /* Ocultar el footer en esta página */
    .main-footer {
        display: none !important;
    }
    
    /* También ocultar cualquier footer genérico */
    footer {
        display: none !important;
    }
    
    /* Asegurar que el contenido principal no tenga altura mínima fija */
    .content-wrapper {
        min-height: auto;
    }
    
    /* Asegurar que las tablas se adapten al espacio disponible */
    .table-responsive {
        max-width: 100%;
        overflow-x: auto;
    }
    
    /* Estilos para la tabla de micronutrientes */
    #items_ensayo_table {
        min-width: 1200px;
        max-width: 100%;
    }
    
    /* Bordes prominentes para separar secciones principales */
    #items_ensayo_table th:nth-child(4),
    #items_ensayo_table td:nth-child(4) {
        border-right: 3px solid #000 !important;
    }
    
    #items_ensayo_table th:nth-child(10),
    #items_ensayo_table td:nth-child(10) {
        border-right: 3px solid #000 !important;
    }
    
    #items_ensayo_table th:nth-child(13),
    #items_ensayo_table td:nth-child(13) {
        border-right: 3px solid #000 !important;
    }
    
    #items_ensayo_table th:nth-child(16),
    #items_ensayo_table td:nth-child(16) {
        border-right: 3px solid #000 !important;
    }
    
    /* Fondo más oscuro para las celdas de resultados */
    #items_ensayo_table th:nth-child(7),
    #items_ensayo_table td:nth-child(7) input,
    #items_ensayo_table th:nth-child(10),
    #items_ensayo_table td:nth-child(10) input,
    #items_ensayo_table th:nth-child(13),
    #items_ensayo_table td:nth-child(13) input,
    #items_ensayo_table th:nth-child(16),
    #items_ensayo_table td:nth-child(16) input {
        background-color: #d1d3d4 !important;
    }
    
    /* Estilos para los inputs de resultados */
    #items_ensayo_table td:nth-child(7) input,
    #items_ensayo_table td:nth-child(10) input,
    #items_ensayo_table td:nth-child(13) input,
    #items_ensayo_table td:nth-child(16) input {
        background-color: #d1d3d4 !important;
        border: 1px solid #adb5bd !important;
    }
</style>

<script>
// Cálculos automáticos para micronutrientes
    document.addEventListener('DOMContentLoaded', function() {
        let itemRowIndex = 0;
        
        // Función para agregar nueva fila
        document.getElementById('add_item_row').addEventListener('click', function() {
            itemRowIndex++;
            const tbody = document.querySelector('#items_ensayo_table tbody');
            const newRow = document.createElement('tr');
            newRow.className = 'fila-muestra';
            newRow.innerHTML = `
                    <td class="numero-fila text-center">${itemRowIndex + 1}</td>
                <td><input type="text" class="form-control" name="items[${itemRowIndex}][codigo_interno]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][peso_muestra]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][humedad]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][volumen_final]"></td>
                    <!-- Mn -->
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][mn_lectura]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][mn_factor]"></td>
                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[${itemRowIndex}][mn_resultado]" readonly></td>
                    <!-- Fe -->
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][fe_lectura]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][fe_factor]"></td>
                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[${itemRowIndex}][fe_resultado]" readonly></td>
                    <!-- Zn -->
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][zn_lectura]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][zn_factor]"></td>
                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[${itemRowIndex}][zn_resultado]" readonly></td>
                    <!-- Cu -->
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][cu_lectura]"></td>
                <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][cu_factor]"></td>
                <td><input type="number" step="any" class="form-control" style="background-color: #d1d3d4;" name="items[${itemRowIndex}][cu_resultado]" readonly></td>
                <td><input type="text" class="form-control" name="items[${itemRowIndex}][observaciones]"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
            `;
            tbody.appendChild(newRow);
            
            // Agregar event listeners a los nuevos inputs
            addCalculationListeners(newRow);
        });
        
        // Función para re-enumerar filas
        function reenumerarFilas() {
            document.querySelectorAll('#items_ensayo_table tbody tr').forEach(function(row, index) {
                row.querySelector('.numero-fila').textContent = index + 1;
            });
        }
        
        // Función para eliminar fila
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
                const row = e.target.closest('tr');
                if (row && row.classList.contains('fila-muestra')) {
                    row.remove();
                    // Reenumerar las filas restantes
                    const filas = document.querySelectorAll('.fila-muestra');
                    filas.forEach((fila, index) => {
                        const numeroFila = fila.querySelector('.numero-fila');
                        if (numeroFila) {
                            numeroFila.textContent = index + 1;
                        }
                    });
                }
            }
        });
        
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

    // Función para agregar listeners de cálculo a una fila específica
    function addCalculationListeners(row) {
        // Event listeners para campos que afectan todos los cálculos
        const camposGenerales = ['peso_muestra', 'humedad', 'volumen_final'];
        camposGenerales.forEach(campo => {
            const input = row.querySelector(`input[name*="[${campo}]"]`);
            if (input) {
                input.addEventListener('input', function() {
                    ['mn', 'fe', 'zn', 'cu'].forEach(nutriente => {
                        calcularResultado(row, nutriente);
                });
            });
        }
        });

        // Event listeners específicos para cada micronutriente
        ['mn', 'fe', 'zn', 'cu'].forEach(nutriente => {
            const lecturaInput = row.querySelector(`input[name*="${nutriente}_lectura"]`);
            const factorInput = row.querySelector(`input[name*="${nutriente}_factor"]`);
            
            if (lecturaInput) {
                lecturaInput.addEventListener('input', function() {
                    calcularResultado(row, nutriente);
                });
            }
            
            if (factorInput) {
                factorInput.addEventListener('input', function() {
                    calcularResultado(row, nutriente);
                });
            }
        });
    }

    // Función para calcular el resultado de un micronutriente en una fila
    function calcularResultado(row, nutriente) {
        const pesoMuestra = parseFloat(row.querySelector(`input[name*="[peso_muestra]"]`).value) || 0;
        const humedad = parseFloat(row.querySelector(`input[name*="[humedad]"]`).value) || 0;
        const volumenFinal = parseFloat(row.querySelector(`input[name*="[volumen_final]"]`).value) || 0;
        const lectura = parseFloat(row.querySelector(`input[name*="${nutriente}_lectura"]`).value) || 0;
        const factor = parseFloat(row.querySelector(`input[name*="${nutriente}_factor"]`).value) || 0;

        let resultado = 0;
        if (pesoMuestra > 0 && lectura > 0) {
            // Fórmula: (Lectura × Factor dilución × Vol final) / Peso muestra × (100 + Humedad) / 100
            resultado = (lectura * factor * volumenFinal) / pesoMuestra * (100 + humedad) / 100;
        }
        row.querySelector(`input[name*="${nutriente}_resultado"]`).value = resultado.toFixed(2);
    }

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

    // Agregar event listeners a las filas existentes
    document.querySelectorAll('.fila-muestra').forEach(function(row) {
        addCalculationListeners(row);
    });

    });
</script>
