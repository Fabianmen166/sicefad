@extends('lscefa::layouts.technical_no_navbar')

@section('title', 'Procesar Análisis de Humedad')

@section('content')
<div class="content-wrapper p-0 m-0" style="max-width: 100%;">
    <!-- Content Header -->
    <section class="content-header p-0 m-0">
        <div class="container-fluid p-0 m-0"></div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Se encontraron los siguientes errores:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Content -->
    <form action="{{ route('lscefa.technical.analyses.humidity.store') }}" method="POST" id="humidity-form" class="w-100">
        @csrf
        <input type="hidden" name="process_id" value="{{ $process->process_id }}">
        <input type="hidden" name="analysis_id" value="{{ $serviceProcessDetail->id }}">
       
        <section class="content p-0 m-0">
            <div class="container-fluid p-0 m-0">
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

                <!-- INFORMACIÓN GENERAL -->
                <div class="card border-0 shadow-none">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Información General</h3>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- Proceso (solo lectura) -->
                            <div class="form-group col-md-3">
                                <label for="proceso">Procesos Involucrados</label>
                                <input type="text" class="form-control form-control-sm" id="proceso"
                                    value="{{ $process->process_id }}" readonly>
                            </div>
                            <!-- Servicio (solo lectura) -->
                            <div class="form-group col-md-3">
                                <label for="servicio">Servicios Involucrados</label>
                                <input type="text" class="form-control form-control-sm" id="servicio"
                                    value="{{ $service->descripcion ?? 'Humedad' }}" readonly>
                            </div>
                            <!-- Consecutivo -->
                            <div class="form-group col-md-3">
                                <label for="consecutivo_no">Consecutivo No.</label>
                                <input type="text" class="form-control form-control-sm" id="consecutivo_no" name="consecutivo_no"
                                    value="{{ old('consecutivo_no') }}" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <!-- Fecha del análisis -->
                            <div class="form-group col-md-3">
                                <label for="fecha_analisis">Fecha del Análisis</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_analisis" name="fecha_analisis"
                                    value="{{ old('fecha_analisis', date('Y-m-d')) }}" required>
                            </div>
                            <!--fecha fin del analis -->
                            <div class="form-group col-md-3">
                                <label for="fecha_fin_analisis">Fecha fin del Análisis</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_fin_analisis"
                                    name="fecha_fin_analisis" value="{{ old('fecha_fin_analisis', date('Y-m-d')) }}) }}">
                            </div>
                            <!-- Analista (solo lectura) -->
                            <div class="form-group col-md-3">
                                <label for="analista">Analista</label>
                                <input type="text" class="form-control form-control-sm" id="analista"
                                    value="{{ $user ? $user->nickname : '' }}" readonly>
                                <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DETALLES DEL EQUIPO -->
                <div class="card mt-3 border-0 shadow-none">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Detalles del Equipo</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- Hora ingreso al horno -->
                            <div class="col-md-3">
                                <label for="hora_ingreso_horno">Hora ingreso al horno</label>
                                <input type="time" class="form-control form-control-sm" name="hora_ingreso_horno"
                                    id="hora_ingreso_horno" value="{{ old('hora_ingreso_horno') }}" required>
                            </div>
                            <!-- Hora salida del horno -->
                            <div class="col-md-3">
                                <label for="hora_salida_horno">Hora salida del horno</label>
                                <input type="time" class="form-control form-control-sm" name="hora_salida_horno"
                                    id="hora_salida_horno" value="{{ old('hora_salida_horno') }}" required>
                            </div>
                            <!-- Temperatura del horno -->
                            <div class="col-md-3">
                                <label for="temperatura_horno">Temperatura del horno (°C)</label>
                                <input type="number" step="0.1" class="form-control form-control-sm" name="temperatura_horno"
                                    id="temperatura_horno" value="{{ old('temperatura_horno') }}" required>
                            </div>
                            <!-- Resolución instrumental -->
                            <div class="col-md-3">
                                <label for="resolucion_instrumental">Resolución instrumental</label>
                                <input type="text" class="form-control form-control-sm" name="resolucion_instrumental"
                                    id="resolucion_instrumental" value="{{ old('resolucion_instrumental') }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <!-- Nombre del método -->
                            <div class="col-md-4">
                                <label for="nombre_metodo">Nombre del método</label>
                                <input type="text" class="form-control form-control-sm" name="nombre_metodo"
                                    id="nombre_metodo" value="{{ old('nombre_metodo', 'NTC 5403:2021') }}" required>
                            </div>
                            <!-- Equipo utilizado -->
                            <div class="col-md-4">
                                <label for="equipo_utilizado">Equipo Utilizado</label>
                                <input type="text" class="form-control form-control-sm" name="equipo_utilizado"
                                    id="equipo_utilizado" value="{{ old('equipo_utilizado', 'Horno') }}" required>
                            </div>
                            <!-- Unidades de reporte del equipo -->
                            <div class="col-md-4">
                                <label for="unidades_reporte_equipo">Unidades de Reporte del Equipo</label>
                                <input type="text" class="form-control form-control-sm" name="unidades_reporte_equipo"
                                    id="unidades_reporte_equipo" value="{{ old('unidades_reporte_equipo', 'g/100g') }}" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <!-- Intervalo del método -->
                            <div class="col-md-4">
                                <label for="intervalo_metodo">Intervalo del método</label>
                                <input type="text" class="form-control form-control-sm" name="intervalo_metodo"
                                    id="intervalo_metodo" value="{{ old('intervalo_metodo', '0.1 - 15%') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLES DE CALIDAD -->
                <div class="card mt-3 border-0 shadow-none">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">Controles de Calidad Analíticos</h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Muestra Fortificada -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Muestra Fortificada</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="small">Masa de suelo (g)</label>
                                    <input type="number" step="0.01" id="masa_suelo"
                                        name="controles_analiticos[masa_suelo]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.masa_suelo') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Masa de agua adicionada (g)</label>
                                    <input type="number" step="0.01" id="masa_agua"
                                        name="controles_analiticos[masa_agua]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.masa_agua') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Masa de suelo seco (g)</label>
                                    <input type="text" id="masa_suelo_seco"
                                        name="controles_analiticos[masa_suelo_seco]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small">% Humedad fortificada teórica</label>
                                    <input type="text" id="humedad_fortificada_teorica"
                                        name="controles_analiticos[humedad_fortificada_teorica]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Identificacion de la Muestra</label>
                                    <input type="text" id="identificacion_mf"
                                        name="controles_analiticos[identificacion_mf]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.identificacion_mf') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">% Humedad obtenida en la muestra</label>
                                    <input type="number" step="0.01" id="humedad_obtenida"
                                        name="controles_analiticos[humedad_obtenida]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.humedad_obtenida') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">% Humedad muestra fortificada</label>
                                    <input type="number" step="0.0001" id="humedad_fortificada"
                                        name="controles_analiticos[humedad_fortificada]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.humedad_fortificada') }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="small">%Recuperacion</label>
                                    <input type="text" id="recuperacion"
                                        name="recuperacion"
                                        class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="small">Aceptable</label>
                                    <input type="text" id="aceptable_fortificada"
                                        name="controles_analiticos[aceptable_fortificada]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- Muestra Referencia -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Muestra Referencia</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="small">Identificacion de Muestra</label>
                                    <input type="text" id="identificacion_mr"
                                        name="controles_analiticos[identificacion_mr]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.identificacion_mr') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Valor Referencia % Humedad</label>
                                    <input type="number" step="0.0001" id="valor_referencia"
                                        name="controles_analiticos[valor_referencia]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.valor_referencia') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Valor Obtenido % Humedad</label>
                                    <input type="number" step="0.0001" id="valor_obtenido"
                                        name="controles_analiticos[valor_obtenido]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.valor_obtenido') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="small">%REC</label>
                                    <input type="text" id="recuperacion"
                                        name="controles_analiticos[recuperacion]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="small">Aceptable</label>
                                    <input type="text" id="aceptable_referencia"
                                        name="controles_analiticos[aceptable_referencia]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- Duplicado Muestra -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Duplicado Muestra</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="small">Identificacion de Muestra</label>
                                    <input type="text" id="identificacion_dm"
                                        name="controles_analiticos[identificacion_dm]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.identificacion_dm') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">% Humedad Réplica 1</label>
                                    <input type="number" step="0.0001" id="humedad_replica_1"
                                        name="controles_analiticos[humedad_replica_1]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.humedad_replica_1') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">% Humedad Réplica 2</label>
                                    <input type="number" step="0.0001" id="humedad_replica_2"
                                        name="controles_analiticos[humedad_replica_2]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.humedad_replica_2') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="small">% DPR</label>
                                    <input type="text" id="dpr" name="controles_analiticos[dpr]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                                <div class="col-md-1">
                                    <label class="small">Aceptable</label>
                                    <input type="text" id="aceptable_duplicado"
                                        name="controles_analiticos[aceptable_duplicado]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- Blanco del método -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Blanco del método</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="small">Identificación</label>
                                    <input type="text" id="identificacion_bm"
                                        name="controles_analiticos[identificacion_bm]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.identificacion_bm') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Resultado</label>
                                    <input type="number" step="0.01" id="resultado"
                                        name="controles_analiticos[resultado]"
                                        class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.resultado') }}"
                                        oninput="evaluarAceptabilidadBlanco()">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Límite de Cuantificación del Método (LCM)</label>
                                    <input type="number" step="0.01" id="limite_cuantificacion_metodo"
                                        name="controles_analiticos[limite_cuantificacion_metodo]" class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.limite_cuantificacion_metodo') }}"
                                        oninput="evaluarAceptabilidadBlanco()">
                                </div>
                                <div class="col-md-2">
                                    <label class="small">Rango del Método</label>
                                    <input type="text" name="controles_analiticos[rango_metodo]"
                                        id="rango_metodo" class="form-control form-control-sm"
                                        value="{{ old('controles_analiticos.rango_metodo') }}">
                                </div>
                                <div class="col-md-1">
                                    <label class="small">Aceptable</label>
                                    <input type="text" id="aceptable_blanco"
                                        name="controles_analiticos[aceptable_blanco]"
                                        class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="small">Observaciones</label>
                            <textarea name="controles_analiticos[observaciones]" id="observaciones" rows="2"
                                class="form-control">{{ old('controles_analiticos.observaciones') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- REGISTRO DE MUESTRAS -->
                <div class="card mt-3 border-0 shadow-none">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">Registro de Muestras</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center" id="tablaMuestras">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Código interno</th>
                                        <th>Peso Cápsula (g)</th>
                                        <th>Peso Muestra (g)</th>
                                        <th>Peso Cápsula + Muestra húmeda (g)</th>
                                        <th>Peso Cápsula + Muestra seca (g)</th>
                                        <th>% Humedad (g/100g)</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="muestras-body">
                                    <!-- Fila inicial -->
                                    <tr class="muestra-fila">
                                        <td>
                                            <input type="text" name="rows[0][codigo_interno]" class="form-control form-control-sm" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="rows[0][peso_capsula]" class="form-control form-control-sm pc" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="rows[0][peso_muestra]" class="form-control form-control-sm muestra">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="rows[0][peso_capsula_muestra_humedad]" class="form-control form-control-sm pmh">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="rows[0][peso_capsula_muestra_seca]" class="form-control form-control-sm pms">
                                        </td>
                                        <td>
                                            <input type="text" name="rows[0][porcentaje_humedad]" class="form-control form-control-sm humedad" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="rows[0][observaciones]" class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm quitar-fila">×</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" id="agregar-fila">+ Agregar fila</button>
                        </div>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="card mt-3 border-0 shadow-none">
                    <div class="card-footer bg-light">
                        <button type="submit" class="btn btn-primary">Guardar Análisis de Humedad</button>
                        <a href="{{ route('lscefa.technical.analyses.humidity.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ========== MUESTRA FORTIFICADA ==========
    document.getElementById('masa_suelo').addEventListener('input', calcularFortificada);
    document.getElementById('humedad_obtenida').addEventListener('input', calcularFortificada);
    document.getElementById('masa_agua').addEventListener('input', calcularFortificada);
    document.getElementById('humedad_fortificada').addEventListener('input', calcularFortificada);
    
    function calcularFortificada() {
        const masaSuelo = parseFloat(document.getElementById('masa_suelo').value) || 0;
        const humedadObtenida = parseFloat(document.getElementById('humedad_obtenida').value) || 0;
        const masaAgua = parseFloat(document.getElementById('masa_agua').value) || 0;
        const humedadFortificada = parseFloat(document.getElementById('humedad_fortificada').value) || 0;
        
        if (masaSuelo > 0 && humedadObtenida >= 0) {
            // 1. Calcular masa de suelo seco
            const denominador = humedadObtenida + 100;
            const factor = 100 / denominador;
            const masaSueloSeco = masaSuelo * factor;
            
            document.getElementById('masa_suelo_seco').value = masaSueloSeco.toFixed(2);
            
            // 2. Calcular humedad fortificada teórica
            if (masaAgua > 0) {
                const humedadTeorica = ((masaSuelo + masaAgua) - masaSueloSeco) / masaSueloSeco * 100;
                document.getElementById('humedad_fortificada_teorica').value = humedadTeorica.toFixed(2);
                
                // 3. Calcular %REC - Fórmula: (humedad_fortificada / humedad_teorica) * 100
                if (humedadFortificada > 0 && humedadTeorica > 0) {
                    const recuperacion = (humedadFortificada / humedadTeorica) * 100;
                    // CORRECCIÓN: Usar el ID correcto del HTML original 'recuperacion'
                    document.getElementById('recuperacion').value = recuperacion.toFixed(1);
                    
                    // Determinar si es aceptable (70-130%)
                    const aceptable = (recuperacion >= 70 && recuperacion <= 130) ? "Aceptable" : "No aceptable";
                    document.getElementById('aceptable_fortificada').value = aceptable;
                } else {
                    document.getElementById('recuperacion').value = '';
                    document.getElementById('aceptable_fortificada').value = '';
                }
            } else {
                document.getElementById('humedad_fortificada_teorica').value = '';
                document.getElementById('recuperacion').value = '';
                document.getElementById('aceptable_fortificada').value = '';
            }
        } else {
            document.getElementById('masa_suelo_seco').value = '';
            document.getElementById('humedad_fortificada_teorica').value = '';
            document.getElementById('recuperacion').value = '';
            document.getElementById('aceptable_fortificada').value = '';
        }
    }

    // ========== MUESTRA REFERENCIA ==========
    document.getElementById('valor_referencia').addEventListener('input', calcularReferencia);
    document.getElementById('valor_obtenido').addEventListener('input', calcularReferencia);

    function calcularReferencia() {
        const valorReferencia = parseFloat(document.getElementById('valor_referencia').value) || 0;
        const valorObtenido = parseFloat(document.getElementById('valor_obtenido').value) || 0;

        if (valorReferencia > 0 && valorObtenido >= 0) {
            // Fórmula: (valor_obtenido / valor_referencia) * 100
            const recuperacion = (valorObtenido / valorReferencia) * 100;
            
            // CORRECCIÓN: Usar selector por name ya que hay conflicto de IDs
            const recuperacionElement = document.querySelector('input[name="controles_analiticos[recuperacion]"]');
            if (recuperacionElement) {
                recuperacionElement.value = recuperacion.toFixed(1);
            }

            // Criterio de aceptación: 70-130%
            const aceptableRef = (recuperacion >= 70 && recuperacion <= 130) ?
                "Aceptable" : "No aceptable";
            document.getElementById('aceptable_referencia').value = aceptableRef;
        } else {
            const recuperacionElement = document.querySelector('input[name="controles_analiticos[recuperacion]"]');
            if (recuperacionElement) {
                recuperacionElement.value = '';
            }
            document.getElementById('aceptable_referencia').value = '';
        }
    }

    // ========== DUPLICADO MUESTRA (DPR) ==========
    document.getElementById('humedad_replica_1').addEventListener('input', calcularDPR);
    document.getElementById('humedad_replica_2').addEventListener('input', calcularDPR);

    function calcularDPR() {
        const replica1 = parseFloat(document.getElementById('humedad_replica_1').value) || 0;
        const replica2 = parseFloat(document.getElementById('humedad_replica_2').value) || 0;

        if (replica1 > 0 && replica2 > 0) {
            // Fórmula DPR: |replica1 - replica2| / ((replica1 + replica2) / 2) * 100
            const diferencia = Math.abs(replica1 - replica2);
            const promedio = (replica1 + replica2) / 2;
            const dpr = (diferencia / promedio) * 100;
            
            document.getElementById('dpr').value = dpr.toFixed(2);

            // Criterio de aceptación: DPR < 25%
            const aceptableDup = (dpr < 25) ? "Aceptable" : "No aceptable";
            document.getElementById('aceptable_duplicado').value = aceptableDup;
        } else {
            document.getElementById('dpr').value = '';
            document.getElementById('aceptable_duplicado').value = '';
        }
    }

    // ========== BLANCO DEL MÉTODO ==========
    document.getElementById('resultado').addEventListener('input', evaluarAceptabilidadBlanco);
    document.getElementById('limite_cuantificacion_metodo').addEventListener('input', evaluarAceptabilidadBlanco);

    function evaluarAceptabilidadBlanco() {
        const resultadoBlanco = parseFloat(document.getElementById('resultado').value) || 0;
        const lcm = parseFloat(document.getElementById('limite_cuantificacion_metodo').value) || 0;

        if (lcm > 0) {
            // Criterio: Resultado del blanco < LCM
            const aceptableBlanco = (resultadoBlanco < lcm) ? "Aceptable" : "No aceptable";
            document.getElementById('aceptable_blanco').value = aceptableBlanco;
        } else {
            document.getElementById('aceptable_blanco').value = '';
        }
    }

    // ========== FUNCIONES PARA TABLA DE MUESTRAS ==========
    
    // Función para calcular valores en la tabla
    function calcularValores(row) {
        const pcInput = row.querySelector('.pc');
        const muestraInput = row.querySelector('.muestra');
        const pmsInput = row.querySelector('.pms');
        const pmhInput = row.querySelector('.pmh');
        const humedadInput = row.querySelector('.humedad');

        const pc = parseFloat(pcInput?.value.replace(',', '.')) || 0;
        const muestra = parseFloat(muestraInput?.value.replace(',', '.')) || 0;
        const pms = parseFloat(pmsInput?.value.replace(',', '.')) || 0;

        // Calcular Pmh: Pc + Muestra
        const pmh = pc + muestra;
        pmhInput.value = pmh > 0 ? pmh.toFixed(4) : '';

        // Calcular % Humedad si hay datos válidos
        if (pmh > 0 && pc > 0 && pms > 0 && (pms - pc) !== 0) {
            const humedad = ((pmh - pms) / (pms - pc)) * 100;
            humedadInput.value = humedad.toFixed(2);

            // Si es la segunda fila (índice 1), copiar a "% Humedad muestra fortificada"
            const filas = document.querySelectorAll('.muestra-fila');
            const filaIndex = Array.from(filas).indexOf(row);
            
            if (filaIndex === 1) { // Segunda fila (índice 1)
                const humedadFortificada = document.getElementById('humedad_fortificada');
                if (humedadFortificada) {
                    humedadFortificada.value = humedad.toFixed(2);
                    // Trigger el cálculo de la muestra fortificada
                    calcularFortificada();
                }
            }
        } else {
            humedadInput.value = '';
        }
    }

    // ========== MANEJO DINÁMICO DE FILAS ==========
    
    // Contador para índices de filas
    let rowCount = 1;
    
    // Agregar nueva fila
    document.getElementById('agregar-fila').addEventListener('click', function() {
        const tbody = document.getElementById('muestras-body');
        const newRow = document.createElement('tr');
        newRow.className = 'muestra-fila';
        
        newRow.innerHTML = `
            <td>
                <input type="text" name="rows[${rowCount}][codigo_interno]" class="form-control form-control-sm" required>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[${rowCount}][peso_capsula]" class="form-control form-control-sm pc" required>
            </td>
            <td>
                <input type="number" step="0.01" name="rows[${rowCount}][peso_muestra]" class="form-control form-control-sm muestra">
            </td>
            <td>
                <input type="number" step="0.01" name="rows[${rowCount}][peso_capsula_muestra_humedad]" class="form-control form-control-sm pmh">
            </td>
            <td>
                <input type="number" step="0.01" name="rows[${rowCount}][peso_capsula_muestra_seca]" class="form-control form-control-sm pms">
            </td>
            <td>
                <input type="text" name="rows[${rowCount}][porcentaje_humedad]" class="form-control form-control-sm humedad" readonly>
            </td>
            <td>
                <input type="text" name="rows[${rowCount}][observaciones]" class="form-control form-control-sm">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm quitar-fila">×</button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        rowCount++;
        
        // Agregar event listeners a los nuevos inputs
        agregarEventListenersFila(newRow);
    });
    
    // Eliminar fila
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quitar-fila')) {
            const fila = e.target.closest('tr');
            if (document.querySelectorAll('.muestra-fila').length > 1) {
                fila.remove();
                // Actualizar los índices de las filas restantes
                actualizarIndicesFilas();
            } else {
                alert('Debe haber al menos una fila de muestra');
            }
        }
    });
    
    // Función para actualizar los índices de las filas después de eliminar
    function actualizarIndicesFilas() {
        const filas = document.querySelectorAll('.muestra-fila');
        let nuevoIndice = 0;
        
        filas.forEach((fila, index) => {
            fila.querySelectorAll('input').forEach(input => {
                // Actualizar el nombre del campo con el nuevo índice
                input.name = input.name.replace(/rows\[\d+\]/, `rows[${index}]`);
            });
            nuevoIndice = index;
        });
        
        // Actualizar el contador para la próxima fila nueva
        rowCount = nuevoIndice + 1;
    }
    
    // Agregar event listeners a la fila inicial
    document.querySelectorAll('.muestra-fila').forEach(fila => {
        agregarEventListenersFila(fila);
    });
    
    // Función para agregar event listeners a una fila específica
    function agregarEventListenersFila(fila) {
        const inputsCalculo = fila.querySelectorAll('.pc, .muestra, .pmh, .pms');
        
        inputsCalculo.forEach(input => {
            input.addEventListener('input', function() {
                calcularValores(fila);
            });
        });
    }
    
    // Configurar el envío del formulario
    const form = document.getElementById('humidity-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Enviando datos de todas las filas...');
        });
    }

    // Hacer la función calcularFortificada disponible globalmente para otros scripts
    window.calcularFortificada = calcularFortificada;
});
</script>
