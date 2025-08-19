@extends('lscefa::layouts.technical')

@section('title', 'Análisis Técnicos')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Enlaces rápidos a análisis específicos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="m-0 font-weight-bold text-primary">Análisis Específicos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                                                  <div class="col-md-3 mb-3">
                              <a href="{{ route('lscefa.ph_analysis.index') }}" class="btn btn-outline-primary btn-block">
                                  <i class="fas fa-flask me-2"></i>Análisis de pH
                              </a>
                          </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('lscefa.technical.analyses.phosphorus.index') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-flask me-2"></i>Análisis de Fósforo
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('lscefa.technical.analyses.texture.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-layer-group me-2"></i>Análisis de Textura
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-flask me-2"></i>Bases Cambiables
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enlaces a análisis específicos -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Procesos Técnicos Pendientes</h5>
                </div>
                <div class="card-body p-0">
                    @if ($processes->isEmpty())
                        <div class="alert alert-info m-4">No hay procesos pendientes para análisis técnico.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código de Item</th>
                                        <th>Servicios Pendientes</th>
                                        <th>Servicios Realizados</th>
                                        <th>Fecha de Entrega</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($processes as $process)
                                        <tr>
                                            <td class="align-middle">{{ $process->item_code }}</td>
                                            <td class="align-middle">
                                                @if(empty($pending[$process->process_id]))
                                                    <span class="text-muted">Ningún servicio pendiente</span>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($pending[$process->process_id] as $spd)
                                                            <li>{{ $spd->service->descripcion ?? 'Servicio' }} (ID: {{ $spd->service_id }})</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if(empty($completed[$process->process_id]))
                                                    <span class="text-muted">Ningún servicio realizado</span>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($completed[$process->process_id] as $spd)
                                                            <li>{{ $spd->service->descripcion ?? 'Servicio' }} (ID: {{ $spd->service_id }})</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @php
                                                    $date = !empty($process->delivery_date) ? \Carbon\Carbon::parse($process->delivery_date) : null;
                                                    $daysLeft = $date ? \Carbon\Carbon::now()->startOfDay()->diffInDays($date->startOfDay(), false) : null;
                                                    $badgeClass = 'bg-secondary';
                                                    if (!is_null($daysLeft)) {
                                                        if ($daysLeft < 0) {
                                                            $badgeClass = 'bg-danger';
                                                        } elseif ($daysLeft <= 2) {
                                                            $badgeClass = 'bg-danger';
                                                        } elseif ($daysLeft <= 5) {
                                                            $badgeClass = 'bg-orange';
                                                        } elseif ($daysLeft <= 7) {
                                                            $badgeClass = 'bg-warning text-dark';
                                                        } else {
                                                            $badgeClass = 'bg-success';
                                                        }
                                                    }
                                                @endphp
                                                @if($date)
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ $date->format('d/m/Y') }}
                                                        @if(!is_null($daysLeft)) ({{ $daysLeft }} días) @endif
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2">
                            {{ $processes->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabla de Análisis Devueltos -->
            <div class="card shadow mt-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">Análisis Devueltos</h5>
                </div>
                <div class="card-body p-0">
                    @php
                        // 1) Aplanar los detalles rechazados
                        $returnedDetails = collect();
                        foreach ($returned as $processId => $details) {
                            foreach ($details as $spd) {
                                // Resolver el análisis asociado (pH o Conductividad)
                                $analysis = $spd->phAnalysis ?? $spd->conductivityAnalysis ?? null;
                                if (!$analysis) { continue; }

                                $type = $spd->phAnalysis ? 'ph' : ($spd->conductivityAnalysis ? 'conductivity' : null);
                                $returnedDetails->push((object) [
                                    'process_id' => $spd->process_id,
                                    'service_id' => $spd->service_id,
                                    'service_name' => $spd->service->descripcion ?? 'Servicio',
                                    'type' => $type,
                                    'consecutivo_no' => $analysis->consecutivo_no ?? null,
                                    'review_date' => $analysis->review_date ?? $analysis->updated_at ?? $spd->updated_at ?? null,
                                    'review_observations' => $analysis->review_observations ?? $spd->observations ?? null,
                                ]);
                            }
                        }

                        // 2) Agrupar por consecutivo_no con llave de respaldo si viene vacío
                        $returnedWithKey = $returnedDetails->map(function($it){
                            $key = !empty($it->consecutivo_no)
                                ? $it->consecutivo_no
                                : ('PROC-' . ($it->process_id ?? '0') . '-SRV-' . ($it->service_id ?? '0'));
                            $it->consecutivo_key = $key;
                            return $it;
                        });

                        $groupedByConsecutivo = $returnedWithKey
                            ->groupBy('consecutivo_key')
                            ->map(function($group){
                                $services = $group->pluck('service_name')->unique()->values()->all();
                                $first = $group->first();
                                // Tomar la fecha de revisión más reciente del grupo
                                $maxDate = $group->max(function($g){ return $g->review_date ? \Carbon\Carbon::parse($g->review_date) : null; });
                                // Unir observaciones no vacías
                                $observations = $group->pluck('review_observations')->filter()->unique()->values()->all();
                                return (object) [
                                    'consecutivo_no' => $first->consecutivo_no ?? null,
                                    'services' => $services,
                                    'review_date' => $maxDate,
                                    'observations' => $observations,
                                    // Guardar un item de referencia para armar la ruta de acción
                                    'ref' => $first,
                                ];
                            });
                    @endphp
                    @if ($groupedByConsecutivo->isEmpty())
                        <div class="alert alert-info m-4">No hay análisis devueltos para mostrar.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Consecutivo</th>
                                        <th>Servicio(s)</th>
                                        <th>Fecha de revisión</th>
                                        <th>Observaciones</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groupedByConsecutivo as $row)
                                        @php
                                            $serviceText = implode(', ', $row->services ?? []);
                                            $obsText = empty($row->observations) ? '—' : implode(' | ', $row->observations);
                                            $ref = $row->ref;
                                            // Construir ruta de acción según tipo
                                            $actionUrl = '#';
                                            if ($ref && $ref->type === 'ph') {
                                                $actionUrl = route('lscefa.ph_analysis.show', [$ref->process_id, $ref->service_id]);
                                            } elseif ($ref && $ref->type === 'conductivity') {
                                                $actionUrl = route('lscefa.conductivity_analysis.show', [$ref->process_id, $ref->service_id]);
                                            }
                                        @endphp
                                        <tr>
                                            <td class="align-middle">{{ $row->consecutivo_no ?? '—' }}</td>
                                            <td class="align-middle">{{ $serviceText ?: '—' }}</td>
                                            <td class="align-middle">{{ $row->review_date ? \Carbon\Carbon::parse($row->review_date)->format('d/m/Y H:i') : '—' }}</td>
                                            <td class="align-middle">{{ $obsText }}</td>
                                            <td class="align-middle text-right">
                                                <a href="{{ $actionUrl }}" class="btn btn-warning btn-sm" title="Revisar">
                                                    <i class="fas fa-undo-alt"></i> Revisar
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Ajustes específicos para mejorar el espaciado */
    .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        color: #4e73df;
        background-color: #f8f9fc;
    }
    
    .table > :not(:first-child) {
        border-top: none;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    /* Helper para naranja (Bootstrap usa warning como amarillo) */
    .bg-orange { background-color: #fd7e14 !important; color: #fff !important; }
</style>
@endsection