@extends('lscefa::layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Informe de resultados - Vista previa</h4>
        <a href="{{ route('lscefa.quality.reports.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Código interno ítem de ensayo:</strong> {{ $process->item_code }}
                </div>
                <div class="col-md-6 text-md-right">
                    <strong>Informe número:</strong> —
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Fecha emisión del informe:</strong> —
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Datos del cliente</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @php($qc = optional(optional($process->quote)->customer))
                    <div><strong>NIT/CC:</strong> {{ $qc->tax_id ?? '—' }}</div>
                    <div><strong>Solicitante:</strong> {{ $qc->applicant ?? '—' }}</div>
                    <div><strong>Contacto:</strong> {{ $qc->applicant ?? '—' }}</div>
                    <div><strong>Teléfono:</strong> {{ $qc->phone ?? '—' }}</div>
                    <div><strong>Correo electrónico:</strong> {{ $qc->email ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Lugar de muestreo:</strong> {{ $process->sampling_place ?? '—' }}</div>
                    <div><strong>Matriz:</strong> Suelo</div>
                    <div><strong>Descripción:</strong> {{ $process->description ?? '—' }}</div>
                    <div><strong>Fecha de muestreo:</strong> {{ $process->sampling_date ?: '—' }}</div>
                    <div><strong>Fecha de recepción:</strong> {{ $process->reception_date ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Resultados</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:25%">Ensayo</th>
                            <th style="width:15%">Resultado</th>
                            <th style="width:15%">Unidad</th>
                            <th style="width:15%">Fecha de Análisis</th>
                            <th style="width:20%">Técnica</th>
                            <th style="width:10%">Documento normativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['ensayo'] }}</td>
                                <td>{{ $row['resultado'] }}</td>
                                <td>{{ $row['unidad'] }}</td>
                                <td>{{ $row['fecha_analisis'] }}</td>
                                <td>{{ $row['tecnica'] }}</td>
                                <td>{{ $row['documento'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay resultados para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
