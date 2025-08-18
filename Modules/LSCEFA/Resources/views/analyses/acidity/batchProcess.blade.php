@extends('lscefa::layouts.technical')

@section('title', 'Procesar Lote de Análisis de Acidez')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->

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
    @if (session('errors'))
    <div class="alert alert-danger alert-dismissible fade show">
        <h5>Errores detallados:</h5>
        <ul>
            @foreach (session('errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

    <!-- Main Content -->
    <form action="{{ route('lscefa.technical.analyses.acidity.batchStore') }}" method="POST" id="form-acidez-lote">
        @csrf
        
        <!-- Inputs hidden para cada proceso del lote -->
        @foreach($analyses as $index => $analysis)
            <input type="hidden" name="analyses[{{ $index }}][process_id]" value="{{ $analysis['process_id'] }}">
            <input type="hidden" name="analyses[{{ $index }}][service_id]" value="{{ $analysis['service_id'] }}">
        @endforeach

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
                    <div class="card-header text-center">
                        <h3 class="card-title mb-0">Información General del Lote ({{ count($analyses) }} análisis)</h3>
                    </div>
                    <div class="card-body">
                        <!-- Primera fila -->
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="proceso">Procesos Involucrados</label>
                                    <input type="text" class="form-control" id="proceso"
                                        value="{{ implode(', ', array_column($analyses, 'process_id')) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="servicio">Servicios Involucrados</label>
                                    <input type="text" class="form-control" id="servicio" value="Acidez" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="analista" class="form-label-fixed">Analista</label>
                                    <input type="text" class="form-control form-control-sm" id="analista"
                                        name="analista" value="{{ old('analista') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="consecutivo_no" class="form-label-fixed">Consecutivo No.</label>
                                    <input type="text" class="form-control form-control-sm" id="consecutivo_no"
                                        name="consecutivo_no" value="{{ old('consecutivo_no') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fecha_analisis" class="form-label-fixed">Fecha del Análisis</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha_analisis"
                                        name="fecha_analisis" value="{{ old('fecha_analisis', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="unidades_reporte_equipo" class="form-label-fixed">Unidades de reporte equipo</label>
                                    <input type="text" class="form-control form-control-sm"
                                        name="unidades_reporte_equipo" id="unidades_reporte_equipo"
                                        value="{{ old('unidades_reporte_equipo', 'g/100g') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Segunda fila -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nombre_metodo" class="form-label-fixed">Nombre del Método</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre_metodo"
                                        id="nombre_metodo" value="{{ old('nombre_metodo', 'NTC 5403:2021') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="equipo_utilizado" class="form-label-fixed">Equipo utilizado</label>
                                    <input type="text" class="form-control form-control-sm" name="equipo_utilizado"
                                        id="equipo_utilizado" value="{{ old('equipo_utilizado') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="intervalo_metodo" class="form-label-fixed">Intervalo del método</label>
                                    <input type="text" class="form-control form-control-sm" name="intervalo_metodo"
                                        id="intervalo_metodo" value="{{ old('intervalo_metodo', '0.1 - 15%') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="resolucion_instrumental" class="form-label-fixed">Resolución instrumental</label>
                                    <input type="text" class="form-control form-control-sm"
                                        name="resolucion_instrumental" id="resolucion_instrumental"
                                        value="{{ old('resolucion_instrumental') }}">
                                </div>
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

                        <!-- Muestra fortificada -->
                        <div class="border p-3 mb-3">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2">Muestra fortificada porcentaje de Recuperación</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Identificación de muestra</label>
                                        <input type="text" id="identificacion_mf"
                                            name="controles_analiticos[identificacion_mf]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_mf') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor referencia</label>
                                        <input type="number" step="any" id="valor_referencia"
                                            name="controles_analiticos[valor_referencia]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_referencia') }}"
                                            oninput="calcularError()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor obtenido</label>
                                        <input type="number" step="any" id="valor_obtenido"
                                            name="controles_analiticos[valor_obtenido]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_obtenido') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">%REC</label>
                                        <input type="text" id="controles_analiticos[recuperacion]"
                                            name="controles_analiticos[recuperacion]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_fortificada"
                                            name="controles_analiticos[aceptable_fortificada]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- %Error (Material de Referencia) -->
                        <div class="border p-3 mb-3">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2">%Error</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Identificación de muestra</label>
                                        <input type="text" id="identificacion_mr"
                                            name="controles_analiticos[identificacion_mr]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_mr') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor referencia</label>
                                        <input type="number" step="any" id="valor_referencia_error"
                                            name="controles_analiticos[valor_referencia_error]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_referencia_error') }}"
                                            oninput="calcularError()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor obtenido</label>
                                        <input type="number" step="any" id="valor_obtenido_error"
                                            name="controles_analiticos[valor_obtenido_error]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_obtenido_error') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">%ERROR</label>
                                        <input type="text" id="error_analitico" name="controles_analiticos[error_analitico]"
                                            value="{{ old('controles_analiticos.error_analitico') }}" class="form-control form-control-sm"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_referencia"
                                            name="controles_analiticos[aceptable_referencia]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Diferencia porcentual Relativa (DPR) -->
                        <div class="border p-3 mb-3">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2">Diferencia porcentual Relativa (DPR)</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Identificación de muestra</label>
                                        <input type="text" id="identificacion_dm"
                                            name="controles_analiticos[identificacion_dm]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_dm') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor Leído M1</label>
                                        <input type="number" step="any" id="replica_1"
                                            name="controles_analiticos[replica_1]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.replica_1') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor Leído M2</label>
                                        <input type="number" step="any" id="replica_2"
                                            name="controles_analiticos[replica_2]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.replica_2') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">%DPR</label>
                                        <input type="text" id="dpr" name="controles_analiticos[dpr]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_duplicado"
                                            name="controles_analiticos[aceptable_duplicado]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blanco del método -->
                        <div class="border p-3 mb-3">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2">Blanco del método</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Identificación de muestra</label>
                                        <input type="text" id="identificacion_bm"
                                            name="controles_analiticos[identificacion_bm]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_bm', 'BLANCO-METODO') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">LCM</label>
                                        <input type="number" step="any" id="limite_cuantificacion_metodo"
                                            name="controles_analiticos[limite_cuantificacion_metodo]"
                                            class="form-control form-control-sm"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Valor leído</label>
                                        <input type="number" step="any" id="controles_analiticos[valor_leido]"
                                            name="controles_analiticos[valor_leido]"
                                            class="form-control form-control-sm"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Rango del metodo</label>
                                        <input type="number" step="any" id="rango_metodo"
                                            name="controles_analiticos[rango_metodo]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.rango_metodo') }}"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label-fixed">Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_blanco"
                                            name="controles_analiticos[aceptable_blanco]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label-fixed">Observaciones Generales</label>
                            <textarea id="controles_analiticos[observaciones]" name="controles_analiticos[observaciones]" rows="2" class="form-control form-control-sm">{{ old('controles_analiticos.observaciones') }}</textarea>
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
                    <th>Vol NaOH consumido Blanco (mL)</th>
                    <th>% Humedad(g / 100g)</th>
                    <th>Molaridad NaOH</th>
                    <th>Vol NaOH consumido muestra (mL)</th>
                    <th>Acidez</th>
                </tr>
            </thead>
            <tbody id="muestras-body">
                <!-- Muestras especiales (fijas) -->
                <tr class="muestra-fila">
                    <td>
                        <input type="text" 
                               name="muestras_especiales[blanco_proceso][codigo_interno]" 
                               class="form-control"
                               value="BLANCO-PROCESO"
                               readonly>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_proceso][peso_muestra]"
                               class="form-control peso-muestra">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_proceso][consumido_blanco]"
                               class="form-control consumido-blanco">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_proceso][porcentaje_humedad]"
                               class="form-control humedad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_proceso][molaridad]"
                               class="form-control molaridad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_proceso][consumido_muestra]"
                               class="form-control consumido_muestra">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_proceso][acidez]"
                               class="form-control acidez" readonly>
                    </td>
                </tr>
                
                <tr class="muestra-fila">
                    <td>
                        <input type="text" 
                               name="muestras_especiales[blanco_metodo][codigo_interno]" 
                               class="form-control"
                               value="BLANCO-METODO"
                               readonly>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_metodo][peso_muestra]"
                               class="form-control peso-muestra">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_metodo][consumido_blanco]"
                               class="form-control consumido-blanco">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_metodo][porcentaje_humedad]"
                               class="form-control humedad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_metodo][molaridad]"
                               class="form-control molaridad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_metodo][consumido_muestra]"
                               class="form-control consumido_muestra">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[blanco_metodo][acidez]"
                               class="form-control acidez" readonly>
                    </td>
                </tr>
                
                <tr class="muestra-fila">
                    <td>
                        <input type="text" 
                               name="muestras_especiales[referencia_interlaboratorio][codigo_interno]" 
                               class="form-control"
                               value="REF-INTERLABORATORIO"
                               readonly>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[referencia_interlaboratorio][peso_muestra]"
                               class="form-control peso-muestra">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[referencia_interlaboratorio][consumido_blanco]"
                               class="form-control consumido-blanco">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[referencia_interlaboratorio][porcentaje_humedad]"
                               class="form-control humedad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[referencia_interlaboratorio][molaridad]"
                               class="form-control molaridad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[referencia_interlaboratorio][consumido_muestra]"
                               class="form-control consumido_muestra">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               name="muestras_especiales[referencia_interlaboratorio][acidez]"
                               class="form-control acidez" readonly>
                    </td>
                </tr>
                
                <!-- Muestras de análisis seleccionados -->
                @foreach($analyses as $index => $analysis)
                <tr class="muestra-fila">
                    <td>
                        <input type="text" 
                               id="analyses[{{ $index }}][codigo_interno]" 
                               name="analyses[{{ $index }}][codigo_interno]" 
                               class="form-control"
                               value="{{ $analysis['process_id'] }}"
                               required>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               id="analyses[{{ $index }}][peso_muestra]" 
                               name="analyses[{{ $index }}][peso_muestra]"
                               class="form-control peso-muestra" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               id="analyses[{{ $index }}][consumido_blanco]" 
                               name="analyses[{{ $index }}][consumido_blanco]"
                               class="form-control consumido-blanco" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               id="analyses[{{ $index }}][porcentaje_humedad]" 
                               name="analyses[{{ $index }}][porcentaje_humedad]"
                               class="form-control humedad">
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               id="analyses[{{ $index }}][molaridad]" 
                               name="analyses[{{ $index }}][molaridad]"
                               class="form-control molaridad" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               id="analyses[{{ $index }}][consumido_muestra]" 
                               name="analyses[{{ $index }}][consumido_muestra]"
                               class="form-control consumido_muestra" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" 
                               id="analyses[{{ $index }}][acidez]" 
                               name="analyses[{{ $index }}][acidez]"
                               class="form-control acidez" readonly>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

                <!-- BOTONES -->
                <div class="card">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Lote de Análisis</button>
                        <a href="{{ route('lscefa.technical.analyses.acidity.index') }}"
                            class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // FUNCIÓN PARA CALCULAR ACIDEZ SEGÚN FÓRMULA DEL EXCEL
    function calcularAcidez(fila) {
        const pesoMuestra = parseFloat(fila.querySelector('.peso-muestra').value) || 0;
        const consumidoBlanco = parseFloat(fila.querySelector('.consumido-blanco').value) || 0;
        const porcentajeHumedad = parseFloat(fila.querySelector('.humedad').value) || 0;
        const molaridad = parseFloat(fila.querySelector('.molaridad').value) || 0;
        const consumidoMuestra = parseFloat(fila.querySelector('.consumido_muestra').value) || 0;
        
        if (pesoMuestra > 0) {
            const acidez = ((consumidoMuestra - consumidoBlanco) * molaridad * (100 + porcentajeHumedad)) / pesoMuestra;
            fila.querySelector('.acidez').value = acidez.toFixed(4);
        } else {
            fila.querySelector('.acidez').value = '';
        }
    }
    
    // AGREGAR EVENTOS A TODAS LAS FILAS
    document.querySelectorAll('.muestra-fila').forEach(fila => {
        const inputs = fila.querySelectorAll('.peso-muestra, .consumido-blanco, .humedad, .molaridad, .consumido_muestra');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                calcularAcidez(fila);
            });
        });
    });

    // FUNCIONES PARA CONTROLES DE CALIDAD
    
    function calcularRecuperacion() {
        const valorReferencia = parseFloat(document.getElementById('valor_referencia').value) || 0;
        const valorObtenido = parseFloat(document.getElementById('valor_obtenido').value) || 0;
        
        if (valorReferencia > 0) {
            const recuperacion = (valorObtenido / valorReferencia) * 100;
            document.getElementById('controles_analiticos[recuperacion]').value = recuperacion.toFixed(2);
            
            const aceptable = (recuperacion >= 70 && recuperacion <= 130) ? 'Aceptable' : 'No Aceptable';
            document.getElementById('aceptable_fortificada').value = aceptable;
        }
    }
    
    function calcularError() {
        const valorReferencia = parseFloat(document.getElementById('valor_referencia_error')?.value) || 0;
        const valorObtenido = parseFloat(document.getElementById('valor_obtenido_error')?.value) || 0;
        
        if (valorReferencia > 0) {
            const diferencia = Math.abs(valorReferencia - valorObtenido);
            const error = (diferencia / valorReferencia) * 100;
            
            document.getElementById('error_analitico').value = error.toFixed(1);
            
            const aceptable = (error <= 20) ? 'Aceptable' : 'No Aceptable';
            document.getElementById('aceptable_referencia').value = aceptable;
        } else {
            document.getElementById('error_analitico').value = '';
            document.getElementById('aceptable_referencia').value = '';
        }
    }
    
    function calcularDPR() {
        const replica1 = parseFloat(document.getElementById('replica_1').value) || 0;
        const replica2 = parseFloat(document.getElementById('replica_2').value) || 0;
        
        if (replica1 > 0 && replica2 > 0) {
            const diferencia = Math.abs(replica2 - replica1);
            const promedio = (replica2 + replica1) / 2;
            const dpr = (diferencia / promedio) * 100;
            
            document.getElementById('dpr').value = dpr.toFixed(1);
            
            const aceptable = (dpr < 25) ? 'Aceptable' : 'No Aceptable';
            document.getElementById('aceptable_duplicado').value = aceptable;
        } else {
            document.getElementById('dpr').value = '';
            document.getElementById('aceptable_duplicado').value = '';
        }
    }
    
    function evaluarAceptabilidadBlanco() {
        const lcm = parseFloat(document.getElementById('limite_cuantificacion_metodo').value) || 0;
        const valorLeido = parseFloat(document.getElementById('controles_analiticos[valor_leido]').value) || 0;
        
        let aceptable = 'No Evaluado';
        
        if (lcm > 0) {
            aceptable = (valorLeido < lcm) ? 'Aceptable' : 'No Aceptable';
        }
        
        document.getElementById('aceptable_blanco').value = aceptable;
    }
    
    // EVENTOS PARA CONTROLES DE CALIDAD
    
    // Muestra fortificada
    const valorReferenciaFort = document.getElementById('valor_referencia');
    const valorObtenidoFort = document.getElementById('valor_obtenido');
    if (valorReferenciaFort) valorReferenciaFort.addEventListener('input', calcularRecuperacion);
    if (valorObtenidoFort) valorObtenidoFort.addEventListener('input', calcularRecuperacion);
    
    // %Error
    const valorReferenciaError = document.getElementById('valor_referencia_error');
    const valorObtenidoError = document.getElementById('valor_obtenido_error');
    if (valorReferenciaError) valorReferenciaError.addEventListener('input', calcularError);
    if (valorObtenidoError) valorObtenidoError.addEventListener('input', calcularError);
    
    // DPR
    const replica1 = document.getElementById('replica_1');
    const replica2 = document.getElementById('replica_2');
    if (replica1) replica1.addEventListener('input', calcularDPR);
    if (replica2) replica2.addEventListener('input', calcularDPR);
    
    // Blanco del método
    const lcmInput = document.getElementById('limite_cuantificacion_metodo');
    const valorLeidoInput = document.getElementById('controles_analiticos[valor_leido]');
    const rangoMetodoInput = document.getElementById('rango_metodo');
    if (lcmInput) lcmInput.addEventListener('input', evaluarAceptabilidadBlanco);
    if (valorLeidoInput) valorLeidoInput.addEventListener('input', evaluarAceptabilidadBlanco);
    if (rangoMetodoInput) rangoMetodoInput.addEventListener('input', evaluarAceptabilidadBlanco);
});
</script>
@endsection

<style>
    /* CSS para mejorar la alineación de los formularios */
    .form-label-fixed {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        min-height: 40px;
        color: #495057;
        font-size: 0.875rem;
        line-height: 1.2;
        display: flex;
        align-items: flex-end;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-control-sm {
        height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    .border {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem;
    }

    .bg-secondary {
        background-color: #6c757d !important;
        border-radius: 0.25rem;
    }

    #tablaMuestras th {
        vertical-align: middle;
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem 0.5rem;
    }

    #tablaMuestras td {
        vertical-align: middle;
        padding: 0.5rem;
    }

    #tablaMuestras input {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
</style>