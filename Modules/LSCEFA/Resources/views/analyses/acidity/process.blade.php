@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de Carbono Orgánico Total')

@section('content')
<div class="content-wrapper p-0 m-0" style="max-width: 100%;">
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

    <!-- Main Content -->
    <form action="{{ route('lscefa.technical.analyses.acidity.store') }}" method="POST" id="form-acidez" class="w-100">
        @csrf
        <input type="hidden" name="process_id" value="{{ $process->process_id }}">

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
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="card-title mb-0">Información General</h3>
                    </div>
                    <div class="card-body p-3">
                        <!-- Primera fila -->
                        <div class="row g-3">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="proceso">Procesos Involucrados</label>
                                    <input type="text" class="form-control form-control-sm" id="proceso"
                                        value="{{ $process->process_id }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="servicio">Servicios Involucrados</label>
                                    <input type="text" class="form-control form-control-sm" id="servicio" value="Acidez" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="analista">Analista</label>
                                    <input type="text" class="form-control form-control-sm" id="analista"
                                        value="{{ $user ? $user->nickname : '' }}" readonly>
                                    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="consecutivo_no">Consecutivo No.</label>
                                    <input type="text" class="form-control form-control-sm" id="consecutivo_no"
                                        name="consecutivo_no">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fecha_analisis">Fecha del Análisis</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha_analisis"
                                        name="fecha_analisis" value="{{ old('fecha_analisis') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="unidades_reporte_equipo">Unidades de reporte equipo</label>
                                    <input type="text" class="form-control form-control-sm"
                                        name="unidades_reporte_equipo" id="unidades_reporte_equipo"
                                        value="{{ old('unidades_reporte_equipo', 'g/100g') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Segunda fila -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nombre_metodo">Nombre del Método</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre_metodo"
                                        id="nombre_metodo" value="{{ old('nombre_metodo', 'NTC 5403:2021') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="equipo_utilizado">Equipo utilizado</label>
                                    <input type="text" class="form-control form-control-sm" name="equipo_utilizado"
                                        id="equipo_utilizado" value="{{ old('equipo_utilizado') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="intervalo_metodo">Intervalo del método</label>
                                    <input type="text" class="form-control form-control-sm" name="intervalo_metodo"
                                        id="intervalo_metodo" value="{{ old('intervalo_metodo', '0.1 - 15%') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="resolucion_instrumental">Resolución instrumental</label>
                                    <input type="text" class="form-control form-control-sm"
                                        name="resolucion_instrumental" id="resolucion_instrumental"
                                        value="{{ old('resolucion_instrumental') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLES DE CALIDAD -->
                <div class="card mt-3 border-0 shadow-none">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">CONTROLES DE CALIDAD</h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Muestra fortificada -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Muestra fortificada porcentaje de Recuperación</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Identificación de muestra</label>
                                        <input type="text" id="identificacion_mf"
                                            name="controles_analiticos[identificacion_mf]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_mf') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor referencia</label>
                                        <input type="number" step="any" id="valor_referencia"
                                            name="controles_analiticos[valor_referencia]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_referencia') }}"
                                            oninput="calcularError()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor obtenido</label>
                                        <input type="number" step="any" id="valor_obtenido"
                                            name="controles_analiticos[valor_obtenido]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.valor_obtenido') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>%REC</label>
                                        <input type="text" id="controles_analiticos[recuperacion]"
                                            name="controles_analiticos[recuperacion]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_fortificada"
                                            name="controles_analiticos[aceptable_fortificada]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- %Error (Material de Referencia) -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">%Error</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Identificación de muestra</label>
                                        <input type="text" id="identificacion_mr"
                                            name="controles_analiticos[identificacion_mr]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_mr') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor referencia</label>
                                        <input type="number" step="any" id="valor_referencia"
                                            name="valor_referencia" class="form-control form-control-sm"
                                            value="{{ old('valor_referencia') }}" oninput="calcularError()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor obtenido</label>
                                        <input type="number" step="any" id="valor_obtenido"
                                            name="valor_obtenido" class="form-control form-control-sm"
                                            value="{{ old('valor_obtenido') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>%ERROR</label>
                                        <input type="text" id="error_analitico" name="error_analitico"
                                            value="{{ old('error_analitico') }}" class="form-control form-control-sm"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_referencia"
                                            name="controles_analiticos[aceptable_referencia]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Diferencia porcentual Relativa (DPR) -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Diferencia porcentual Relativa (DPR)</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Identificación de muestra</label>
                                        <input type="text" id="identificacion_dm"
                                            name="controles_analiticos[identificacion_dm]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_dm') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor Leído M1</label>
                                        <input type="number" step="any" id="replica_1"
                                            name="controles_analiticos[replica_1]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.replica_1') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor Leído M2</label>
                                        <input type="number" step="any" id="replica_2"
                                            name="controles_analiticos[replica_2]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.replica_2') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>%DPR</label>
                                        <input type="text" id="dpr" name="controles_analiticos[dpr]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_duplicado"
                                            name="controles_analiticos[aceptable_duplicado]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blanco del método -->
                        <div class="border p-3 mb-3 rounded">
                            <h6 class="mb-3 text-center bg-secondary text-white py-2 rounded">Blanco del método</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Identificación de muestra</label>
                                        <input type="text" id="identificacion_bm"
                                            name="controles_analiticos[identificacion_bm]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.identificacion_bm') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>LCM</label>
                                        <input type="number" step="any" id="limite_cuantificacion_metodo"
                                            name="controles_analiticos[limite_cuantificacion_metodo]"
                                            class="form-control form-control-sm"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Valor leído</label>
                                        <input type="number" step="any" id="controles_analiticos[valor_leido]"
                                            name="controles_analiticos[valor_leido]"
                                            class="form-control form-control-sm"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Rango del metodo</label>
                                        <input type="number" step="any" id="rango_metodo"
                                            name="controles_analiticos[rango_metodo]"
                                            class="form-control form-control-sm"
                                            value="{{ old('controles_analiticos.rango_metodo') }}"
                                            oninput="evaluarAceptabilidadBlanco()">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Aceptable/no aceptable</label>
                                        <input type="text" id="aceptable_blanco"
                                            name="controles_analiticos[aceptable_blanco]"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label>Observaciones Generales</label>
                            <textarea id="controles_analiticos[observaciones]" name="controles_analiticos[observaciones]" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <!-- REGISTRO DE MUESTRAS -->
                <div class="card mt-3 border-0 shadow-none">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">Información de Resultados</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center" id="tablaMuestras">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Código interno</th>
                                        <th>Peso de muestra (g)</th>
                                        <th>Vol NaOH consumido Blanco (mL)</th>
                                        <th>% Humedad(g / 100g)</th>
                                        <th>Molaridad NaOH</th>
                                        <th>Vol NaOH consumido muestra (mL)</th>
                                        <th>Acidez</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="muestras-body">
                                    <!-- Fila inicial -->
                                    <tr class="muestra-fila">
                                        <td>
                                            <input type="text" id="rows[0][codigo_interno]" name="rows[0][codigo_interno]" class="form-control form-control-sm" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" id="rows[0][peso_muestra]" name="rows[0][peso_muestra]"
                                                class="form-control form-control-sm peso-muestra" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" id="rows[0][consumido_blanco]" name="rows[0][consumido_blanco]"
                                                class="form-control form-control-sm consumido-blanco" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" id="rows[0][porcentaje_humedad]" name="rows[0][porcentaje_humedad]"
                                                class="form-control form-control-sm humedad">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" id="rows[0][molaridad]" name="rows[0][molaridad]"
                                                class="form-control form-control-sm molaridad" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" id="rows[0][consumido_muestra]" name="rows[0][consumido_muestra]"
                                                class="form-control form-control-sm consumido_muestra" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" id="rows[0][acidez]" name="rows[0][acidez]"
                                                class="form-control form-control-sm acidez" readonly>
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
                        <button type="submit" class="btn btn-primary">Guardar Análisis de Acidez</button>
                        <a href="{{ route('lscefa.technical.analyses.acidity.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection

