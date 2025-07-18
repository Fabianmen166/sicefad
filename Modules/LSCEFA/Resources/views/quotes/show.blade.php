@extends('lscefa::layouts.master')
@section('content')
<div class="container py-4">
    <div class="card shadow">
        <h5 class="card-header">Detalles de la Cotización #{{ $quote->quote_id ?? 'N/A' }}</h5>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Información del Cliente</h6>
                    <p><strong>NIT:</strong> {{ $quote->customer->tax_id ?? 'N/A' }}</p>
                    <p><strong>Solicitante:</strong> {{ $quote->customer->applicant ?? 'N/A' }}</p>
                    <p><strong>Tipo de Cliente:</strong> {{ $quote->customer->customerType->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <h6>Información de la Cotización</h6>
                    <p><strong>ID de Cotización:</strong> {{ $quote->quote_id }}</p>
                    <p><strong>Creado por:</strong> {{ $quote->user->name ?? 'N/A' }}</p>
                    <p><strong>Total:</strong> ${{ number_format($quote->total, 2) }}</p>
                </div>
            </div>
            <h6 class="mt-4">Servicios y Paquetes por Unidad</h6>
            @php
                $servicesPerUnit = [];
                foreach ($quote->quoteServices as $qs) {
                    $unitIdx = $qs->unit_index ?? 0;
                    $servicesPerUnit[$unitIdx][] = $qs;
                }
                use Modules\LSCEFA\Models\Service;
            @endphp
            @if (!empty($servicesPerUnit))
                @foreach ($servicesPerUnit as $unitIndex => $unitServices)
                    <h6 class="mt-3">Unidad {{ $unitIndex + 1 }}</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unitServices as $qs)
                                @if ($qs->service_id && $qs->service)
                                    <tr>
                                        <td>Servicio</td>
                                        <td>{{ $qs->service->name ?? $qs->service->descripcion ?? 'No disponible' }}</td>
                                        <td>{{ $qs->quantity ?? $qs->cantidad }}</td>
                                        <td>${{ number_format($qs->subtotal, 2) }}</td>
                                    </tr>
                                @elseif ($qs->service_package_id && $qs->servicePackage)
                                    <tr>
                                        <td>Paquete</td>
                                        <td>{{ $qs->servicePackage->name ?? $qs->servicePackage->nombre ?? 'No disponible' }}</td>
                                        <td>{{ $qs->quantity ?? $qs->cantidad }}</td>
                                        <td>${{ number_format($qs->subtotal, 2) }}</td>
                                    </tr>
                                    @if ($qs->servicePackage && $qs->servicePackage->included_services)
                                        @php
                                            $included = $qs->servicePackage->included_services;
                                            if (is_string($included)) {
                                                $included = json_decode($included, true);
                                            }
                                        @endphp
                                        @if (is_array($included))
                                            @foreach ($included as $includedService)
                                                <tr class="table-light">
                                                    <td>Incluido</td>
                                                    <td>
                                                        ↳
                                                        @if (is_numeric($includedService))
                                                            {{ optional(Service::find($includedService))->descripcion ?? 'Servicio no disponible' }}
                                                        @elseif (is_array($includedService))
                                                            {{ $includedService['description'] ?? $includedService['descripcion'] ?? 'Descripción no disponible' }}
                                                        @else
                                                            {{ $includedService }}
                                                        @endif
                                                    </td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <p>Sin servicios ni paquetes.</p>
            @endif
            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('lscefa.quality.quotes.edit', $quote->quote_id) }}" class="btn btn-primary">Editar</a>
                <a href="{{ route('lscefa.quality.quotes.pdf', $quote->quote_id) }}" class="btn btn-info">Descargar PDF</a>
                <form action="{{ route('lscefa.quality.quotes.destroy', $quote->quote_id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de eliminar esta cotización?')">Eliminar</button>
                </form>
                @php
                    $user = auth()->user();
                    $hasQualityRole = $user && $user->roles->contains('slug', 'lscefa.quality');
                    $hasAdminRole = $user && $user->roles->contains('slug', 'lscefa.admin');
                @endphp
                @if($hasQualityRole || $hasAdminRole)
                    <a href="{{ route('lscefa.quality.quotes.upload', $quote->quote_id) }}" class="btn btn-success">Subir Comprobante</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 