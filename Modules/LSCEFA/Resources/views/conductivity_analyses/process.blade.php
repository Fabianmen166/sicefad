@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de Conductividad')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Conductividad</h1>
                </div>
            </div>
        </div>
    </section>

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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('lscefa.conductivity_analysis.store') }}" method="POST" autocomplete="off" id="conductivityForm">
                @csrf
                <input type="hidden" name="fecha_analisis" value="{{ now()->format('Y-m-d') }}">

                <!-- Información General -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información General</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Consecutivo No.</label>
                                    <input type="text" name="consecutivo_no" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombre del Método</label>
                                    <input type="text" name="nombre_metodo" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Analista</label>
                                    <input type="text" class="form-control" value="{{ $user ? $user->nickname : '' }}" readonly>
                            </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha del Análisis</label>
                                    <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles del Equipo -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalles del Equipo</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Equipo Utilizado</label>
                                    <input type="text" name="equipo_utilizado" class="form-control">
                            </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Resolución Instrumental</label>
                                    <input type="text" name="resolucion_instrumental" class="form-control">
                        </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Unidades de Reporte Equipo</label>
                                    <input type="text" name="unidades_reporte" class="form-control">
                            </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Intervalo del Método</label>
                                    <input type="text" name="intervalo_metodo" class="form-control">
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles Analíticos: Blanco del Proceso -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Blanco del Proceso</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Blanco del proceso</th>
                                    <th>Valor leído (dS/m)</th>
                                    <th>Aceptable/No aceptable</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" class="form-control" value="Blanco del proceso" readonly></td>
                                    <td><input type="number" step="0.01" name="blanco_valor_leido" id="blanco_valor_leido" class="form-control"></td>
                                    <td><span id="blanco_aceptable"></span></td>
                                    <td><textarea name="blanco_observaciones" class="form-control"></textarea></td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Controles Analíticos: Precisión (Duplicados) -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Precisión (Duplicados)</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <td colspan="9">
                                    <div class="form-group mb-0">
                                        <label for="duplicado_identificacion" class="mb-0">Identificación de la Muestra (Aplicable a ambas réplicas)</label>
                                        <input type="text" class="form-control" id="duplicado_identificacion" name="duplicado_identificacion" value="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Réplica</th>
                                <th>Peso (g)</th>
                                <th>Volumen H₂O (mL)</th>
                                <th>Temperatura (°C)</th>
                                <th>Valor leído (µS/cm)</th>
                                <th>Valor leído (mS/m)</th>
                                <th>Promedio (mS/m)</th>
                                <th>Diferencia (mS/m)</th>
                                <th>Aceptable/No aceptable</th>
                                <th>Observaciones</th>
                            </tr>
                            <tr>
                                <td><strong>A</strong></td>
                                <td>
                                    <input type="number" step="0.0001" name="duplicado_peso" 
                                           class="form-control" value="">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="duplicado_volumen_agua" 
                                           class="form-control" value="">
                                </td>
                                <td>
                                    <input type="number" step="0.1" name="duplicado_temperatura" 
                                           class="form-control" value="">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="duplicado_a_valor_leido" 
                                           id="duplicado_a_valor_leido" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.01" id="duplicado_a_valor_leido_msm" 
                                           class="form-control" readonly>
                                </td>
                                <td rowspan="2">
                                    <span class="form-control-plaintext" id="precision_promedio">-</span>
                                </td>
                                <td rowspan="2">
                                    <span class="form-control-plaintext" id="precision_diferencia">-</span>
                                </td>
                                <td rowspan="2">
                                    <span class="form-control-plaintext" id="precision_aceptable">-</span>
                                </td>
                                <td rowspan="2">
                                    <textarea name="duplicado_observaciones" class="form-control" rows="3"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>B</strong></td>
                                <td colspan="3" class="text-center">
                                    <span class="text-muted">Mismos valores que Réplica A</span>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="duplicado_b_valor_leido" 
                                           id="duplicado_b_valor_leido" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.01" id="duplicado_b_valor_leido_msm" 
                                           class="form-control" readonly>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Controles Analíticos: Veracidad (Controles de calidad) -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Controles de calidad (Veracidad)</h3>
                    </div>
                    <div class="card-body">
                        <!-- Tabla de Estándar de Control -->
                        <h5>Estándar de Control</h5>
                        <table class="table table-bordered mb-4">
                            <thead>
                                <tr>
                                    <th>Valor esperado (dS/m)</th>
                                    <th>Valor leído (dS/m)</th>
                                    <th>% Recuperación</th>
                                    <th>Aceptable/No aceptable</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" step="0.0001" name="veracidad[0][valor_esperado]" id="veracidad_0_esperado" class="form-control"></td>
                                    <td><input type="number" step="0.0001" name="veracidad[0][valor_leido]" id="veracidad_0_leido" class="form-control"></td>
                                    <td><span id="veracidad_0_recuperacion"></span></td>
                                    <td><span id="veracidad_0_aceptable"></span></td>
                                    <td><textarea name="veracidad[0][observaciones]" class="form-control"></textarea></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Tabla de Muestra de Referencia -->
                        <h5>Muestra de Referencia</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Peso (g)</th>
                                    <th>Volumen H₂O (mL)</th>
                                    <th>Temperatura (°C)</th>
                                    <th>Valor esperado (dS/m)</th>
                                    <th>Valor leído (dS/m)</th>
                                    <th>% Recuperación</th>
                                    <th>Aceptable/No aceptable</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" step="0.0001" name="veracidad[1][peso]" class="form-control"></td>
                                    <td><input type="number" step="0.01" name="veracidad[1][volumen_agua]" class="form-control"></td>
                                    <td><input type="number" step="0.1" name="veracidad[1][temperatura]" class="form-control"></td>
                                    <td><input type="number" step="0.0001" name="veracidad[1][valor_esperado]" id="veracidad_1_esperado" class="form-control"></td>
                                    <td><input type="number" step="0.0001" name="veracidad[1][valor_leido]" id="veracidad_1_leido" class="form-control"></td>
                                    <td><span id="veracidad_1_recuperacion"></span></td>
                                    <td><span id="veracidad_1_aceptable"></span></td>
                                    <td><textarea name="veracidad[1][observaciones]" class="form-control"></textarea></td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="mt-4">Criterios de aceptación</h6>
                        <ul>
                            <li>Blanco de proceso: ≤ 0,1 dS/m</li>
                            <li>Precisión: Conductividad eléctrica a 25 ºC de 0 mS/m a 50 mS/m Variación aceptada 5 mS/m; >50 mS/m hasta 200 mS/m Variación aceptada 20 mS/m; >200 mS/m Variación aceptada 10%</li>
                            <li>Calidad del agua: Conductividad < 2 µS/cm a 25 °C</li>
                            <li>Veracidad: Recuperación (70 - 130)%</li>
                        </ul>
                    </div>
                </div>

                <!-- Ítems de Ensayo Pendientes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ítems de Ensayo Pendientes</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código Ítem</th>
                                    <th>Peso (g)</th>
                                    <th>Volumen H₂O (mL)</th>
                                    <th>Temperatura (°C)</th>
                                    <th>Valor leído (µS/cm)</th>
                                    <th>Valor leído (dS/m)</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $selectedAnalysisIds = $pendingAnalyses->pluck('id')->toArray();
                                @endphp
                                @foreach ($pendingItems as $index => $item)
                                    @if (in_array($item['analysis_id'], $selectedAnalysisIds))
                                        @php
                                            $analysis = $pendingAnalyses->firstWhere('id', $item['analysis_id']);
                                        @endphp
                                        @if ($analysis)
                                            <tr>
                                                <td><input type="text" name="items_ensayo[{{$index}}][codigo_item]" class="form-control" value="{{ old('items_ensayo.' . $index . '.codigo_item', $item['codigo_item'] ?? '') }}" readonly></td>
                                                <td><input type="number" step="0.0001" name="items_ensayo[{{$index}}][peso]" class="form-control @error('items_ensayo.' . $index . '.peso') is-invalid @enderror" value="{{ old('items_ensayo.' . $index . '.peso', $item['peso'] ?? '') }}" required>
                                                    @error('items_ensayo.' . $index . '.peso')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td><input type="number" step="0.01" name="items_ensayo[{{$index}}][volumen_agua]" class="form-control @error('items_ensayo.' . $index . '.volumen_agua') is-invalid @enderror" value="{{ old('items_ensayo.' . $index . '.volumen_agua', $item['volumen_agua'] ?? '') }}" required>
                                                    @error('items_ensayo.' . $index . '.volumen_agua')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td><input type="number" step="0.1" name="items_ensayo[{{$index}}][temperatura]" class="form-control @error('items_ensayo.' . $index . '.temperatura') is-invalid @enderror" value="{{ old('items_ensayo.' . $index . '.temperatura', $item['temperatura'] ?? '') }}" required>
                                                    @error('items_ensayo.' . $index . '.temperatura')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td><input type="number" step="0.0001" name="items_ensayo[{{$index}}][valor_leido]" class="form-control @error('items_ensayo.' . $index . '.valor_leido') is-invalid @enderror" value="{{ old('items_ensayo.' . $index . '.valor_leido', $item['valor_leido'] ?? '') }}" required>
                                                    @error('items_ensayo.' . $index . '.valor_leido')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td><input type="number" step="0.0001" name="items_ensayo[{{$index}}][valor_leido_dsm]" class="form-control @error('items_ensayo.' . $index . '.valor_leido_dsm') is-invalid @enderror" value="{{ old('items_ensayo.' . $index . '.valor_leido_dsm', $item['valor_leido_dsm'] ?? '') }}" required>
                                                    @error('items_ensayo.' . $index . '.valor_leido_dsm')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td><textarea name="items_ensayo[{{$index}}][observaciones]" class="form-control">{{ old('items_ensayo.' . $index . '.observaciones', $item['observaciones'] ?? '') }}</textarea></td>
                                            </tr>
                                        @endif
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addItemEnsayo()">Agregar Muestra</button>
                    </div>
                </div>

                <!-- Observaciones del Analista -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Observaciones del Analista</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea name="observaciones_analista" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Análisis</button>
                <a href="{{ route('lscefa.conductivity_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </section>
