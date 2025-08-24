@extends('lscefa::layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4>Procesos en realización</h4>
        </div>
    </div>

    <form method="get" action="{{ route('lscefa.quality.reports.index') }}" class="mb-3">
        <div class="form-row align-items-end">
            <div class="col-md-4">
                <label for="item">Filtrar por Código de Ítem</label>
                <input type="text" id="item" name="item" class="form-control" value="{{ $itemFilter }}" placeholder="Ej: 001-pr-2025">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('lscefa.quality.reports.index') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Código de Ítem</th>
                            <th>Proceso</th>
                            <th>Cliente</th>
                            <th>Servicios</th>
                            <th>Estado</th>
                            <th style="width:120px">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($processes as $process)
                            <tr>
                                <td>{{ $process->item_code }}</td>
                                <td>{{ $process->process_id }}</td>
                                <td>{{ optional(optional($process->quote)->customer)->applicant ?? optional($process->quote)->customer_name ?? 'N/D' }}</td>
                                <td>
                                    @foreach($process->serviceProcessDetails as $spd)
                                        <span class="badge badge-{{ $spd->status === 'approved' ? 'success' : ($spd->status === 'completed' ? 'warning' : 'secondary') }}">
                                            {{ $spd->service->descripcion ?? 'N/A' }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    @php
                                        $hasApprovedReports = false;
                                        
                                        // Check if any service is approved
                                        if ($process->serviceProcessDetails->where('status', 'approved')->count() > 0) {
                                            $hasApprovedReports = true;
                                        }
                                        
                                        // Check if there are approved boron analyses
                                        $boronAnalyses = \Modules\LSCEFA\Entities\BoronAnalysisDetail::where('process_id', $process->process_id)
                                            ->where('review_status', 'approved')
                                            ->count();
                                        
                                        if ($boronAnalyses > 0) {
                                            $hasApprovedReports = true;
                                        }
                                    @endphp
                                    
                                    @if($hasApprovedReports)
                                        <span class="badge badge-success">Con informes disponibles</span>
                                    @else
                                        {{ ucfirst($process->status) }}
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('lscefa.quality.reports.show', $process->process_id) }}">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay procesos en realización.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($processes, 'links'))
        <div class="card-footer">
            {{ $processes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
