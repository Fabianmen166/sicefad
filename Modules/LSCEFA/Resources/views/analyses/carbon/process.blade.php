@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de Carbono Orgánico Total')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">

            </div>
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
        <form action="{{ route('lscefa.technical.analyses.carbon.store') }}" method="POST">
            <input type="hidden" name="process_id" value="{{ $process->process_id }}">
            <input type="hidden" name="service_id" value="{{ $service->services_id }}">
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
                                <div class="form-group col-md-2">
                                    <label for="proceso">Procesos Involucrados</label>
                                    <input type="text" class="form-control" id="proceso"
                                        value="{{ $process->process_id }}" readonly>
                                </div>

                                <!-- Servicio (solo lectura) -->
                                <div class="form-group col-md-2">
                                    <label for="servicio">Servicios Involucrados</label>
                                    <input type="text" class="form-control" id="servicio"
                                        value="{{ $carbonAnalysis->service->descripcion ?? 'Carbono Orgánico' }}" readonly>
                                </div>
                                <!-- Analista (solo lectura) -->
                                <div class="form-group col-md-2">
                                    <label for="analista">Analista</label>
                                    <input type="text" class="form-control" id="analista"
                                        value="{{ Auth::user()->name ?? 'N/A' }}" readonly>
                                </div>
                                <!-- Consecutivo -->
                                <div class="form-group col-md-2">
                                    <label for="consecutivo_no">Consecutivo No.</label>
                                    <input type="text" class="form-control" id="consecutivo_no" name="consecutivo_no">
                                </div>

                                <!-- Unidades de reporte equipo -->
                                <div class="form-group col-md-2">
                                    <label for="unidades_reporte_equipo">Unidades de reporte equipo</label>
                                    <input type="text" class="form-control" name="unidades_reporte_equipo"
                                        id="unidades_reporte_equipo" value="{{ old('unidades_reporte_equipo', 'g/100g') }}">
                                </div>
                            </div>

                            <div class="form-row mt-2">
                                <!-- Fecha del análisis -->
                                <div class="form-group col-md-2">
                                    <label for="fecha_analisis">Fecha del Análisis</label>
                                    <input type="date" class="form-control" id="fecha_analisis" name="fecha_analisis"
                                        value="{{ old('fecha_analisis') }}">
                                </div>
                                <!-- Nombre del método -->
                                <div class="form-group col-md-4">
                                    <label for="nombre_metodo">Nombre del Método</label>
                                    <input type="text" class="form-control" name="nombre_metodo" id="nombre_metodo"
                                        value="{{ old('nombre_metodo', 'NTC 5403:2021') }}">
                                </div>

                                <!-- Equipo utilizado -->
                                <div class="form-group col-md-4">
                                    <label for="equipo_utilizado">Equipo utilizado</label>
                                    <input type="text" class="form-control" name="equipo_utilizado" id="equipo_utilizado"
                                        value="{{ old('equipo_utilizado') }}">
                                </div>

                                <!-- Intervalo del método -->
                                <div class="form-group col-md-4">
                                    <label for="intervalo_metodo">Intervalo del método</label>
                                    <input type="text" class="form-control" name="intervalo_metodo" id="intervalo_metodo"
                                        value="{{ old('intervalo_metodo', '0.1 - 15%') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="resolucion_instrumental">Resolución instrumental</label>
                                    <input type="text" class="form-control" name="resolucion_instrumental"
                                        id="resolucion_instrumental" value="{{ old('resolucion_instrumental') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTROLES DE CALIDAD -->
                    <div class="card mt-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0">CONTROLES DE CALIDAD</h6>
                        </div>
                        <div class="card-body p-2">
                            <!-- Blanco del método -->
                            <div class="border p-2 mb-2">
                                <h6 class="mb-2 text-center bg-secondary text-white py-1">Blanco del método</h6>
                                <div class="row g-1">
                                    <div class="col-2">
                                        <label class="small">Identificación</label>
                                        <input type="text" id="identificacion_bm"
                                            name="controles_analiticos[identificacion_bm]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_bm') }}">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">LCM</label>
                                        <input type="number" step="any" id="limite_cuantificacion_metodo"
                                            name="controles_analiticos[limite_cuantificacion_metodo]"
                                            class="form-control form-control-sm" oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor leído</label>
                                        <input type="number" step="any" id="valor_leido"
                                            name="controles_analiticos[valor_leido]" class="form-control form-control-sm"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Aceptable/no aceptable</label>
                                        <input type="text"id="aceptable_blanco"
                                            name="controles_analiticos[aceptable_blanco]"
                                            class="form-control form-control-sm" readonly>
                                    </div>

                                </div>
                            </div>

                            <!-- Muestra fortificada -->
                            <div class="border p-2 mb-2">
                                <h6 class="mb-2 text-center bg-secondary text-white py-1">Muestra fortificada porcentaje de
                                    Recuperación</h6>
                                <div class="row g-1 mb-2">
                                    <div class="col-3">
                                        <label class="small">Identificación de muestra</label>
                                        <input type="text" id="identificacion_mf"
                                            name="controles_analiticos[identificacion_mf]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_mf') }}">
                                    </div>
                                    <div class="col-9">

                                        <div class="row g-1">
                                            <div class="col-3">
                                                <label class="small">% COT fortificado</label>
                                                <input type="number" step="any" id="fortificado" name="fortificado"
                                                    class="form-control form-control-sm" oninput="calcularFortificada()">
                                            </div>
                                            <div class="col-3">
                                                <label class="small">% COT muestra</label>
                                                <input type="number" step="any" id="cot_muestra" name="cot_muestra"
                                                    value="{{ old('cot_muestra') }}" class="form-control form-control-sm"
                                                    oninput="calcularFortificada()">
                                            </div>
                                            <div class="col-3">
                                                <label class="small">Valor obtenido</label>
                                                <input type="number" step="any" id="valor_obtenido"
                                                    name="controles_analiticos[valor_obtenido]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old('controles_analiticos.valor_obtenido') }}">
                                            </div>
                                            <div class="col-3">
                                                <label class="small">%REC</label>
                                                <input type="text" id="recuperacion"
                                                    name="controles_analiticos[recuperacion]"
                                                    class="form-control form-control-sm" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-1">
                                    <div class="col-2">
                                        <label class="small">Aceptable/no aceptable</label>
                                        <input type="text"id="aceptable_fortificada"
                                            name="controles_analiticos[aceptable_fortificada]"
                                            class="form-control form-control-sm" readonly>
                                    </div>

                                </div>
                            </div>

                            <!-- %Error (Material de Referencia) -->
                            <div class="border p-2 mb-2">
                                <h6 class="mb-2 text-center bg-secondary text-white py-1">%Error</h6>
                                <div class="row g-1">
                                    <div class="col-2">
                                        <label class="small">Identificación de muestra</label>
                                        <input type="text" id="identificacion_mr"
                                            name="controles_analiticos[identificacion_mr]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_mr') }}">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor referencia</label>
                                        <input type="number" step="any" id="valor_referencia"
                                            name="controles_analiticos[valor_referencia]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_referencia') }}"
                                            oninput="calcularError()">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor leído</label>
                                        <input type="number" step="any" id="valor_cot_leido" name="valor_cot_leido"
                                            value="{{ old('valor_leido') }}" class="form-control form-control-sm"
                                            oninput="calcularError()">
                                    </div>
                                    <div class="col-1">
                                        <label class="small">%ERROR</label>
                                        <input type="text" id="error_analitico" name="error_analitico"
                                            value="{{ old('error_analitico') }}" class="form-control form-control-sm"
                                            readonly>
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Aceptable/no aceptable</label>
                                        <input type="text"id="aceptable_referencia"
                                            name="controles_analiticos[aceptable_referencia]"
                                            class="form-control form-control-sm" readonly>
                                    </div>

                                </div>
                            </div>

                            <!-- Diferencia porcentual Relativa (DPR) -->
                            <div class="border p-2 mb-2">
                                <h6 class="mb-2 text-center bg-secondary text-white py-1">Diferencia porcentual Relativa
                                    (DPR)</h6>
                                <div class="row g-1">
                                    <div class="col-2">
                                        <label class="small">Identificación de muestra</label>
                                        <input type="text" id="identificacion_dm"
                                            name="controles_analiticos[identificacion_dm]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_dm') }}">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor Leído M1</label>
                                        <input type="number" step="any" id="replica_1"
                                            name="controles_analiticos[replica_1]" class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.replica_1') }}">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor Leído M2</label>
                                        <input type="number" step="any" id="replica_2"
                                            name="controles_analiticos[replica_2]" class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.replica_2') }}">
                                    </div>
                                    <div class="col-1">
                                        <label class="small">%DPR</label>
                                        <input type="text" id="dpr" name="controles_analiticos[dpr]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_duplicado"
                                            name="controles_analiticos[aceptable_duplicado]"
                                            class="form-control form-control-sm" readonly>
                                    </div>

                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="small">Observaciones Generales</label>
                                <textarea name="analytical_controls[general_observations]" rows="2" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- REGISTRO DE MUESTRAS -->
                    <div class="card mt-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0">Información de Resultados</h6>
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-bordered text-center" id="tablaMuestras">
                                <thead>
                                    <tr>
                                        <th>Código interno</th>
                                        <th>Peso de muestra (g)</th>
                                        <th>% Humedad (pW)</th>
                                        <th>Vol. dicromato potasio 0,17 M (mL)</th>
                                        <th>Vol. Sulfato ferroso 0,5 M Blanco (mL)</th>
                                        <th>Vol. sulfato ferroso 0,5 M muestra (mL)</th>
                                        <th>Molaridad sulfato ferroso</th>
                                        <th>%CO ox. total (g/100g)</th>
                                        <th>%COT (g/100g)</th>
                                        <th>%MO (g/100g)</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" id="codigo_interno" name="codigo_interno"
                                                class="form-control" required></td>

                                        <td><input type="number" step="0.0001" id="peso_muestra" name="peso_muestra"
                                                class="form-control peso-muestra" required></td>

                                        <td><input type="number" step="0.01" id="porcentaje_humedad"
                                                name="porcentaje_humedad" class="form-control humedad"></td>

                                        <td><input type="number" step="0.01" name="volumen_dicromato"
                                                id="volumen_dicromato" class="form-control dicromato" required></td>

                                        <td><input type="number" step="0.01" id="volumen_sulfato_blanco"
                                                name="volumen_sulfato_blanco" class="form-control sulfato-blanco"
                                                required></td>

                                        <td><input type="number" step="0.01" id="volumen_sulfato_muestra"
                                                name="volumen_sulfato_muestra" class="form-control sulfato-muestra"
                                                required></td>

                                        <td><input type="text" ide="molaridad_sulfato" name="molaridad_sulfato"
                                                class="form-control molaridad" readonly></td>

                                        <td><input type="text" name="porcentaje_co_total" id="porcentaje_co_total"
                                                class="form-control co-total" readonly></td>

                                        <td><input type="text" name="porcentaje_cot" id="porcentaje_cot"
                                                class="form-control cot" readonly></td>
                                        <td><input type="text" name="porcentaje_mo" id="porcentaje_mo"
                                                class="form-control mo" readonly></td>
                                        <td><input type="text" name="observaciones" class="form-control">
                                        </td>
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
                            <button type="submit" class="btn btn-primary">Guardar Análisis de Carbono</button>
                            <a href="" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection


