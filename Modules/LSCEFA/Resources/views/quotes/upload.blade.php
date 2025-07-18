@extends('lscefa::layouts.master')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <h5 class="card-header">Subir Comprobante y Gestionar Proceso para Cotización #{{ $quote->quote_id ?? 'N/A' }}</h5>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            <!-- Formulario para Subir Comprobante -->
            <form action="{{ route('lscefa.quality.quotes.upload', $quote->quote_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="archivo">Seleccionar Comprobante:</label>
                    <input type="file" name="archivo" id="archivo" class="form-control" required>
                    @error('archivo')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Subir Comprobante</button>
                <a href="{{ route('lscefa.quality.quotes.show', $quote->quote_id) }}" class="btn btn-secondary">Volver</a>
            </form>

            <!-- Sección para Gestionar Procesos por Terreno -->
            <hr>
            <h3>Gestionar Procesos por Terreno</h3>
            @php
                $unitCount = $unitCount ?? 1;
                if ($unitCount < 1) $unitCount = 1;
                $existingProcesses = $quote->processes && $quote->processes->count() > 0 ? $quote->processes : collect();
                // $servicesPerUnit ya viene del controlador
            @endphp
            @if ($existingProcesses->count() > 0)
                <div class="alert alert-info">
                    <strong>Proceso iniciado.</strong> Ya existen procesos para esta cotización.<br>
                    <a href="{{ route('lscefa.quality.processes.index', $quote->quote_id) }}" class="btn btn-info mt-2">Ver Procesos Iniciados</a>
                </div>
            @elseif ($unitCount == 0)
                <div class="alert alert-warning">
                    No se han definido unidades para esta cotización.
                </div>
            @else
                <form action="{{ route('lscefa.quality.process.start', $quote->quote_id) }}" method="POST" id="process-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="unit_count" value="{{ $unitCount }}">
                    <div class="form-group">
                        <label for="comunicacion_cliente">Comunicación con el Cliente <span class="text-danger">*</span>:</label>
                        <textarea name="comunicacion_cliente" id="comunicacion_cliente" class="form-control" rows="3" required>{{ old('comunicacion_cliente') }}</textarea>
                        @error('comunicacion_cliente')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="archivo_comunicacion">Archivo de Comunicación (Opcional):</label>
                        <input type="file" name="archivo_comunicacion" id="archivo_comunicacion" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <small class="form-text text-muted">Formatos permitidos: PDF, Word (.doc, .docx), Excel (.xls, .xlsx). Tamaño máximo: 5MB</small>
                        @error('archivo_comunicacion')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="dias_procesar">Días para Procesar:</label>
                        <input type="number" name="dias_procesar" id="dias_procesar" class="form-control" value="{{ old('dias_procesar', 5) }}" min="1" required>
                        @error('dias_procesar')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="lugar_muestreo">Lugar de Muestreo (Opcional):</label>
                        <input type="text" name="lugar_muestreo" id="lugar_muestreo" class="form-control" value="{{ old('lugar_muestreo') }}">
                        @error('lugar_muestreo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha_muestreo">Fecha de Muestreo (Opcional):</label>
                        <input type="date" name="fecha_muestreo" id="fecha_muestreo" class="form-control" value="{{ old('fecha_muestreo') }}">
                        @error('fecha_muestreo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    @foreach (range(0, $unitCount - 1) as $unitIndex)
                        @php
                            $suggestedDescription = 'UNIDAD-' . ($unitIndex + 1);
                            $suggestedItemCode = 'UNIDAD-' . ($unitIndex + 1);
                            $unitServices = $servicesPerUnit[$unitIndex] ?? [];
                            $unitServiceIds = array_map(fn($service) => $service->id, $unitServices);
                        @endphp
                        <div class="mt-4" data-unit-index="{{ $unitIndex }}">
                            <h4>Unidad {{ $unitIndex + 1 }} ({{ $quote->quote_id }})</h4>
                            <div class="form-group">
                                <label>Servicios Asociados a la Unidad {{ $unitIndex + 1 }}:</label>
                                @if (empty($unitServices))
                                    <p class="text-muted">No hay servicios asignados a esta unidad.</p>
                                @else
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($unitServices as $quoteService)
                                                <tr>
                                                    <td>
                                                        @if ($quoteService->service_id)
                                                            {{ $quoteService->service->descripcion ?? 'Servicio no encontrado' }}
                                                        @elseif ($quoteService->service_package_id)
                                                            {{ $quoteService->servicePackage->name ?? 'Paquete no encontrado' }}
                                                        @else
                                                            Detalles no disponibles
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $quoteService->quantity ?? 'N/A' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="item_code_{{ $unitIndex }}">Código del Ítem para Unidad {{ $unitIndex + 1 }}:</label>
                                <input type="text" name="item_codes[{{ $unitIndex }}]" id="item_code_{{ $unitIndex }}" class="form-control" value="{{ old('item_codes.' . $unitIndex, $suggestedItemCode) }}" required>
                                @error('item_codes.' . $unitIndex)
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="descripcion_{{ $unitIndex }}">Descripción para Unidad {{ $unitIndex + 1 }}:</label>
                                <textarea name="descriptions[{{ $unitIndex }}]" id="descripcion_{{ $unitIndex }}" class="form-control" rows="3">{{ old('descriptions.' . $unitIndex, $suggestedDescription) }}</textarea>
                                @error('descriptions.' . $unitIndex)
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <input type="hidden" name="services[{{ $unitIndex }}]" value="{{ json_encode($unitServiceIds) }}" />
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-success mt-4">Iniciar Procesos para Todas las Unidades</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection 