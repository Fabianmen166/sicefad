@extends('lscefa::layouts.technical')

@section('title', 'Procesar Análisis de Boro')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Boro</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.panel') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.boron.index') }}">Gestión de Boro</a></li>
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

            <form action="{{ route('lscefa.technical.analyses.boron.store') }}" method="POST">
                @csrf
                <input type="hidden" name="process_id" value="{{ $process->process_id }}">
                <input type="hidden" name="service_id" value="{{ $service->services_id }}">
                <input type="hidden" name="curva_valor_leido" id="curva_valor_leido_hidden">
                <input type="hidden" name="curva_error_porcentaje" id="curva_error_porcentaje_hidden">
                <input type="hidden" name="curva_aceptabilidad" id="curva_aceptabilidad_hidden">
                <input type="hidden" name="duplicado_a" id="duplicado_a_hidden">
                <input type="hidden" name="duplicado_b" id="duplicado_b_hidden">
                <input type="hidden" name="dpr_resultado" id="dpr_resultado_hidden">
                <input type="hidden" name="dpr_aceptabilidad" id="dpr_aceptabilidad_hidden">
                
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
                                <td colspan="2" style="width: 30%"><input type="text" class="form-control" name="metodologia_aplicada" value="{{ old('metodologia_aplicada', 'Extracción por Bray (II) y cuantificación por ácido ascórbico') }}"></td>
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
                                                        <th class="text-center">Aceptabilidad</th>
                                                        <th class="text-center">Duplicado</th>
                                                        <th class="text-center">Valor Leído</th>
                                                        <th class="text-center">% DPR</th>
                                                        <th class="text-center">Aceptabilidad</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="2" class="align-middle text-center"><strong>Curva de calibración</strong></td>
                                                        <td rowspan="2" class="align-middle"><input type="number" step="any" class="form-control" name="curva_valor" id="curva_valor" value="0.995" readonly></td>
                                                        <td rowspan="2" class="align-middle"><input type="number" step="any" class="form-control" id="curva_valor_leido"></td>
                                                        <td rowspan="2" class="align-middle"><input type="number" step="any" class="form-control" id="curva_error_porcentaje" readonly></td>
                                                        <td rowspan="2" class="align-middle"><input type="text" class="form-control" id="curva_aceptabilidad" readonly></td>
                                                        <td class="text-center"><strong>Duplicado A</strong></td>
                                                        <td><input type="number" step="any" class="form-control" id="duplicado_a"></td>
                                                        <td rowspan="2"><input type="number" step="any" class="form-control" id="dpr_resultado" readonly></td>
                                                        <td rowspan="2"><input type="text" class="form-control" id="dpr_aceptabilidad" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center"><strong>Duplicado B</strong></td>
                                                        <td><input type="number" step="any" class="form-control" id="duplicado_b"></td>
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
                                            <th class="text-center">Boro Disponible (mg/L)</th>
                                            <th class="text-center">Boro Disponible (mg/kg)</th>
                                            <th class="text-center">Observaciones</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" class="form-control" name="items_ensayo[0][codigo_interno]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][peso_muestra]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][pw]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][v_extractante]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][lectura_blanco]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][factor_dilucion]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][boro_disponible_mg_l]"></td>
                                            <td><input type="number" step="any" class="form-control" name="items_ensayo[0][boro_disponible_mg_kg]" readonly></td>
                                            <td><input type="text" class="form-control" name="items_ensayo[0][observaciones]"></td>
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
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1" style="color: #28a745;"></i>Guardar Análisis
                        </button>
                        <a href="{{ route('lscefa.technical.analyses.boron.index') }}" class="btn btn-secondary">
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
        let itemRowIndex = 0;
        $('#add_item_row').click(function() {
            itemRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="items_ensayo[${itemRowIndex}][codigo_interno]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][peso_muestra]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][pw]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][v_extractante]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][lectura_blanco]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][factor_dilucion]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][boro_disponible_mg_l]"></td>
                    <td><input type="number" step="any" class="form-control" name="items_ensayo[${itemRowIndex}][boro_disponible_mg_kg]" readonly></td>
                    <td><input type="text" class="form-control" name="items_ensayo[${itemRowIndex}][observaciones]"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#items_ensayo_table tbody').append(newRow);
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
            let aceptabilidad = '';
            
            if (valorEsperado !== 0) {
                errorPorcentaje = Math.abs(valorLeido - valorEsperado) / valorEsperado * 100;
            }
            
            $('#curva_error_porcentaje').val(errorPorcentaje.toFixed(2));
            
            // Aceptabilidad para curva de calibración
            if (valorLeido < 0.995) {
                aceptabilidad = 'No aceptable';
            } else {
                aceptabilidad = 'Aceptable';
            }
            $('#curva_aceptabilidad').val(aceptabilidad);
            
            // Sincronizar con campos ocultos
            $('#curva_valor_leido_hidden').val(valorLeido);
            $('#curva_error_porcentaje_hidden').val(errorPorcentaje.toFixed(2));
            $('#curva_aceptabilidad_hidden').val(aceptabilidad);
        }
        $(document).on('input', '#curva_valor_leido', calcularErrorCurva);
        calcularErrorCurva();

        // Valores fijos de la curva de calibración (ajusta según tu caso)
        const m = 1; // pendiente (ajusta este valor)
        const b = 0; // intersección (ajusta este valor)

        function calcularBoroEnsayo() {
            $('#items_ensayo_table tbody tr').each(function() {
                const boroMgL = parseFloat($(this).find('input[name$="[boro_disponible_mg_l]"]').val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat($(this).find('input[name$="[peso_muestra]"]').val().replace(',', '.')) || 0;
                const vExtractante = parseFloat($(this).find('input[name$="[v_extractante]"]').val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat($(this).find('input[name$="[factor_dilucion]"]').val().replace(',', '.')) || 0;
                const pw = parseFloat($(this).find('input[name$="[pw]"]').val().replace(',', '.')) || 0;

                let boroMgKg = "";
                if (boroMgL === 0) {
                    boroMgKg = "";
                } else {
                    // Fórmula exacta según Excel: ((H18*E18*G18)/C18*(100+D18)/100)
                    // Donde: H18=boroMgL, E18=vExtractante, G18=factorDilucion, C18=pesoMuestra, D18=pw
                    boroMgKg = ((boroMgL * vExtractante * factorDilucion) / pesoMuestra * (100 + pw) / 100);
                }
                $(this).find('input[name$="[boro_disponible_mg_kg]"]').val(boroMgKg === "" ? '' : boroMgKg.toFixed(2));
            });
        }

        // Ejecutar al cambiar cualquier input relevante
        $(document).on('input', '#items_ensayo_table input', calcularBoroEnsayo);
        // Ejecutar al cargar la página
        calcularBoroEnsayo();
        
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