<script>
    // ===== FUNCIONES PARA CONTROLES DE CALIDAD =====

    // Función para evaluar aceptabilidad del blanco
    function evaluarAceptabilidadBlanco() {
        const lcm = parseFloat(document.getElementById('limite_cuantificacion_metodo').value) || 0;
        const valorLeido = parseFloat(document.getElementById('valor_leido').value) || 0;
        const aceptableInput = document.getElementById('aceptable_blanco');

        if (lcm > 0 && valorLeido >= 0) {
            // Criterio: valor leído debe ser menor que LCM
            const esAceptable = valorLeido < lcm;
            aceptableInput.value = esAceptable ? 'Aceptable' : 'No Aceptable';
            aceptableInput.style.color = esAceptable ? 'green' : 'red';
            aceptableInput.style.fontWeight = 'bold';
        } else {
            aceptableInput.value = '';
            aceptableInput.style.color = '';
        }
    }

    // Función para calcular muestra fortificada
    function calcularFortificada() {
        const fortificado = parseFloat(document.getElementById('fortificado').value) || 0;
        const cotMuestra = parseFloat(document.getElementById('cot_muestra').value) || 0;
        const valorObtenido = parseFloat(document.getElementById('valor_obtenido').value) || 0;
        const recuperacionInput = document.getElementById('recuperacion');
        const aceptableInput = document.getElementById('aceptable_fortificada');

        if (fortificado > 0 && valorObtenido >= 0 && cotMuestra >= 0) {
            // Fórmula: %REC = ((Valor obtenido - COT muestra) / Fortificado) * 100
            const recuperacion = ((valorObtenido - cotMuestra) / fortificado) * 100;
            recuperacionInput.value = recuperacion.toFixed(2);

            // Criterio de aceptabilidad: 70% ≤ recuperación ≤ 130%
            // Si es exactamente 130 es aceptable, si pasa de 130 (ej: 131) es no aceptable
            const esAceptable = recuperacion >= 70 && recuperacion <= 130;
            aceptableInput.value = esAceptable ? 'Aceptable' : 'No Aceptable';
            aceptableInput.style.color = esAceptable ? 'green' : 'red';
            aceptableInput.style.fontWeight = 'bold';
        } else {
            recuperacionInput.value = '';
            aceptableInput.value = '';
            aceptableInput.style.color = '';
        }
    }

    // Función para calcular error analítico
    function calcularError() {
        const valorReferencia = parseFloat(document.getElementById('valor_referencia').value) || 0;
        const valorLeido = parseFloat(document.getElementById('valor_cot_leido').value) || 0;
        const errorInput = document.getElementById('error_analitico');
        const aceptableInput = document.getElementById('aceptable_referencia');

        if (valorReferencia > 0 && valorLeido > 0) {
            // Fórmula: %Error = ((Valor leído - Valor referencia) / Valor referencia) * 100
            const error = ((valorLeido - valorReferencia) / valorReferencia) * 100;
            // Mostrar solo el valor absoluto (sin signo negativo)
            errorInput.value = Math.abs(error).toFixed(2);

            // Criterio de aceptabilidad: |ERROR| ≤ 20%
            const esAceptable = Math.abs(error) <= 20;
            aceptableInput.value = esAceptable ? 'Aceptable' : 'No Aceptable';
            aceptableInput.style.color = esAceptable ? 'green' : 'red';
            aceptableInput.style.fontWeight = 'bold';
        } else {
            errorInput.value = '';
            aceptableInput.value = '';
            aceptableInput.style.color = '';
        }
    }

    // Función para calcular DPR
    function calcularDPR() {
        const replica1 = parseFloat(document.getElementById('replica_1').value) || 0;
        const replica2 = parseFloat(document.getElementById('replica_2').value) || 0;
        const dprInput = document.getElementById('dpr');
        const aceptableInput = document.getElementById('aceptable_duplicado');

        if (replica1 > 0 && replica2 > 0) {
            // Fórmula: DPR = (|R1 - R2| / ((R1 + R2) / 2)) * 100
            const promedio = (replica1 + replica2) / 2;
            const diferencia = Math.abs(replica1 - replica2);
            const dpr = (diferencia / promedio) * 100;

            dprInput.value = dpr.toFixed(2);

            // Criterio de aceptabilidad: DPR < 25%
            const esAceptable = dpr < 25;
            aceptableInput.value = esAceptable ? 'Aceptable' : 'No Aceptable';
            aceptableInput.style.color = esAceptable ? 'green' : 'red';
            aceptableInput.style.fontWeight = 'bold';
        } else {
            dprInput.value = '';
            aceptableInput.value = '';
            aceptableInput.style.color = '';
        }
    }

    // ===== FUNCIONES PARA CÁLCULOS DE MUESTRAS =====

      function calcularValoresMuestra(fila) {
        const pesoMuestra = parseFloat(fila.querySelector('.peso-muestra').value) || 0;
        const humedad = parseFloat(fila.querySelector('.humedad').value) || 0;
        const sulfatoBlanco = parseFloat(fila.querySelector('.sulfato-blanco').value) || 0;
        const sulfatoMuestra = parseFloat(fila.querySelector('.sulfato-muestra').value) || 0;
        const dicromato = parseFloat(fila.querySelector('.dicromato').value) || 0;

        // Constantes del método Walkley-Black modificado
        const MOLARIDAD_DICROMATO = 0.17; 
        const PESO_ATOMICO_C = 12.01; // g/mol
        const FACTOR_MO = 1.724; // Factor para materia orgánica 
        

        if (pesoMuestra > 0 && dicromato > 0 && sulfatoBlanco > 0) {
            // 1. Calcular molaridad del sulfato ferroso
            //  Molaridad = Vol_dicromato / Vol_sulfato_blanco
            const molaridad = dicromato / sulfatoBlanco;
            fila.querySelector('.molaridad').value = molaridad.toFixed(2);

             // 2. Calcular meq de C oxidado
            // meq C = (Vol_blanco - Vol_muestra) * Molaridad_sulfato
            const meqCarbono = (sulfatoBlanco - sulfatoMuestra) * molaridad;

          // 3. Calcular %CO oxidable total
            // Fórmula Excel: %CO ox. total = [(Vol_blanco - Vol_muestra) × Molaridad × 0,003 × (100 + %Humedad)] / Peso_muestra
            const factorHumedad = 100 + humedad; // Si humedad es 0, factor = 100
            coTotal = ((sulfatoBlanco - sulfatoMuestra) * molaridad * 0.003 * factorHumedad) / pesoMuestra;
            fila.querySelector('.co-total').value = coTotal.toFixed(2);
 
            
            // 4. Calcular %COT 
            // Fórmula: %COT = %CO ox. total × 1.34
            const cot = coTotal * 1.34;
            fila.querySelector('.cot').value = cot.toFixed(2);

            // 5. Calcular %MO (Materia Orgánica)
            // %MO = %COT * Factor Van Bemmelen (1.724)
            const mo = cot * FACTOR_MO;
            fila.querySelector('.mo').value = mo.toFixed(2);

            // Agregar estilo visual para indicar que se calculó
            ['.molaridad', '.co-total', '.cot', '.mo'].forEach(selector => {
                const input = fila.querySelector(selector);
                if (input) {
                    input.style.backgroundColor = '#e8f5e8';
                    input.style.fontWeight = 'bold';
                }
            });
        } else {
            // Limpiar campos si no hay datos suficientes
            ['.molaridad', '.co-total', '.cot', '.mo'].forEach(selector => {
                const input = fila.querySelector(selector);
                if (input) {
                    input.value = '';
                    input.style.backgroundColor = '';
                    input.style.fontWeight = '';
                }
            });
        }
    }

    // ===== FUNCIONES PARA MANEJO DE FILAS =====

    // Función para agregar una nueva fila
    function agregarFila() {
        const tabla = document.getElementById('tablaMuestras').getElementsByTagName('tbody')[0];
        const filaOriginal = tabla.rows[0];
        const nuevaFila = filaOriginal.cloneNode(true);

        // Limpiar los valores de la nueva fila
        const inputs = nuevaFila.querySelectorAll('input');
        inputs.forEach(input => {
            if (!input.readOnly) {
                input.value = '';
            } else {
                // Limpiar campos calculados
                input.value = '';
                input.style.backgroundColor = '';
                input.style.fontWeight = '';
            }
        });

        // Actualizar nombres de campos para arrays
        const filaIndex = tabla.rows.length;
        inputs.forEach(input => {
            if (input.name && !input.name.includes('[')) {
                const baseName = input.name;
                input.name = `muestras[${filaIndex}][${baseName}]`;
                input.id = input.id ? `${input.id}_${filaIndex}` : '';
            }
        });

        // Añadir eventos de cálculo
        agregarEventosCalculoFila(nuevaFila);

        tabla.appendChild(nuevaFila);

        // Enfocar el primer input de la nueva fila
        const primerInput = nuevaFila.querySelector('input:not([readonly])');
        if (primerInput) primerInput.focus();
    }

    // Función para quitar la última fila
    function quitarFila() {
        const tabla = document.getElementById('tablaMuestras').getElementsByTagName('tbody')[0];
        if (tabla.rows.length > 1) {
            tabla.deleteRow(-1);
        } else {
            alert('No se puede eliminar la única fila restante.');
        }
    }

    // Función para agregar eventos a una fila
    function agregarEventosCalculoFila(fila) {
        const inputsCalculables = fila.querySelectorAll(
            '.peso-muestra, .humedad, .sulfato-blanco, .sulfato-muestra, .dicromato'
        );

        inputsCalculables.forEach(input => {
            input.addEventListener('input', function() {
                calcularValoresMuestra(fila);
            });

            // Agregar validación en tiempo real
            input.addEventListener('blur', function() {
                validarRangoInput(this);
            });
        });
    }

    // ===== FUNCIONES DE VALIDACIÓN =====

    // Función para validar rangos de inputs
    function validarRangoInput(input) {
        const value = parseFloat(input.value);
        let isValid = true;
        let message = '';

        switch (input.className.split(' ')[1]) { // Obtener la segunda clase
            case 'peso-muestra':
                if (value <= 0 || value > 10) {
                    isValid = false;
                    message = 'El peso debe estar entre 0.1 y 10 g';
                }
                break;
            case 'humedad':
                if (value < 0 || value >= 100) {
                    isValid = false;
                    message = 'La humedad debe estar entre 0 y 99%';
                }
                break;
            case 'sulfato-blanco':
            case 'sulfato-muestra':
                if (value <= 0 || value > 50) {
                    isValid = false;
                    message = 'El volumen debe estar entre 0.1 y 50 mL';
                }
                break;
            case 'dicromato':
                if (value <= 0 || value > 20) {
                    isValid = false;
                    message = 'El volumen debe estar entre 0.1 y 20 mL';
                }
                break;
        }

        if (!isValid && input.value) {
            input.style.borderColor = 'red';
            input.title = message;
        } else {
            input.style.borderColor = '';
            input.title = '';
        }
    }

    // Función para validar formulario antes del envío
    function validarFormulario(event) {
        const camposRequeridos = [{
                id: 'consecutivo_no',
                nombre: 'Consecutivo No.'
            },
            {
                id: 'fecha_analisis',
                nombre: 'Fecha del Análisis'
            },
            {
                id: 'equipo_utilizado',
                nombre: 'Equipo utilizado'
            },
            {
                id: 'codigo_interno',
                nombre: 'Código interno'
            },
            {
                id: 'peso_muestra',
                nombre: 'Peso de muestra'
            },
            {
                id: 'volumen_sulfato_blanco',
                nombre: 'Volumen sulfato blanco'
            },
            {
                id: 'volumen_sulfato_muestra',
                nombre: 'Volumen sulfato muestra'
            },
            {
                id: 'volumen_dicromato',
                nombre: 'Volumen dicromato'
            }
        ];

        let errores = [];

        // Validar campos requeridos
        camposRequeridos.forEach(campo => {
            const input = document.getElementById(campo.id);
            if (!input || !input.value.trim()) {
                errores.push(`• ${campo.nombre} es requerido`);
            }
        });

        // Validar que hay al menos una muestra con datos completos
        const tabla = document.getElementById('tablaMuestras');
        const filas = tabla.querySelectorAll('tbody tr');
        let hayMuestraValida = false;

        filas.forEach((fila, index) => {
            const codigo = fila.querySelector('.form-control[name*="codigo_interno"]')?.value;
            const peso = fila.querySelector('.peso-muestra')?.value;
            const sulfatoBlanco = fila.querySelector('.sulfato-blanco')?.value;
            const sulfatoMuestra = fila.querySelector('.sulfato-muestra')?.value;
            const dicromato = fila.querySelector('.dicromato')?.value;

            if (codigo && peso && sulfatoBlanco && sulfatoMuestra && dicromato) {
                hayMuestraValida = true;
            }
        });

        if (!hayMuestraValida) {
            errores.push('• Debe completar al menos una muestra con todos los datos requeridos');
        }

        // Mostrar errores si los hay
        if (errores.length > 0) {
            event.preventDefault();
            alert('Por favor corrija los siguientes errores:\n\n' + errores.join('\n'));
            return false;
        }

        return true;
    }

    // ===== INICIALIZACIÓN =====

    // Inicializar eventos cuando el DOM esté cargado
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Iniciando script de análisis de carbono orgánico');

        // Agregar eventos de cálculo automático a la primera fila
        const primeraFila = document.querySelector('#tablaMuestras tbody tr');
        if (primeraFila) {
            agregarEventosCalculoFila(primeraFila);
        }

        // Agregar eventos a los controles de calidad
        const eventosControles = [{
                ids: ['limite_cuantificacion_metodo', 'valor_leido'],
                funcion: evaluarAceptabilidadBlanco
            },
            {
                ids: ['fortificado', 'cot_muestra', 'valor_obtenido'],
                funcion: calcularFortificada
            },
            {
                ids: ['valor_referencia', 'valor_cot_leido'],
                funcion: calcularError
            },
            {
                ids: ['replica_1', 'replica_2'],
                funcion: calcularDPR
            }
        ];

        eventosControles.forEach(evento => {
            evento.ids.forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    elemento.addEventListener('input', evento.funcion);
                }
            });
        });

        // Establecer fecha actual si está vacía
        const fechaAnalisis = document.getElementById('fecha_analisis');
        if (fechaAnalisis && !fechaAnalisis.value) {
            const hoy = new Date().toISOString().split('T')[0];
            fechaAnalisis.value = hoy;
        }

        // Agregar validación al formulario
        const formulario = document.querySelector('form');
        if (formulario) {
            formulario.addEventListener('submit', validarFormulario);
        }

        // Agregar tooltips informativos
        agregarTooltips();

        console.log('Script inicializado correctamente');
    });

    // Función para agregar tooltips informativos
    function agregarTooltips() {
        const tooltips = {
            'limite_cuantificacion_metodo': 'Límite de cuantificación del método (LCM)',
            'fortificado': 'Cantidad de carbono orgánico añadida a la muestra (g/100g)',
            'valor_referencia': 'Valor certificado del material de referencia',
            'replica_1': 'Primer resultado del análisis por duplicado',
            'replica_2': 'Segundo resultado del análisis por duplicado',
            'peso_muestra': 'Peso exacto de la muestra seca (g)',
            'porcentaje_humedad': 'Contenido de humedad de la muestra (%)',
            'volumen_sulfato_blanco': 'Volumen de FeSO4 consumido en el blanco (mL)',
            'volumen_sulfato_muestra': 'Volumen de FeSO4 consumido en la muestra (mL)',
            'volumen_dicromato': 'Volumen de K2Cr2O7 usado en la normalización (mL)'
        };

        Object.entries(tooltips).forEach(([id, texto]) => {
            const elemento = document.getElementById(id);
            if (elemento && !elemento.title) {
                elemento.title = texto;
            }
        });
    }

    // ===== FUNCIONES AUXILIARES =====

    // Función para formatear números
    function formatearNumero(numero, decimales = 2) {
        if (isNaN(numero) || numero === null || numero === undefined) {
            return '';
        }
        return parseFloat(numero).toFixed(decimales);
    }

    // Función para limpiar todos los cálculos
    function limpiarCalculos() {
        if (confirm('¿Está seguro de que desea limpiar todos los cálculos?')) {
            // Limpiar controles de calidad
            ['recuperacion', 'error_analitico', 'dpr', 'aceptable_blanco', 'aceptable_fortificada',
                'aceptable_referencia', 'aceptable_duplicado'
            ].forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    elemento.value = '';
                    elemento.style.color = '';
                    elemento.style.fontWeight = '';
                }
            });

            // Limpiar tabla de muestras
            const tabla = document.getElementById('tablaMuestras');
            const filas = tabla.querySelectorAll('tbody tr');
            filas.forEach(fila => {
                ['.molaridad', '.co-total', '.cot', '.mo'].forEach(selector => {
                    const input = fila.querySelector(selector);
                    if (input) {
                        input.value = '';
                        input.style.backgroundColor = '';
                        input.style.fontWeight = '';
                    }
                });
            });
        }
    }
</script>