<script>
// Script completo para el manejo de filas dinámicas y cálculo de acidez

document.addEventListener('DOMContentLoaded', function() {
    let filaCount = 1; // Empezamos en 1 porque ya hay una fila inicial
    
    // FUNCIÓN PARA CALCULAR ACIDEZ SEGÚN FÓRMULA DEL EXCEL
    function calcularAcidez(fila) {
        // Obtener los valores de los campos de la fila específica
        const pesoMuestra = parseFloat(fila.querySelector('.peso-muestra').value) || 0;
        const consumidoBlanco = parseFloat(fila.querySelector('.consumido-blanco').value) || 0;
        const porcentajeHumedad = parseFloat(fila.querySelector('.humedad').value) || 0;
        const molaridad = parseFloat(fila.querySelector('.molaridad').value) || 0;
        const consumidoMuestra = parseFloat(fila.querySelector('.consumido_muestra').value) || 0;
        
        // Validar que los valores necesarios no sean cero
        if (pesoMuestra > 0) {
            // Aplicar la fórmula exacta del Excel: =(F-C)*E*(100+D)/B
            const acidez = ((consumidoMuestra - consumidoBlanco) * molaridad * (100 + porcentajeHumedad)) / pesoMuestra;
            
            // Asignar el resultado al campo de acidez
            fila.querySelector('.acidez').value = acidez.toFixed(4);
        } else {
            // Si no hay peso de muestra, limpiar el campo
            fila.querySelector('.acidez').value = '';
        }
    }
    
    // FUNCIÓN PARA AGREGAR EVENTOS A UNA FILA
    function agregarEventosFila(fila) {
        const inputs = fila.querySelectorAll('.peso-muestra, .consumido-blanco, .humedad, .molaridad, .consumido_muestra');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                calcularAcidez(fila);
            });
        });
        
        // Evento para el botón de quitar fila
        const btnQuitar = fila.querySelector('.quitar-fila');
        if (btnQuitar) {
            btnQuitar.addEventListener('click', function() {
                if (document.querySelectorAll('.muestra-fila').length > 1) {
                    fila.remove();
                    actualizarIndices();
                }
            });
        }
    }
    
    // FUNCIÓN PARA ACTUALIZAR LOS ÍNDICES DE LAS FILAS
    function actualizarIndices() {
        const filas = document.querySelectorAll('.muestra-fila');
        filas.forEach((fila, index) => {
            // Actualizar todos los inputs de la fila
            const inputs = fila.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                const id = input.getAttribute('id');
                
                if (name && name.includes('rows[')) {
                    // Reemplazar el índice en el name
                    const newName = name.replace(/rows\[\d+\]/, `rows[${index}]`);
                    input.setAttribute('name', newName);
                }
                
                if (id && id.includes('rows[')) {
                    // Reemplazar el índice en el id
                    const newId = id.replace(/rows\[\d+\]/, `rows[${index}]`);
                    input.setAttribute('id', newId);
                }
            });
        });
        
        filaCount = filas.length;
    }
    
    // FUNCIÓN PARA CREAR UNA NUEVA FILA
    function crearNuevaFila(indice) {
        const nuevaFila = document.createElement('tr');
        nuevaFila.className = 'muestra-fila';
        nuevaFila.innerHTML = `
            <td>
                <input type="text" id="rows[${indice}][codigo_interno]" name="rows[${indice}][codigo_interno]" 
                       class="form-control" required>
            </td>
            <td>
                <input type="number" step="0.01" id="rows[${indice}][peso_muestra]" name="rows[${indice}][peso_muestra]"
                       class="form-control peso-muestra" required>
            </td>
            <td>
                <input type="number" step="0.01" id="rows[${indice}][consumido_blanco]" name="rows[${indice}][consumido_blanco]"
                       class="form-control consumido-blanco" required>
            </td>
            <td>
                <input type="number" step="0.01" id="rows[${indice}][porcentaje_humedad]" name="rows[${indice}][porcentaje_humedad]"
                       class="form-control humedad">
            </td>
            <td>
                <input type="number" step="0.01" id="rows[${indice}][molaridad]" name="rows[${indice}][molaridad]"
                       class="form-control molaridad" required>
            </td>
            <td>
                <input type="number" step="0.01" id="rows[${indice}][consumido_muestra]" name="rows[${indice}][consumido_muestra]"
                       class="form-control consumido_muestra" required>
            </td>
            <td>
                <input type="number" step="0.01" id="rows[${indice}][acidez]" name="rows[${indice}][acidez]"
                       class="form-control acidez" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm quitar-fila">×</button>
            </td>
        `;
        
        return nuevaFila;
    }
    
    // EVENTO PARA AGREGAR NUEVA FILA
    document.getElementById('agregar-fila').addEventListener('click', function() {
        const tbody = document.getElementById('muestras-body');
        const nuevaFila = crearNuevaFila(filaCount);
        
        tbody.appendChild(nuevaFila);
        agregarEventosFila(nuevaFila);
        filaCount++;
    });
    
    // AGREGAR EVENTOS A LA FILA INICIAL
    const filaInicial = document.querySelector('.muestra-fila');
    if (filaInicial) {
        agregarEventosFila(filaInicial);
    }
    
    // FUNCIONES PARA CONTROLES DE CALIDAD
    
    function calcularRecuperacion() {
        const valorReferencia = parseFloat(document.getElementById('valor_referencia').value) || 0;
        const valorObtenido = parseFloat(document.getElementById('valor_obtenido').value) || 0;
        
        if (valorReferencia > 0) {
            const recuperacion = (valorObtenido / valorReferencia) * 100;
            document.getElementById('controles_analiticos[recuperacion]').value = recuperacion.toFixed(2);
            
            // Evaluar aceptabilidad
            const aceptable = (recuperacion >= 70 && recuperacion <= 130) ? 'Aceptable' : 'No Aceptable';
            document.getElementById('aceptable_fortificada').value = aceptable;
        }
    }
    
  function calcularError() {
        // Usar los campos específicos de la sección %Error 
        const valorReferencia = parseFloat(document.getElementById('valor_referencia_error')?.value) || 
                               parseFloat(document.querySelector('input[name="valor_referencia"]')?.value) || 0;
        const valorObtenido = parseFloat(document.getElementById('valor_obtenido_error')?.value) || 
                             parseFloat(document.querySelector('input[name="valor_obtenido"]')?.value) || 0;
        
        console.log('Valores para %Error:', {
            valorReferencia: valorReferencia,
            valorObtenido: valorObtenido
        });
        
        if (valorReferencia > 0) {
            // Fórmula correcta del Excel: =ABS(C28-D28)/C28*100
            // donde C28 es Valor Referencia y D28 es Valor Obtenido
            const diferencia = Math.abs(valorReferencia - valorObtenido);
            const error = (diferencia / valorReferencia) * 100;
            
            console.log('Cálculo %Error:', {
                diferencia: diferencia,
                division: diferencia / valorReferencia,
                errorFinal: error
            });
            
            document.getElementById('error_analitico').value = error.toFixed(1);
            
            // Evaluar aceptabilidad
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
        
        console.log('Valores DPR:', {
            replica1: replica1,
            replica2: replica2
        });
        
        if (replica1 > 0 && replica2 > 0) {
            // Fórmula Excel: =ABS(D31-C31)/((D31+C31)/2)*100
            const diferencia = Math.abs(replica2 - replica1);
            const promedio = (replica2 + replica1) / 2;
            const dpr = (diferencia / promedio) * 100;
            
            console.log('Cálculo DPR:', {
                diferencia: diferencia,
                promedio: promedio,
                division: diferencia / promedio,
                dpr: dpr
            });
            
            // Redondear a 1 decimal para que coincida con Excel
            document.getElementById('dpr').value = dpr.toFixed(1);
            
            // Criterio de aceptabilidad: DPR < 25%
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
        const rangoMetodo = parseFloat(document.getElementById('rango_metodo').value) || 0;
        
        let aceptable = 'No Evaluado';
        
        if (lcm > 0) {
            // Criterio: el valor leído debe ser menor que el LCM
            aceptable = (valorLeido < lcm) ? 'Aceptable' : 'No Aceptable';
        }
        
        document.getElementById('aceptable_blanco').value = aceptable;
    }
    
    // AGREGAR EVENTOS A LOS CONTROLES DE CALIDAD
    
    // Eventos para muestra fortificada
    const valorReferenciaFort = document.getElementById('valor_referencia');
    const valorObtenidoFort = document.getElementById('valor_obtenido');
    if (valorReferenciaFort) valorReferenciaFort.addEventListener('input', calcularRecuperacion);
    if (valorObtenidoFort) valorObtenidoFort.addEventListener('input', calcularRecuperacion);
    
    // Eventos para %Error
    const valorReferenciaError = document.querySelector('input[name="valor_referencia"]');
    const valorObtenidoError = document.querySelector('input[name="valor_obtenido"]');
    if (valorReferenciaError) valorReferenciaError.addEventListener('input', calcularError);
    if (valorObtenidoError) valorObtenidoError.addEventListener('input', calcularError);
    
    // Eventos para DPR
    const replica1 = document.getElementById('replica_1');
    const replica2 = document.getElementById('replica_2');
    if (replica1) replica1.addEventListener('input', calcularDPR);
    if (replica2) replica2.addEventListener('input', calcularDPR);
    
    // Eventos para blanco del método
    const lcmInput = document.getElementById('limite_cuantificacion_metodo');
    const valorLeidoInput = document.getElementById('controles_analiticos[valor_leido]');
    const rangoMetodoInput = document.getElementById('rango_metodo');
    if (lcmInput) lcmInput.addEventListener('input', evaluarAceptabilidadBlanco);
    if (valorLeidoInput) valorLeidoInput.addEventListener('input', evaluarAceptabilidadBlanco);
    if (rangoMetodoInput) rangoMetodoInput.addEventListener('input', evaluarAceptabilidadBlanco);
});
</script>
