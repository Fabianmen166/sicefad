@extends('lscefa::layouts.master')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Detalles del Proceso</h3>
    <div class="card mb-4">
        <div class="card-header">Información del Proceso</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>ID del Proceso:</strong> {{ $process->process_id }}<br>
                    <strong>Estado:</strong> {{ $process->status }}<br>
                    <strong>Fecha de Inicio:</strong> {{ $process->reception_date }}<br>
                    <strong>Item Code:</strong> {{ $process->item_code }}<br>
                    <strong>Comunicación con el Cliente:</strong> {{ $process->client_communication }}<br>
                    <strong>Días para Procesar:</strong> {{ $process->processing_days }}<br>
                    <strong>Fecha de Recepción:</strong> {{ $process->reception_date }}<br>
                </div>
                <div class="col-md-6">
                    <strong>Descripción:</strong> {{ $process->description }}<br>
                    <strong>Lugar de Muestreo:</strong> {{ $process->sampling_place ?? 'No especificado' }}<br>
                    <strong>Fecha de Muestreo:</strong> {{ $process->sampling_date ?? 'No especificada' }}<br>
                    @php
                        $responsable = \App\Models\User::find($process->reception_responsible);
                    @endphp
                    <strong>Responsable de Recepción:</strong>
                    @if($responsable)
                        {{ $responsable->nickname }}
                    @else
                        {{ $process->reception_responsible }}
                    @endif
                    <br>
                    <strong>Fecha de Entrega:</strong> {{ $process->delivery_date }}<br>
                    <strong>Comprobante de Cotización:</strong>
                    @if($process->quote && $process->quote->file)
                        <a href="{{ route('lscefa.comprobante_file.download', ['quote_id' => $process->quote->quote_id, 'filename' => $process->quote->file]) }}" class="btn btn-info btn-sm" target="_blank">Descargar Comprobante</a>
                    @else
                        N/A
                    @endif
                    <br>
                    <strong>Archivo de Comunicación:</strong>
                    @if($process->communication_file)
                        <a href="{{ route('lscefa.communication_file.download', ['filename' => $process->communication_file]) }}" class="btn btn-info btn-sm" target="_blank">Descargar Archivo</a>
                    @else
                        N/A
                    @endif
                    <br>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Información de la Cotización</div>
        <div class="card-body">
            @if($process->quote)
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID de la Cotización:</strong> {{ $process->quote->quote_id }}<br>
                        <strong>Total:</strong> {{ number_format($process->quote->total, 2) }}<br>
                        <strong>Fecha de Creación:</strong> {{ $process->quote->created_at }}<br>
                    </div>
                    <div class="col-md-6">
                        <strong>Solicitante:</strong> {{ $process->quote->customer->applicant ?? '' }}<br>
                        <strong>Teléfono:</strong> {{ $process->quote->customer->phone ?? '' }}<br>
                        <strong>NIT:</strong> {{ $process->quote->customer->tax_id ?? '' }}<br>
                        <strong>Correo:</strong> {{ $process->quote->customer->email ?? '' }}<br>
                        <strong>Tipo de Cliente:</strong> {{ $process->quote->customer->customerType->name ?? '' }}<br>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">No hay información de la cotización asociada.</div>
            @endif
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Servicios a Realizar</div>
        <div class="card-body">
            @php
                $services = collect();
                if($process->quote && $process->quote->quoteServices) {
                    $services = $process->quote->quoteServices->where('unit_index', $process->item_code ? (is_numeric(str_replace('UNIDAD-', '', $process->item_code)) ? intval(str_replace('UNIDAD-', '', $process->item_code)) - 1 : 0) : 0);
                }
            @endphp
            @if($services->isEmpty())
                <div class="alert alert-info">No hay servicios asociados a este proceso.</div>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                            <tr>
                                <td>{{ $service->service_id ? 'Servicio' : 'Paquete' }}</td>
                                <td>
                                    @if($service->service_id)
                                        {{ $service->service->descripcion ?? 'Servicio no encontrado' }}
                                    @elseif($service->service_package_id)
                                        {{ $service->servicePackage->name ?? 'Paquete no encontrado' }}
                                    @else
                                        Detalles no disponibles
                                    @endif
                                </td>
                                <td>{{ $service->quantity ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    <a href="{{ route('lscefa.quality.processes.index') }}" class="btn btn-secondary mt-3">Volver al Listado de Procesos</a>
</div>
@endsection 