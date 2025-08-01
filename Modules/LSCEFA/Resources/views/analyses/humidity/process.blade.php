@extends('layouts.app')

@section('title', 'Procesar Análisis de Humedad')

@section('contenido')
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Procesar Análisis de Humedad</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('humidity_analysis.index') }}">Gestión de
                                    Humedad</a></li>
                            <li class="breadcrumb-item active">Procesar Análisis</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <!-- Para mensajes de Campos no ingresados -->
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
        <form
            action="{{ route('humidity_analysis.store_humidity_analysis', [$process->process_id, $humidityAnalysis->service_id]) }}"
            method="POST">
            @csrf

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

                    <!-- INFORMACIÓN GENERAL -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Información General</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <!-- Proceso (solo lectura) -->
                                <div class="form-group col-md-3">
                                    <label for="proceso">Procesos Involucrados</label>
                                    <input type="text" class="form-control" id="proceso"
                                        value="{{ $process->process_id }}" readonly>
                                </div>

                                <!-- Servicio (solo lectura) -->
                                <div class="form-group col-md-3">
                                    <label for="servicio">Servicios Involucrados</label>
                                    <input type="text" class="form-control" id="servicio"
                                        value="{{ $humidityAnalysis->service->descripcion ?? 'Humedad' }}" readonly>
                                </div>

                                <!-- Consecutivo -->
                                <div class="form-group col-md-3">
                                    <label for="consecutivo_no">Consecutivo No.</label>
                                    <input type="text" class="form-control" id="consecutivo_no" name="consecutivo_no">
                                </div>
                            </div>

                            <div class="form-row mt-2">
                                <!-- Fecha del análisis -->
                                <div class="form-group col-md-3"> <label for="fecha_analisis">Fecha del Análisis</label>
                                    <input type="date" class="form-control" id="fecha_analisis" name="fecha_analisis"
                                        value="{{ old('fecha_analisis') }}">
                                </div>

                                <!-- Analista (solo lectura) -->
                                <div class="form-group col-md-3">
                                    <label for="analista">Analista</label>
                                    <input type="text" class="form-control" id="analista"
                                        value="{{ Auth::user()->name ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                        </div>

                        <!-- DETALLES DEL EQUIPO -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Detalles del Equipo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Hora ingreso al horno -->
                                    <div class="col-md-3 mb-3">
                                        <label for="hora_ingreso_horno">Hora ingreso al horno</label>
                                        <input type="time" class="form-control" name="hora_ingreso_horno"
                                            id="hora_ingreso_horno" value="{{ old('hora_ingreso_horno') }}">
                                    </div>

                                    <!-- Hora salida del horno -->
                                    <div class="col-md-3 mb-3">
                                        <label for="hora_salida_horno">Hora salida del horno</label>
                                        <input type="time" class="form-control" name="hora_salida_horno"
                                            id="hora_salida_horno" value="{{ old('hora_salida_horno') }}">
                                    </div>

                                    <!-- Temperatura del horno -->
                                    <div class="col-md-3 mb-3">
                                        <label for="temperatura_horno">Temperatura del horno (°C)</label>
                                        <input type="number" step="any" class="form-control" name="temperatura_horno"
                                            id="temperatura_horno" value="{{ old('temperatura_horno') }}">
                                    </div>

                                    <!-- Resolución instrumental -->
                                    <div class="col-md-3 mb-3">
                                        <label for="resolucion_instrumental">Resolución instrumental</label>
                                        <input type="text" class="form-control" name="resolucion_instrumental"
                                            id="resolucion_instrumental" value="{{ old('resolucion_instrumental') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Nombre del método -->
                                    <div class="col-md-4 mb-3">
                                        <label for="nombre_metodo">Nombre del método</label>
                                        <input type="text" class="form-control" name="nombre_metodo"
                                            id="nombre_metodo" value="{{ old('nombre_metodo') }}">
                                    </div>

                                    <!-- Equipo utilizado -->
                                    <div class="col-md-4 mb-3">
                                        <label for="equipo_utilizado">Equipo Utilizado</label>
                                        <input type="text" class="form-control" name="equipo_utilizado"
                                            id="equipo_utilizado" value="{{ old('equipo_utilizado') }}">
                                    </div>

                                    <!-- Unidades de reporte del equipo -->
                                    <div class="col-md-4 mb-3">
                                        <label for="unidades_reporte_equipo">Unidades de Reporte del Equipo</label>
                                        <input type="text" class="form-control" name="unidades_reporte_equipo"
                                            id="unidades_reporte_equipo" value="{{ old('unidades_reporte_equipo') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Intervalo del método -->
                                    <div class="col-md-4 mb-3">
                                        <label for="intervalo_metodo">Intervalo del método</label>
                                        <input type="text" class="form-control" name="intervalo_metodo"
                                            id="intervalo_metodo" value="{{ old('intervalo_metodo') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- CONTROLES DE CALIDAD -->
                        <div class="card mt-3">
                            <div class="card-header py-2">
                                <h6 class="mb-0">Controles de Calidad Analíticos</h6>
                            </div>
                            <div class="card-body p-2">

                                <!-- Muestra Fortificada -->
                                <div class="border p-2 mb-2">
                                    <h6 class="mb-2">Muestra Fortificada</h6>
                                    <div class="row g-1">
                                        <div class="col-3">
                                            <label class="small">Masa suelo (g)</label>
                                            <input type="number" step="any" id="masa_suelo"
                                                name="controles_analiticos[masa_suelo]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">Masa agua (g)</label>
                                            <input type="number" step="any" id="masa_agua"
                                                name="controles_analiticos[masa_agua]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">Masa suelo seco (g)</label>
                                            <input type="text" id="masa_suelo_seco"
                                                name="controles_analiticos[masa_suelo_seco]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-3">
                                            <label class="small">% Humedad teórica</label>
                                            <input type="text" id="humedad_fortificada_teorica"
                                                name="controles_analiticos[humedad_fortificada_teorica]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-3">
                                            <label class="small">ID Muestra</label>
                                            <input type="text" id="identificacion_mf"
                                                name="controles_analiticos[identificacion_mf]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">% Humedad obtenida</label>
                                            <input type="number" step="any" id="humedad_obtenida"
                                                name="controles_analiticos[humedad_obtenida]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">% Humedad fortificada</label>
                                            <input type="number" step="any" id="humedad_fortificada"
                                                name="controles_analiticos[humedad_fortificada]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-2">
                                            <label class="small">%REC</label>
                                            <input type="text" id="recuperacion"
                                                name="controles_analiticos[recuperacion]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-1">
                                            <label class="small">Aceptable</label>
                                            <input type="text" id="aceptable_fortificada"
                                                name="controles_analiticos[aceptable_fortificada]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Muestra Referencia -->
                                <div class="border p-2 mb-2">
                                    <h6 class="mb-2">Muestra Referencia</h6>
                                    <div class="row g-1">
                                        <div class="col-3">
                                            <label class="small">ID Muestra</label>
                                            <input type="text" id="identificacion_mr"
                                                name="controles_analiticos[identificacion_mr]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">Valor Referencia</label>
                                            <input type="number" step="any" id="valor_referencia"
                                                name="controles_analiticos[valor_referencia]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">Valor Obtenido</label>
                                            <input type="number" step="any" id="valor_obtenido"
                                                name="controles_analiticos[valor_obtenido]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-2">
                                            <label class="small">%REC</label>
                                            <input type="text" id="recuperacion_referencia"
                                                name="controles_analiticos[recuperacion_referencia]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-1">
                                            <label class="small">Aceptable</label>
                                            <input type="text" id="aceptable_referencia"
                                                name="controles_analiticos[aceptable_referencia]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Duplicado Muestra -->
                                <div class="border p-2 mb-2">
                                    <h6 class="mb-2">Duplicado Muestra</h6>
                                    <div class="row g-1">
                                        <div class="col-3">
                                            <label class="small">ID Muestra</label>
                                            <input type="text" id="identificacion_dm"
                                                name="controles_analiticos[identificacion_dm]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">% Réplica 1</label>
                                            <input type="number" step="any" id="humedad_replica_1"
                                                name="controles_analiticos[humedad_replica_1]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">% Réplica 2</label>
                                            <input type="number" step="any" id="humedad_replica_2"
                                                name="controles_analiticos[humedad_replica_2]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-2">
                                            <label class="small">% DPR</label>
                                            <input type="text" id="dpr" name="controles_analiticos[dpr]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-1">
                                            <label class="small">Aceptable</label>
                                            <input type="text" id="aceptable_duplicado"
                                                name="controles_analiticos[aceptable_duplicado]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Blanco del método -->
                                <div class="border p-2 mb-2">
                                    <h6 class="mb-2">Blanco del método</h6>
                                    <div class="row g-1">
                                        <div class="col-3">
                                            <label class="small">Identificación</label>
                                            <input type="text" id="identificacion_bm"
                                                name="controles_analiticos[identificacion_bm]"
                                                class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">Resultado</label>
                                            <input type="number" step="any" id="resultado_blanco"
                                                name="controles_analiticos[resultado_blanco]"
                                                class="form-control form-control-sm"
                                                oninput="evaluarAceptabilidadBlanco()">
                                        </div>
                                        <div class="col-3">
                                            <label class="small">LCM</label>
                                            <input type="number" step="any" id="lcm"
                                                name="controles_analiticos[lcm]" class="form-control form-control-sm"
                                                oninput="evaluarAceptabilidadBlanco()">
                                        </div>
                                        <div class="col-2">
                                            <label class="small">Rango del Método</label>
                                            <input type="text" name="controles_calidad[blanco][rango_metodo]"
                                                id="rango_metodo" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-1">
                                            <label class="small">Aceptable</label>
                                            <input type="text" id="aceptable_blanco"
                                                name="controles_analiticos[aceptable_blanco]"
                                                class="form-control form-control-sm" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="small">Observaciones</label>
                                    <textarea name="controles_calidad[observaciones]" id="observaciones" rows="2"
                                        class="form-control form-control-sm"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- REGISTRO DE MUESTRAS -->
                    <div class="card mt-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0">Registro de Muestras</h6>
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-bordered text-center" id="tablaMuestras">
                                <thead>
                                    <tr>
                                        <th>Código interno</th>
                                        <th>Peso Cápsula, Pc (g)</th>
                                        <th>Peso Muestra (g)</th>
                                        <th>Pmh (Pc + Muestra)</th>
                                        <th>Pms (g)</th>
                                        <th>% Humedad</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" id="codigo_interno" name="codigo_interno" class="form-control" required>
                                        </td>
                                        <td><input type="number" step="0.0001" id=" peso_capsula" name="peso_capsula"
                                                class="form-control pc" required></td>
                                        <td><input type="number" step="0.0001" name="peso_muestra"
                                                class="form-control muestra" required></td>
                                        <td><input type="number" step="0.0001" id="peso_capsula_muestra_humedad" name="peso_capsula_muestra_humedad"
                                                class="form-control pmh" readonly></td>
                                        <td><input type="number" step="0.0001" id="peso_capsula_muestra_seca" name="peso_capsula_muestra_seca"
                                                class="form-control pms" required></td>
                                        <td><input type="text" name="porcentaje_humedad"
                                                class="form-control humedad" readonly></td>
                                        <td><input type="text" name="observaciones" class="form-control"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-primary" onclick="agregarFila()">+ Agregar
                                    fila</button>
                                <button type="button" class="btn btn-danger" onclick="quitarFila()">- Quitar
                                    fila</button>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="card">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar Análisis de Humedad</button>
                            <a href="{{ route('humidity_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                    <script src="{{ asset('js/controles_analiticos.js') }}"></script>

            </section>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Muestra Fortificada - Cálculo masa suelo seco
            document.getElementById('masa_suelo').addEventListener('input', calcularFortificada);
            document.getElementById('humedad_obtenida').addEventListener('input', calcularFortificada);
            document.getElementById('masa_agua').addEventListener('input', calcularFortificada);
            document.getElementById('humedad_fortificada_teorica').addEventListener('input', calcularFortificada);

            function calcularFortificada() {
                const masaSuelo = parseFloat(document.getElementById('masa_suelo').value) || 0;
                const humedadObtenida = parseFloat(document.getElementById('humedad_obtenida').value) || 0;
                const masaAgua = parseFloat(document.getElementById('masa_agua').value) || 0;
                const humedadFortificada = parseFloat(document.getElementById('humedad_fortificada')
                    .value) || 0;

                const masaSueloSeco = masaSuelo * (100 / (humedadObtenida + 100));
                document.getElementById('masa_suelo_seco').value = masaSueloSeco.toFixed(4);

                const humedadTeorica = (((masaSuelo + masaAgua) - masaSueloSeco) / masaSueloSeco) * 100;
                document.getElementById('humedad_fortificada_teorica').value = humedadTeorica.toFixed(2);

                const recuperacion = (humedadFortificada / humedadTeorica) * 100;
                document.getElementById('recuperacion').value = recuperacion.toFixed(2);

                const aceptable = (recuperacion >= 70 && recuperacion <= 130) ? "Aceptable" : "No aceptable";
                document.getElementById('aceptable_fortificada').value = aceptable;
            }

            // Muestra Referencia - Cálculo %REC
            document.getElementById('valor_referencia').addEventListener('input', calcularReferencia);
            document.getElementById('valor_obtenido').addEventListener('input', calcularReferencia);

            function calcularReferencia() {
                const valorReferencia = parseFloat(document.getElementById('valor_referencia').value) || 0;
                const valorObtenido = parseFloat(document.getElementById('valor_obtenido').value) || 0;

                const recuperacionReferencia = Math.abs(valorObtenido) / valorReferencia * 100;
                document.getElementById('recuperacion_referencia').value = recuperacionReferencia.toFixed(2);

                const aceptableRef = (recuperacionReferencia >= 70 && recuperacionReferencia <= 130) ? "Aceptable" :
                    "No aceptable";
                document.getElementById('aceptable_referencia').value = aceptableRef;
            }

            // Duplicado Muestra - Cálculo %DPR
            document.getElementById('humedad_replica_1').addEventListener('input', calcularDuplicado);
            document.getElementById('humedad_replica_2').addEventListener('input', calcularDuplicado);

            function calcularDuplicado() {
                const replica1 = parseFloat(document.getElementById('humedad_replica_1').value) || 0;
                const replica2 = parseFloat(document.getElementById('humedad_replica_2').value) || 0;

                const dpr = Math.abs(replica1 - replica2) / ((replica1 + replica2) / 2) * 100;
                document.getElementById('dpr').value = dpr.toFixed(2);

                const aceptableDup = (dpr < 25) ? "Aceptable" : "No aceptable";
                document.getElementById('aceptable_duplicado').value = aceptableDup;
            }
        });
    </script>

    <script>
        function evaluarAceptabilidadBlanco() {
            const resultado = parseFloat(document.getElementById('resultado_blanco').value);
            const lcm = parseFloat(document.getElementById('lcm').value);
            const aceptableField = document.getElementById('aceptable_blanco');

            if (!isNaN(resultado) && !isNaN(lcm)) {
                if (resultado <= lcm) {
                    aceptableField.value = "Aceptable";

                } else {
                    aceptableField.value = "No Aceptable";

                }
            } else {
                aceptableField.value = "";
                aceptableField.style.color = "black";
            }
        }
    </script>
    <script>
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
        if (pmh > 0 && pc > 0 && pms > 0 && (pmh - pc) !== 0) {
            const humedad = ((pmh - pms) / (pmh - pc)) * 100;
            humedadInput.value = humedad.toFixed(2);
        } else {
            humedadInput.value = '';
        }
    }

    function agregarFila() {
        const table = document.getElementById("tablaMuestras").getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);

        newRow.querySelectorAll('input').forEach(input => {
            input.value = '';
        });

        table.appendChild(newRow);
    }

    function quitarFila() {
        const table = document.getElementById("tablaMuestras").getElementsByTagName('tbody')[0];
        if (table.rows.length > 1) {
            table.deleteRow(table.rows.length - 1);
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target.closest('tr') &&
            (e.target.classList.contains('pc') || 
             e.target.classList.contains('muestra') || 
             e.target.classList.contains('pms'))) {
            calcularValores(e.target.closest('tr'));
        }
    });
</script>