</div>

<script>
    let itemEnsayoCount = 1;
    function addItemEnsayo() {
        const container = document.getElementById('items-ensayo');
        const newItem = document.createElement('div');
        newItem.classList.add('row', 'item-ensayo');
        newItem.innerHTML = `
            <div class="col-md-2">
                <div class="form-group">
                    <label>Código Ítem</label>
                    <input type="text" name="items_ensayo[${itemEnsayoCount}][codigo_item]" class="form-control" value="{{ $codigo_item ?? '' }}" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Peso (g)</label>
                    <input type="number" step="0.0001" name="items_ensayo[${itemEnsayoCount}][peso_muestra]" class="form-control" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Volumen H₂O (mL)</label>
                    <input type="number" step="0.1" name="items_ensayo[${itemEnsayoCount}][volumen_agua]" class="form-control" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Temperatura (°C)</label>
                    <input type="number" step="0.1" name="items_ensayo[${itemEnsayoCount}][temperatura]" class="form-control" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Lectura (µS/cm)</label>
                    <input type="number" step="0.01" name="items_ensayo[${itemEnsayoCount}][lectura_uscm]" class="form-control" required oninput="this.parentElement.parentElement.nextElementSibling.querySelector('input').value = (this.value ? (parseFloat(this.value) * 0.001).toFixed(4) : '')">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Lectura (dS/cm)</label>
                    <input type="number" step="0.0001" class="form-control" readonly>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="items_ensayo[${itemEnsayoCount}][observaciones]" class="form-control"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newItem);
        itemEnsayoCount++;
    }
</script>

<script>
// Blanco del proceso
const blancoInput = document.getElementById('blanco_valor_leido');
const blancoAceptable = document.getElementById('blanco_aceptable');
if (blancoInput && blancoAceptable) {
    blancoInput.addEventListener('input', function() {
        const valor = parseFloat(blancoInput.value);
        if (!isNaN(valor)) {
            blancoAceptable.textContent = valor <= 0.1 ? 'Aceptable' : 'No aceptable';
            blancoAceptable.className = valor <= 0.1 ? 'text-success' : 'text-danger';
        } else {
            blancoAceptable.textContent = '';
            blancoAceptable.className = '';
        }
    });
}

// Precisión (Duplicados)
function actualizarPrecision() {
    const a = parseFloat(document.getElementById('duplicado_a_valor_leido').value);
    const b = parseFloat(document.getElementById('duplicado_b_valor_leido').value);
    const a_msm = document.getElementById('duplicado_a_valor_leido_msm');
    const b_msm = document.getElementById('duplicado_b_valor_leido_msm');
    const promedioSpan = document.getElementById('precision_promedio');
    const diferenciaSpan = document.getElementById('precision_diferencia');
    const aceptableSpan = document.getElementById('precision_aceptable');

    if (!isNaN(a)) a_msm.value = (a / 10).toFixed(2); else a_msm.value = '';
    if (!isNaN(b)) b_msm.value = (b / 10).toFixed(2); else b_msm.value = '';
    if (!isNaN(a) && !isNaN(b)) {
        const a_msm_val = a / 10;
        const b_msm_val = b / 10;
        const promedio = (a_msm_val + b_msm_val) / 2;
        const diferencia = Math.abs(a_msm_val - b_msm_val);
        promedioSpan.textContent = promedio.toFixed(2);
        diferenciaSpan.textContent = diferencia.toFixed(2);
        let limite;
        if (promedio <= 50) limite = 5;
        else if (promedio <= 200) limite = 20;
        else limite = promedio * 0.10;
        if (diferencia <= limite) {
            aceptableSpan.textContent = 'Aceptable';
            aceptableSpan.className = 'text-success';
        } else {
            aceptableSpan.textContent = 'No aceptable';
            aceptableSpan.className = 'text-danger';
        }
    } else {
        promedioSpan.textContent = '';
        diferenciaSpan.textContent = '';
        aceptableSpan.textContent = '';
        aceptableSpan.className = '';
    }
}
document.getElementById('duplicado_a_valor_leido').addEventListener('input', actualizarPrecision);
document.getElementById('duplicado_b_valor_leido').addEventListener('input', actualizarPrecision);

// Veracidad (Controles de calidad)
document.getElementById('estandar_control_esperado').addEventListener('input', function() { actualizarVeracidad('estandar_control'); });
document.getElementById('estandar_control_leido').addEventListener('input', function() { actualizarVeracidad('estandar_control'); });
document.getElementById('muestra_referencia_esperado').addEventListener('input', function() { actualizarVeracidad('muestra_referencia'); });
document.getElementById('muestra_referencia_leido').addEventListener('input', function() { actualizarVeracidad('muestra_referencia'); });
function actualizarVeracidad(type) {
    const esperado = parseFloat(document.getElementById(type + '_esperado').value);
    const leido = parseFloat(document.getElementById(type + '_leido').value);
    const recuperacionSpan = document.getElementById(type + '_recuperacion');
    const aceptableSpan = document.getElementById(type + '_aceptable');
    if (!isNaN(esperado) && !isNaN(leido) && esperado !== 0) {
        const recuperacion = (leido / esperado) * 100;
        recuperacionSpan.textContent = recuperacion.toFixed(2) + '%';
        if (recuperacion >= 70 && recuperacion <= 130) {
            aceptableSpan.textContent = 'Aceptable';
            aceptableSpan.className = 'text-success';
        } else {
            aceptableSpan.textContent = 'No aceptable';
            aceptableSpan.className = 'text-danger';
        }
    } else {
        recuperacionSpan.textContent = '';
        aceptableSpan.textContent = '';
        aceptableSpan.className = '';
    }
}
</script>

<script>
// Actualiza Lectura (dS/cm) automáticamente al escribir en Lectura (µS/cm) para todos los ítems
function updateDSCM(input) {
    var row = input.closest('tr');
    if (row) {
        var dsInput = row.querySelector('input.valor-leido-dsm');
        var dsHidden = row.querySelector('input[type="hidden"][name*="valor_leido_dsm"]');
        if (dsInput && dsHidden) {
            var val = input.value ? (parseFloat(input.value) * 0.001).toFixed(4) : '';
            dsInput.value = val;
            dsHidden.value = val;
        }
    }
}
document.querySelectorAll('input.valor-leido').forEach(function(input) {
    input.addEventListener('input', function() { updateDSCM(input); });
    updateDSCM(input);
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Asegurarse de que los campos numéricos tengan el formato correcto
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                if (this.value === '') return;
                const value = parseFloat(this.value);
                if (!isNaN(value)) {
                    this.value = value;
                }
            });
        });
    });
</script>
@endsection 