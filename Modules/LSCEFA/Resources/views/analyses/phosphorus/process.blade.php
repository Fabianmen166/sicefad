@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de Fósforo')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Fósforo</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.panel') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.phosphorus.index') }}">Gestión de Fósforo</a></li>
                        <li class="breadcrumb-item active">Procesar Análisis</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
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

            <form action="{{ route('lscefa.technical.analyses.phosphorus.store') }}" method="POST">
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
                                <td class="fw-bold" style="width: 10%">Consecutivo:</td>
                                <td style="width: 18%"><input type="text" class="form-control" name="consecutivo_no" value="{{ old('consecutivo_no', '1') }}"></td>
                                <td class="fw-bold" style="width: 16%">Metodología aplicada:</td>
                                <td colspan="2" style="width: 30%"><span class="form-control-plaintext">Extracción por Bray (II) y cuantificación por ácido ascórbico</span></td>
                                <td class="fw-bold" style="width: 10%">Intervalo:</td>
                                <td style="width: 16%"><input type="text" class="form-control" name="intervalo_metodo" value="{{ old('intervalo_metodo') }}"></td>
                            </tr>
                            <tr style="height: 10px;"></tr>
                            <tr>
                                <td class="fw-bold">Fecha:</td>
                                <td><input type="date" class="form-control" name="fecha_analisis" value="{{ old('fecha_analisis', now()->format('Y-m-d')) }}"></td>
                                <td class="fw-bold">Equipo:</td>
                                <td><input type="text" class="form-control" name="equipo_utilizado" value="{{ old('equipo_utilizado') }}"></td>
                                <td></td>
                                <td class="fw-bold">Analista:</td>
                                <td><input type="text" class="form-control" name="analista" value="{{ old('analista', Auth::user()->name) }}"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Barra de Navegación Horizontal -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body p-0">
                                    <ul class="nav nav-tabs nav-fill" id="analysisTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="controls-tab" data-toggle="tab" href="#controls-content" role="tab" aria-controls="controls-content" aria-selected="true">
                                                <i class="fas fa-flask mr-2"></i>Controles Analíticos
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="items-tab" data-toggle="tab" href="#items-content" role="tab" aria-controls="items-content" aria-selected="false">
                                                <i class="fas fa-list-alt mr-2"></i>Ítems de Ensayo
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido de las Pestañas -->
                    <div class="tab-content" id="analysisTabsContent">
                        <!-- Pestaña Controles Analíticos -->
                        <div class="tab-pane fade show active" id="controls-content" role="tabpanel" aria-labelledby="controls-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="items_ensayo" class="h5 mb-3">
                                            <i class="fas fa-flask mr-2" style="color: #28a745;"></i>Controles Analíticos
                                        </label>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="controles_analiticos_table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">Identificación del Control</th>
                                                        <th class="text-center">Valor Esperado</th>
                                                        <th class="text-center">Valor Leído</th>
                                                        <th class="text-center">% de Error</th>
                                                        <th class="text-center">Aceptabilidad</th>
                                                        <th class="text-center">% Recuperación</th>
                                                        <th class="text-center">Aceptabilidad</th>
                                                        <th class="text-center">% DPR</th>
                                                        <th class="text-center">Aceptabilidad</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[0][identificacion]" value="Estándar A"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][valor_esperado]" value="30"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_error]" readonly></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_recuperacion]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_recuperacion]" readonly></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_dpr]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_dpr]" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[1][identificacion]" value="Estándar B"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][valor_esperado]" value="5"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][valor_leido]"></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_error]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_error]" readonly></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_recuperacion]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_recuperacion]" readonly></td>
                                                        <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_dpr]" readonly></td>
                                                        <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_dpr]" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="table-responsive mt-4">
                                            <table class="table table-bordered table-hover" id="curva_duplicados_table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">Curva de Calibración</th>
                                                        <th class="text-center">Valor</th>
                                                        <th class="text-center">Valor Leído</th>
                                                        <th class="text-center">% ERROR</th>
                                                        <th></th>
                                                        <th class="text-center">Duplicado</th>
                                                        <th class="text-center">Valor Leído</th>
                                                        <th class="text-center">% DPR</th>
                                                        <th class="text-center">Aceptabilidad</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="2" class="align-middle text-center"><strong>Curva de calibración</strong></td>
                                                        <td rowspan="2" class="align-middle"><input type="number" class="form-control" value="0.995" readonly></td>
                                                        <td rowspan="2" class="align-middle"><input type="number" step="any" class="form-control" id="curva_valor_leido" name="curva_valor_leido"></td>
                                                        <td rowspan="2" class="align-middle"><input type="number" step="any" class="form-control" id="curva_error_porcentaje" name="curva_error_porcentaje" readonly></td>

                                                        <td colspan="2" rowspan="2"></td>
                                                        <td class="text-center"><strong>Duplicado A</strong></td>
                                                        <td><input type="number" step="any" class="form-control" id="duplicado_a" name="duplicado_a"></td>
                                                        <td rowspan="2"><input type="number" step="any" class="form-control" id="dpr_resultado" name="dpr_resultado" readonly></td>
                                                        <td rowspan="2"><input type="text" class="form-control" id="dpr_aceptabilidad" name="dpr_aceptabilidad" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center"><strong>Duplicado B</strong></td>
                                                        <td><input type="number" step="any" class="form-control" id="duplicado_b" name="duplicado_b"></td>

                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Ítems de Ensayo -->
                        <div class="tab-pane fade" id="items-content" role="tabpanel" aria-labelledby="items-tab">
                            <h4 class="mb-3">
                                <i class="fas fa-list-alt mr-2" style="color: #28a745;"></i>Ítems de Ensayo
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="items_ensayo_table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Código Interno</th>
                                            <th class="text-center">Peso Muestra (g)</th>
                                            <th class="text-center">pW</th>
                                            <th class="text-center">V. Extractante (mL)</th>
                                            <th class="text-center">Lectura Blanco (mg/L)</th>
                                            <th class="text-center">Factor de Dilución (fd)</th>
                                            <th class="text-center">Fósforo Disponible (mg/L)</th>
                                            <th class="text-center">Fósforo Disponible (mg/kg)</th>
                                            <th class="text-center">Observaciones</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" class="form-control" name="items[0][codigo_interno]" value="Blanco de proceso"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][peso_muestra]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][pw]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][v_extractante]" value="50"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][lectura_blanco]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][factor_dilucion]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][fosforo_disponible_mg_l]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items[0][fosforo_disponible_mg_kg]" readonly></td>
                                            <td><input type="text" class="form-control" name="items[0][observaciones_item]"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-success btn-sm" id="add_item_row">
                                    <i class="fas fa-plus mr-1" style="color: #28a745;"></i>Agregar Fila
                                </button>
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
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Limpieza: se eliminan referencias a tablas no presentes en la vista (reporte_resultados_table, controles_calidad_table)

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Fin limpieza

        // Cálculo sobre Ítems de Ensayo se maneja con calcularFosforoEnsayo más abajo

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
            // Para cada fila: error, recuperación y aceptabilidad
            for (let i = 0; i < 2; i++) {
                let row = $('#controles_analiticos_table tbody tr').eq(i);
                const valorEsperado = parseFloat(row.find('input[name$="[valor_esperado]"]').val().replace(',', '.')) || 0;
                const valorLeido = parseFloat(row.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
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
            const valorLeido0 = parseFloat(row0.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
            const valorLeido1 = parseFloat(row1.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
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
        }
        $(document).on('input', '#controles_analiticos_table input', calcularControlesAnaliticos);
        calcularControlesAnaliticos();

        // Cálculo automático de % DPR y aceptabilidad para duplicados
        function calcularDPR() {
            const a = parseFloat($('#duplicado_a').val().replace(',', '.')) || 0;
            const b = parseFloat($('#duplicado_b').val().replace(',', '.')) || 0;
            let dpr = 0;
            let aceptabilidad = '';
            if ((a + b) !== 0) {
                let promedio = (a + b) / 2;
                dpr = Math.abs(a - b) / promedio * 100;
                aceptabilidad = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
            }
            $('#dpr_resultado').val(dpr.toFixed(2));
            $('#dpr_aceptabilidad').val(aceptabilidad);
        }
        $(document).on('input', '#duplicado_a, #duplicado_b', calcularDPR);
        calcularDPR();

        // Cálculo automático de % ERROR para curva de calibración
        function calcularErrorCurva() {
            const valorEsperado = 0.995; // Valor fijo de la curva
            const valorLeido = parseFloat($('#curva_valor_leido').val().replace(',', '.')) || 0;
            let errorPorcentaje = 0;
            
            if (valorEsperado !== 0) {
                errorPorcentaje = Math.abs(valorLeido - valorEsperado) / valorEsperado * 100;
            }
            
            $('#curva_error_porcentaje').val(errorPorcentaje.toFixed(2));
        }
        $(document).on('input', '#curva_valor_leido', calcularErrorCurva);
        calcularErrorCurva();

        // Valores fijos de la curva de calibración (ajusta según tu caso)
        const m = 1; // pendiente (ajusta este valor)
        const b = 0; // intersección (ajusta este valor)

        function calcularFosforoEnsayo() {
            $('#items_ensayo_table tbody tr').each(function() {
                const fosforoMgL = parseFloat($(this).find('input[name$="[fosforo_disponible_mg_l]"]').val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat($(this).find('input[name$="[peso_muestra]"]').val().replace(',', '.')) || 0;
                const vExtractante = parseFloat($(this).find('input[name$="[v_extractante]"]').val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat($(this).find('input[name$="[factor_dilucion]"]').val().replace(',', '.')) || 0;
                const pw = parseFloat($(this).find('input[name$="[pw]"]').val().replace(',', '.')) || 0;
                const lecturaBlanco = parseFloat($(this).find('input[name$="[lectura_blanco]"]').val().replace(',', '.')) || 0;

                let fosforoMgKg = "";
                if (fosforoMgL === 0) {
                    fosforoMgKg = "";
                } else {
                    // Fórmula corregida según Excel: ((Fósforo disponible (mg/L) × Factor de dilución) - Lectura Blanco) × V. Extractante / Peso muestra × (100 + pW) / 100
                    fosforoMgKg = ((fosforoMgL * factorDilucion) - lecturaBlanco) * vExtractante / pesoMuestra * (100 + pw) / 100;
                }
                $(this).find('input[name$="[fosforo_disponible_mg_kg]"]').val(fosforoMgKg === "" ? '' : fosforoMgKg.toFixed(2));
            });
        }

        // Ejecutar al cambiar cualquier input relevante
        $(document).on('input', '#items_ensayo_table input', calcularFosforoEnsayo);
        // Ejecutar al cargar la página
        calcularFosforoEnsayo();
        
        // Funcionalidad para las pestañas de navegación
        $(document).ready(function() {
            // Navegación entre pestañas
            $('#analysisTabs .nav-link').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');
                
                // Remove active class from all tabs and content
                $('#analysisTabs .nav-link').removeClass('active');
                $('#analysisTabsContent .tab-pane').removeClass('show active');
                
                // Add active class to clicked tab
                $(this).addClass('active');
                $(target).addClass('show active');
            });
        });
    });
</script>
@endpush

<style>
    /* Estilos para la barra de navegación horizontal */
    #analysisTabs {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    #analysisTabs .nav-link {
        border: none;
        border-radius: 0;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }
    
    #analysisTabs .nav-link:hover {
        border-color: transparent;
        background-color: #e9ecef;
        color: #495057;
    }
    
    #analysisTabs .nav-link.active {
        color: #007bff;
        background-color: #fff;
        border-bottom: 3px solid #007bff;
        font-weight: 600;
    }
    
    #analysisTabs .nav-link i {
        font-size: 1.1rem;
    }
    
    /* Estilos para el contenido de las pestañas */
    .tab-content {
        padding-top: 1rem;
    }
    
    .tab-pane {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        #analysisTabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        
        #analysisTabs .nav-link i {
            font-size: 1rem;
            margin-right: 0.25rem;
        }
    }
</style> 