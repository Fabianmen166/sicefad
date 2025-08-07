@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de Carbono Orgánico Total')

@section('contenido')
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Procesar Análisis de Carbono Orgánico Total</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="">Gestión de Carbono</a></li>
                            <li class="breadcrumb-item active">Procesar Análisis</li>
                        </ol>
                    </div>
                </div>
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
        <form method="POST"
            action="{{ route('lscefa.technical.analyses.carbon.store') }}">
            @csrf

            <input type="hidden" name="service_id" value="{{ $analysis->service_id }}">
            <input type="hidden" name="process_id" value="{{ $analysis->process_id }}">

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
                                        value="{{ $carbonAnalysis->service->descripcion ?? 'Carbono Orgánico Total' }}"
                                        readonly>
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
                                        id="unidades_reporte_equipo"
                                        value="{{ old('unidades_reporte_equipo', 'g/100g') }}">
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
                                        <input type="text"id="identificacion_bm"
                                            name="controles_analiticos[identificacion_bm]"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">LCM</label>
                                        <input type="number" step="any" id="lcm"
                                            name="controles_analiticos[lcm]" class="form-control form-control-sm"
                                            oninput="evaluarAceptabilidadBlanco()">
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
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-9">

                                        <div class="row g-1">
                                            <div class="col-3">
                                                <label class="small">% COT fortificado</label>
                                                <input type="number" step="any" id="fortificado"
                                                    name="fortificado" class="form-control form-control-sm"
                                                    oninput="calcularFortificada()">
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
                                                    class="form-control form-control-sm" oninput="calcularFortificada()">
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
                                            value="Material de Referencia Certificado">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor referencia</label>
                                        <input type="number" step="any" id="valor_referencia"
                                            name="controles_analiticos[valor_referencia]"
                                            class="form-control form-control-sm" value="5.0000"
                                            oninput="calcularError()">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor leído</label>
                                        <input type="number" step="any" id="valor_leido" name="valor_leido"
                                            value="{{ old('valor_leido') }}" class="form-control form-control-sm"
                                            value="5.000" oninput="calcularError()">
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
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor Leído M1</label>
                                        <input type="number" step="any" id="replica_1"
                                            name="controles_analiticos[replica_1]" class="form-control form-control-sm"
                                            oninput="calcularDPR()">
                                    </div>
                                    <div class="col-2">
                                        <label class="small">Valor Leído M2</label>
                                        <input type="number" step="any" id="replica_2"
                                            name="controles_analiticos[replica_2]" class="form-control form-control-sm"
                                            oninput="calcularDPR()">
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
                                        <th>Vol. Sulfato ferroso 0,5 M Blanco (mL)</th>
                                        <th>Vol. sulfato ferroso 0,5 M muestra (mL)</th>
                                        <th>Vol. dicromato potasio 0,17 M (mL)</th>
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
                                        <td><input type="number" step="0.01" id="volumen_sulfato_blanco"
                                                name="volumen_sulfato_blanco" class="form-control sulfato-blanco"
                                                required></td>

                                        <td><input type="number" step="0.01" id="volumen_sulfato_muestra"
                                                name="volumen_sulfato_muestra" class="form-control sulfato-muestra"
                                                required></td>

                                        <td><input type="number" step="0.01" name="volumen_dicromato"
                                                id="volumen_dicromato" class="form-control dicromato" required></td>

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar cálculos con valores predeterminados
            calcularError();

            // Configurar normalización de decimales para todos los inputs numéricos
            setupDecimalNormalization();

            // Agregar event listeners para todos los inputs de cálculo
            setupEventListeners();
        });

        // Función auxiliar para convertir comas a puntos y parsear números
        function parseFloatWithComma(value) {
            if (!value) return 0;
            // Convertir a string y reemplazar comas por puntos
            const normalizedValue = value.toString().replace(',', '.');
            return parseFloat(normalizedValue) || 0;
        }

        // Función para normalizar valores decimales (cambiar comas por puntos)
        function normalizeDecimalValue(input) {
            let value = input.value;
            if (value.includes(',')) {
                // Reemplazar coma por punto
                value = value.replace(',', '.');
                input.value = value;
            }
            return value;
        }

        // Configurar normalización automática para inputs numéricos
        function setupDecimalNormalization() {
            // Seleccionar todos los inputs de tipo number y inputs numéricos específicos
            const numericInputs = document.querySelectorAll(`
        input[type="number"],
        input[step],
        .peso-muestra,
        .humedad,
        .sulfato-blanco,
        .sulfato-muestra,
        .dicromato,
        .molaridad,
        .co-total,
        .cot,
        .mo,
        #lcm,
        #valor_leido,
        #fortificado,
        #cot_muestra,
        #valor_obtenido,
        #valor_referencia,
        #replica_1,
        #replica_2
    `);

            numericInputs.forEach(input => {
                // Normalizar al perder el foco (blur)
                input.addEventListener('blur', function() {
                    normalizeDecimalValue(this);
                });

                // También normalizar antes de hacer cálculos (input event)
                input.addEventListener('input', function() {
                    // Pequeño delay para permitir que el usuario termine de escribir
                    setTimeout(() => {
                        if (this.value.includes(',')) {
                            normalizeDecimalValue(this);
                        }
                    }, 100);
                });
            });
        }

        function setupEventListeners() {
            // Event listeners para controles de calidad
            const blancoInputs = ['lcm', 'valor_leido'];
            blancoInputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', evaluarAceptabilidadBlanco);
                }
            });

            const fortificadaInputs = ['fortificado', 'cot_muestra', 'valor_obtenido'];
            fortificadaInputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', calcularFortificada);
                }
            });

            const errorInputs = ['valor_referencia', 'valor_leido'];
            errorInputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', calcularError);
                }
            });

            const dprInputs = ['replica_1', 'replica_2'];
            dprInputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', calcularDPR);
                }
            });

            // Event listener para cálculos de muestras - usar los IDs correctos del HTML
            const sampleInputs = [
                'peso_muestra', 'porcentaje_humedad', 'volumen_sulfato_blanco',
                'volumen_sulfato_muestra', 'volumen_dicromato'
            ];

            sampleInputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', function() {
                        calcularResultadosCarbono(this.closest('tr'));
                    });
                }
            });

            // Event listener global para filas dinámicas
            document.addEventListener('input', function(e) {
                if (e.target.closest('tr') &&
                    (e.target.classList.contains('peso-muestra') ||
                        e.target.classList.contains('humedad') ||
                        e.target.classList.contains('sulfato-blanco') ||
                        e.target.classList.contains('sulfato-muestra') ||
                        e.target.classList.contains('dicromato'))) {
                    calcularResultadosCarbono(e.target.closest('tr'));
                }
            });
        }

        // Función para evaluar blanco del método
        function evaluarAceptabilidadBlanco() {
            const valorLeido = parseFloatWithComma(document.getElementById('valor_leido')?.value) || 0;
            const lcm = parseFloatWithComma(document.getElementById('lcm')?.value) || 0;
            const aceptableField = document.getElementById('aceptable_blanco');

            if (!aceptableField) return;

            if (valorLeido && lcm) {
                if (valorLeido < lcm) {
                    aceptableField.value = "Aceptable";
                    aceptableField.style.backgroundColor = "#d4edda";
                    aceptableField.style.color = "#155724";
                } else {
                    aceptableField.value = "No aceptable";
                    aceptableField.style.backgroundColor = "#f8d7da";
                    aceptableField.style.color = "#721c24";
                }
            } else {
                aceptableField.value = "";
                aceptableField.style.backgroundColor = "";
                aceptableField.style.color = "";
            }
        }

        // Función para calcular muestra fortificada
        function calcularFortificada() {
            const cotFortificado = parseFloatWithComma(document.getElementById('fortificado')?.value) || 0;
            const cotMuestra = parseFloatWithComma(document.getElementById('cot_muestra')?.value) || 0;
            const valorObtenido = parseFloatWithComma(document.getElementById('valor_obtenido')?.value) || 0;

            const recField = document.getElementById('recuperacion');
            const aceptableField = document.getElementById('aceptable_fortificada');

            if (!recField || !aceptableField) return;

            if (cotFortificado && cotMuestra && valorObtenido) {
                // Fórmula de recuperación: ((Valor obtenido - COT muestra) / COT fortificado) * 100
                const recuperacion = Math.abs(valorObtenido - cotMuestra) / cotFortificado * 100;

                recField.value = recuperacion.toFixed(1);

                // Criterio de aceptabilidad: 70% - 130%
                if (recuperacion >= 70 && recuperacion <= 130) {
                    aceptableField.value = "Aceptable";
                    aceptableField.style.backgroundColor = "#d4edda";
                    aceptableField.style.color = "#155724";
                } else {
                    aceptableField.value = "No aceptable";
                    aceptableField.style.backgroundColor = "#f8d7da";
                    aceptableField.style.color = "#721c24";
                }
            } else {
                recField.value = "";
                aceptableField.value = "";
                aceptableField.style.backgroundColor = "";
                aceptableField.style.color = "";
            }
        }

        // Función para calcular %Error (Material de Referencia)
        function calcularError() {
            const valorReferencia = parseFloatWithComma(document.getElementById('valor_referencia')?.value) || 5.0000;
            const valorLeido = parseFloatWithComma(document.getElementById('valor_leido')?.value) || 0;

            const errorField = document.getElementById('error_analitico');
            const aceptableField = document.getElementById('aceptable_referencia');

            if (!errorField || !aceptableField) return;

            if (valorReferencia && valorLeido) {
                // Fórmula de error: |Valor leído - Valor referencia| / Valor referencia * 100
                const error = Math.abs(valorLeido - valorReferencia) / valorReferencia * 100;
                errorField.value = error.toFixed(1);

                // Criterio de aceptabilidad: ≤ 20%
                if (error <= 20) {
                    aceptableField.value = "Aceptable";
                    aceptableField.style.backgroundColor = "#d4edda";
                    aceptableField.style.color = "#155724";
                } else {
                    aceptableField.value = "No aceptable";
                    aceptableField.style.backgroundColor = "#f8d7da";
                    aceptableField.style.color = "#721c24";
                }
            } else {
                errorField.value = "";
                aceptableField.value = "";
                aceptableField.style.backgroundColor = "";
                aceptableField.style.color = "";
            }
        }

        // Función para calcular DPR (Diferencia Porcentual Relativa)
        function calcularDPR() {
            const valorM1 = parseFloatWithComma(document.getElementById('replica_1')?.value) || 0;
            const valorM2 = parseFloatWithComma(document.getElementById('replica_2')?.value) || 0;

            const dprField = document.getElementById('dpr');
            const aceptableField = document.getElementById('aceptable_duplicado');

            if (!dprField || !aceptableField) return;

            if (valorM1 && valorM2) {
                const promedio = (valorM1 + valorM2) / 2;
                // Fórmula DPR: |M1 - M2| / Promedio * 100
                const dpr = (Math.abs(valorM1 - valorM2) / promedio) * 100;

                dprField.value = dpr.toFixed(2);

                // Criterio de aceptabilidad: ≤ 25%
                if (dpr <= 25) {
                    aceptableField.value = "Aceptable";
                    aceptableField.style.backgroundColor = "#d4edda";
                    aceptableField.style.color = "#155724";
                } else {
                    aceptableField.value = "No aceptable";
                    aceptableField.style.backgroundColor = "#f8d7da";
                    aceptableField.style.color = "#721c24";
                }
            } else {
                dprField.value = "";
                aceptableField.value = "";
                aceptableField.style.backgroundColor = "";
                aceptableField.style.color = "";
            }
        }

        // Función para calcular resultados de carbono en muestras
        function calcularResultadosCarbono(row) {
            // Buscar los inputs correctos basados en el HTML proporcionado
            const pesoMuestraInput = row.querySelector('#peso_muestra') || row.querySelector('.peso-muestra');
            const humedadInput = row.querySelector('#porcentaje_humedad') || row.querySelector('.humedad');
            const sulfatoBlancoInput = row.querySelector('#volumen_sulfato_blanco') || row.querySelector('.sulfato-blanco');
            const sulfatoMuestraInput = row.querySelector('#volumen_sulfato_muestra') || row.querySelector(
                '.sulfato-muestra');
            const dicromatoInput = row.querySelector('#volumen_dicromato') || row.querySelector('.dicromato');
            const molaridadInput = row.querySelector('#molaridad_sulfato') || row.querySelector('.molaridad');
            const coTotalInput = row.querySelector('#porcentaje_cot_leido') || row.querySelector('.co-total');
            const cotInput = row.querySelector('#porcentaje_cot') || row.querySelector('.cot');
            const moInput = row.querySelector('#porcentaje_mo') || row.querySelector('.mo');

            const pesoMuestra = parseFloatWithComma(pesoMuestraInput?.value) || 0;
            const humedad = parseFloatWithComma(humedadInput?.value) || 0;
            const sulfatoBlanco = parseFloatWithComma(sulfatoBlancoInput?.value) || 0;
            const sulfatoMuestra = parseFloatWithComma(sulfatoMuestraInput?.value) || 0;
            const dicromato = parseFloatWithComma(dicromatoInput?.value) || 0;

            if (pesoMuestra > 0 && sulfatoBlanco > 0 && sulfatoMuestra >= 0 && dicromato > 0) {
                // Calcular molaridad del sulfato ferroso
                // Molaridad = (Vol. sulfato blanco - Vol. sulfato muestra) * 0.17 / Vol. sulfato blanco
                const molaridad = (sulfatoBlanco - sulfatoMuestra) * 0.17 / sulfatoBlanco;
                if (molaridadInput) molaridadInput.value = molaridad.toFixed(4);

                // Calcular %CO oxidable total
                // %CO = ((Vol. sulfato blanco - Vol. sulfato muestra) * 0.17 * 0.003 * 100) / peso muestra
                const coTotal = ((sulfatoBlanco - sulfatoMuestra) * 0.17 * 0.003 * 100) / pesoMuestra;
                if (coTotalInput) coTotalInput.value = coTotal.toFixed(2);

                // Calcular %COT (corregido por humedad si se proporciona)
                let cot = coTotal;
                if (humedad > 0 && humedad < 100) {
                    // Corrección por humedad: COT = CO total * (100 / (100 - % humedad))
                    cot = coTotal * (100 / (100 - humedad));
                }
                if (cotInput) cotInput.value = cot.toFixed(2);

                // Calcular %MO (materia orgánica = COT * 1.724)
                const mo = cot * 1.724;
                if (moInput) moInput.value = mo.toFixed(2);
            } else {
                // Limpiar campos si no hay datos suficientes
                if (molaridadInput) molaridadInput.value = '';
                if (coTotalInput) coTotalInput.value = '';
                if (cotInput) cotInput.value = '';
                if (moInput) moInput.value = '';
            }
        }

        // Función para agregar nueva fila a la tabla de muestras
        function agregarFila() {
            const table = document.getElementById("tablaMuestras")?.getElementsByTagName('tbody')[0];
            if (!table || table.rows.length === 0) return;

            const newRow = table.rows[0].cloneNode(true);
            const rowIndex = table.rows.length;

            // Limpiar valores de la nueva fila y actualizar nombres/IDs
            newRow.querySelectorAll('input').forEach((input, index) => {
                input.value = '';

                // Actualizar IDs para evitar duplicados
                const originalId = input.getAttribute('id');
                if (originalId) {
                    input.setAttribute('id', `${originalId}_${rowIndex}`);
                }

                // Actualizar nombres si es necesario para arrays
                const name = input.getAttribute('name');
                if (name && name.includes('[')) {
                    // Si ya tiene formato de array, actualizar el índice
                    const newName = name.replace(/\[\d*\]/, `[${rowIndex}]`);
                    input.setAttribute('name', newName);
                } else if (name) {
                    // Si no tiene formato de array, convertir a array
                    input.setAttribute('name', `${name}[${rowIndex}]`);
                }

                // Agregar event listeners para normalización decimal en la nueva fila
                if (input.type === 'number' || input.hasAttribute('step') ||
                    input.classList.contains('peso-muestra') ||
                    input.classList.contains('humedad') ||
                    input.classList.contains('sulfato-blanco') ||
                    input.classList.contains('sulfato-muestra') ||
                    input.classList.contains('dicromato')) {

                    input.addEventListener('blur', function() {
                        normalizeDecimalValue(this);
                    });

                    input.addEventListener('input', function() {
                        setTimeout(() => {
                            if (this.value.includes(',')) {
                                normalizeDecimalValue(this);
                            }
                        }, 100);
                    });
                }
            });

            table.appendChild(newRow);

            // Mostrar mensaje de confirmación
            showMessage('Nueva fila agregada correctamente', 'success');
        }

        // Función para quitar última fila de la tabla de muestras
        function quitarFila() {
            const table = document.getElementById("tablaMuestras")?.getElementsByTagName('tbody')[0];
            if (!table) return;

            if (table.rows.length > 1) {
                table.deleteRow(table.rows.length - 1);
                showMessage('Fila eliminada correctamente', 'info');
            } else {
                showMessage('No se puede eliminar la única fila restante', 'warning');
            }
        }

        // Función auxiliar para mostrar mensajes
        function showMessage(message, type = 'info') {
            // Crear elemento de mensaje temporal
            const alertDiv = document.createElement('div');
            alertDiv.className =
                `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show`;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';

            alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

            document.body.appendChild(alertDiv);

            // Auto-eliminar después de 3 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }

        // Función para validar formulario antes del envío
        function validarFormulario() {
            const errores = [];

            // Validar campos obligatorios de información general
            const camposObligatorios = [{
                    id: 'fecha_analisis',
                    nombre: 'Fecha del Análisis'
                },
                {
                    id: 'consecutivo_no',
                    nombre: 'Consecutivo No.'
                },
                {
                    id: 'equipo_utilizado',
                    nombre: 'Equipo utilizado'
                }
            ];

            camposObligatorios.forEach(campo => {
                const elemento = document.getElementById(campo.id);
                if (elemento && !elemento.value.trim()) {
                    errores.push(`${campo.nombre} es obligatorio`);
                }
            });

            // Validar que haya al menos una muestra con datos usando los nombres reales del HTML
            const table = document.getElementById("tablaMuestras")?.getElementsByTagName('tbody')[0];
            if (table) {
                let hayMuestrasValidas = false;

                for (let i = 0; i < table.rows.length; i++) {
                    const row = table.rows[i];

                    // Buscar por ID específico del HTML
                    const codigoInterno = row.querySelector('#codigo_interno')?.value?.trim() ||
                        row.querySelector('input[name*="codigo_interno"]')?.value?.trim();

                    const pesoMuestra = row.querySelector('#peso_muestra')?.value?.trim() ||
                        row.querySelector('input[name*="peso_muestra"]')?.value?.trim();

                    console.log(`Fila ${i}: Código="${codigoInterno}", Peso="${pesoMuestra}"`); // Para debug

                    if (codigoInterno && pesoMuestra && parseFloatWithComma(pesoMuestra) > 0) {
                        hayMuestrasValidas = true;
                        break;
                    }
                }

                if (!hayMuestrasValidas) {
                    errores.push('Debe ingresar al menos una muestra con código interno y peso válido');
                }
            }

            if (errores.length > 0) {
                console.log('Errores de validación:', errores); // Para debug
                alert('Por favor corrija los siguientes errores:\n\n' + errores.join('\n'));
                return false;
            }

            return true;
        }

        // Agregar validación al formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validarFormulario()) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
