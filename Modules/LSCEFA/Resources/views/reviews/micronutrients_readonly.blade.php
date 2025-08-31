@extends('lscefa::layouts.master')

@section('title', 'Revisión - Análisis de Micronutrientes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-flask"></i> Revisión - Análisis de Micronutrientes
                        </h4>
                        <div>
                            <a href="{{ route('lscefa.quality.reviews.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Volver a Revisiones
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información del proceso -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información del Proceso</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Consecutivo:</th>
                                    <td>{{ $detail->consecutivo_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $customer->nombre ?? $customer->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Servicio:</th>
                                    <td>{{ $detail->service->descripcion ?? 'Análisis de Micronutrientes' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Información del Análisis</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Fecha de Análisis:</th>
                                    <td>{{ $analysis->fecha_analisis ? $analysis->fecha_analisis->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Equipo Utilizado:</th>
                                    <td>{{ $analysis->equipo_utilizado ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Intervalo del Método:</th>
                                    <td>{{ $analysis->intervalo_metodo ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Analista:</th>
                                    <td>{{ $technicianName ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Items de Ensayo -->
                    @if(isset($itemsEnsayo) && !empty($itemsEnsayo))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Items de Ensayo</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Código Interno</th>
                                            <th>Peso Muestra (g)</th>
                                            <th>Humedad (%)</th>
                                            <th>Volumen Final (ml)</th>
                                            <th>Mn - Lectura</th>
                                            <th>Mn - Factor</th>
                                            <th>Mn - Resultado (mg/kg)</th>
                                            <th>Fe - Lectura</th>
                                            <th>Fe - Factor</th>
                                            <th>Fe - Resultado (mg/kg)</th>
                                            <th>Zn - Lectura</th>
                                            <th>Zn - Factor</th>
                                            <th>Zn - Resultado (mg/kg)</th>
                                            <th>Cu - Lectura</th>
                                            <th>Cu - Factor</th>
                                            <th>Cu - Resultado (mg/kg)</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsEnsayo as $item)
                                        <tr>
                                            <td>{{ $item['codigo_interno'] ?? 'N/A' }}</td>
                                            <td>{{ $item['peso_muestra'] ?? 'N/A' }}</td>
                                            <td>{{ $item['humedad'] ?? 'N/A' }}</td>
                                            <td>{{ $item['volumen_final'] ?? 'N/A' }}</td>
                                            <td>{{ $item['mn_lectura'] ?? 'N/A' }}</td>
                                            <td>{{ $item['mn_factor'] ?? 'N/A' }}</td>
                                            <td>{{ $item['mn_resultado'] ?? 'N/A' }}</td>
                                            <td>{{ $item['fe_lectura'] ?? 'N/A' }}</td>
                                            <td>{{ $item['fe_factor'] ?? 'N/A' }}</td>
                                            <td>{{ $item['fe_resultado'] ?? 'N/A' }}</td>
                                            <td>{{ $item['zn_lectura'] ?? 'N/A' }}</td>
                                            <td>{{ $item['zn_factor'] ?? 'N/A' }}</td>
                                            <td>{{ $item['zn_resultado'] ?? 'N/A' }}</td>
                                            <td>{{ $item['cu_lectura'] ?? 'N/A' }}</td>
                                            <td>{{ $item['cu_factor'] ?? 'N/A' }}</td>
                                            <td>{{ $item['cu_resultado'] ?? 'N/A' }}</td>
                                            <td>{{ $item['observaciones'] ?? '' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Controles Analíticos -->
                    @if(isset($controlesAnaliticos) && !empty($controlesAnaliticos))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Controles Analíticos</h5>
                            
                            <!-- Blanco del Método -->
                            @if(isset($controlesAnaliticos['blanco_metodo']) && !empty($controlesAnaliticos['blanco_metodo']))
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Blanco del Método</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Identificación</th>
                                                    <th>Peso (g)</th>
                                                    <th>Volumen Final (ml)</th>
                                                    <th>Mn - Lectura</th>
                                                    <th>Fe - Lectura</th>
                                                    <th>Zn - Lectura</th>
                                                    <th>Cu - Lectura</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($controlesAnaliticos['blanco_metodo'] as $blanco)
                                                <tr>
                                                    <td>{{ $blanco['identificacion'] ?? 'N/A' }}</td>
                                                    <td>{{ $blanco['peso'] ?? 'N/A' }}</td>
                                                    <td>{{ $blanco['volumen_final'] ?? 'N/A' }}</td>
                                                    <td>{{ $blanco['mn_lectura'] ?? 'N/A' }}</td>
                                                    <td>{{ $blanco['fe_lectura'] ?? 'N/A' }}</td>
                                                    <td>{{ $blanco['zn_lectura'] ?? 'N/A' }}</td>
                                                    <td>{{ $blanco['cu_lectura'] ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Duplicado de Muestra -->
                            @if(isset($controlesAnaliticos['duplicado_muestra']) && !empty($controlesAnaliticos['duplicado_muestra']))
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Duplicado de Muestra</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Identificación</th>
                                                    <th>Peso (g)</th>
                                                    <th>Mn - Resultado (mg/kg)</th>
                                                    <th>Fe - Resultado (mg/kg)</th>
                                                    <th>Zn - Resultado (mg/kg)</th>
                                                    <th>Cu - Resultado (mg/kg)</th>
                                                    <th>DPR (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($controlesAnaliticos['duplicado_muestra'] as $duplicado)
                                                <tr>
                                                    <td>{{ $duplicado['identificacion'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['peso'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['mn_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['fe_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['zn_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['cu_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $duplicado['dpr'] ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Control Estándar -->
                            @if(isset($controlesAnaliticos['control_estandar']) && !empty($controlesAnaliticos['control_estandar']))
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Control Estándar</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Identificación</th>
                                                    <th>Lote</th>
                                                    <th>Peso (g)</th>
                                                    <th>Mn - Resultado (mg/kg)</th>
                                                    <th>Mn - Valor Esperado</th>
                                                    <th>Fe - Resultado (mg/kg)</th>
                                                    <th>Fe - Valor Esperado</th>
                                                    <th>Zn - Resultado (mg/kg)</th>
                                                    <th>Zn - Valor Esperado</th>
                                                    <th>Cu - Resultado (mg/kg)</th>
                                                    <th>Cu - Valor Esperado</th>
                                                    <th>Aceptable</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($controlesAnaliticos['control_estandar'] as $control)
                                                <tr>
                                                    <td>{{ $control['identificacion'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['lote'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['peso'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['mn_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['mn_valor_esperado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['fe_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['fe_valor_esperado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['zn_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['zn_valor_esperado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['cu_resultado'] ?? 'N/A' }}</td>
                                                    <td>{{ $control['cu_valor_esperado'] ?? 'N/A' }}</td>
                                                    <td>
                                                        @if(isset($control['aceptable']) && $control['aceptable'])
                                                            <span class="badge badge-success">Sí</span>
                                                        @else
                                                            <span class="badge badge-danger">No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Curva de Calibración -->
                            @if(isset($controlesAnaliticos['curva_calibracion']) && !empty($controlesAnaliticos['curva_calibracion']))
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Curva de Calibración</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Elemento</th>
                                                    <th>Concentración (mg/L)</th>
                                                    <th>Lectura</th>
                                                    <th>Factor</th>
                                                    <th>R²</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($controlesAnaliticos['curva_calibracion'] as $curva)
                                                <tr>
                                                    <td>{{ $curva['elemento'] ?? 'N/A' }}</td>
                                                    <td>{{ $curva['concentracion'] ?? 'N/A' }}</td>
                                                    <td>{{ $curva['lectura'] ?? 'N/A' }}</td>
                                                    <td>{{ $curva['factor'] ?? 'N/A' }}</td>
                                                    <td>{{ $curva['r2'] ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Observaciones -->
                    @if($analysis->observaciones)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Observaciones</h5>
                            <div class="alert alert-info">
                                {{ $analysis->observaciones }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Estado de Revisión -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Estado de Revisión</h5>
                            <div class="alert alert-{{ $analysis->review_status === 'approved' ? 'success' : ($analysis->review_status === 'rejected' ? 'danger' : 'warning') }}">
                                <strong>Estado:</strong> 
                                @if($analysis->review_status === 'approved')
                                    Aprobado
                                @elseif($analysis->review_status === 'rejected')
                                    Rechazado
                                @else
                                    Pendiente de Revisión
                                @endif
                            </div>
                            
                            @if($analysis->review_observations)
                            <div class="alert alert-secondary">
                                <strong>Observaciones del Revisor:</strong><br>
                                {{ $analysis->review_observations }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
