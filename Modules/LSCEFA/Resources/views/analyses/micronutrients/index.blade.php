@extends('lscefa::layouts.technical')

@section('title', 'Gestión de Análisis de Micronutrientes')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Micronutrientes</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Análisis de Micronutrientes</li>
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

            <div class="row mb-3">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary" id="processSelectedBtn" style="display: none;">Procesar Seleccionados</button>
                    <button type="button" class="btn btn-secondary" id="clearSelectionBtn" style="display: none;">Limpiar Selección</button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis Pendientes</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID Proceso</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processes as $process)
                                    @php
                                        $micronutrientsService = $process->serviceProcessDetails->filter(function($detail) {
                                            return str_contains(strtolower($detail->service->descripcion), 'micronutrientes') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'micronutrients') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'zinc') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'hierro') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'manganeso') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'cobre') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'boro');
                                        })->first();
                                    @endphp
                                    @if($micronutrientsService && $micronutrientsService->status === 'pending')
                                        <tr data-process-id="{{ $process->process_id }}" data-service-type="micronutrients">
                                            <td><input type="checkbox" class="process-checkbox" value="{{ $process->process_id }}"></td>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $micronutrientsService->service->descripcion ?? 'Análisis de Micronutrientes' }}</td>
                                            <td><span class="badge badge-warning">Pendiente</span></td>
                                            <td>
                                                <a href="{{ route('lscefa.technical.analyses.micronutrients.process', ['processId' => $process->process_id, 'serviceId' => $micronutrientsService->service_id]) }}"
                                                   class="btn btn-primary btn-sm">
                                                    Procesar Análisis
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr><td colspan="5" class="text-center">No hay análisis pendientes</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const MAX_PROCESSES = 10;
    
    // Select all functionality
    $('#selectAll').change(function() {
        var totalCheckboxes = $('.process-checkbox').length;
        if ($(this).is(':checked') && totalCheckboxes > MAX_PROCESSES) {
            alert('No se pueden seleccionar más de ' + MAX_PROCESSES + ' procesos a la vez. Por favor, selecciona menos procesos.');
            $(this).prop('checked', false);
            return;
        }
        $('.process-checkbox').prop('checked', $(this).is(':checked'));
        updateButtonVisibility();
    });

    // Individual checkbox change
    $('.process-checkbox').change(function() {
        var checkedCount = $('.process-checkbox:checked').length;
        
        if (checkedCount > MAX_PROCESSES) {
            alert('No se pueden seleccionar más de ' + MAX_PROCESSES + ' procesos a la vez. Por favor, deselecciona algunos procesos.');
            $(this).prop('checked', false);
            return;
        }
        
        updateButtonVisibility();
        updateSelectAllState();
    });

    // Process selected button
    $('#processSelectedBtn').click(function() {
        var selectedProcesses = $('.process-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedProcesses.length === 0) {
            alert('Por favor, selecciona al menos un proceso para procesar.');
            return;
        }
        
        if (selectedProcesses.length > MAX_PROCESSES) {
            alert('No se pueden procesar más de ' + MAX_PROCESSES + ' procesos a la vez. Por favor, selecciona menos procesos.');
            return;
        }
        
        var batchUrl = "{{ route('lscefa.technical.analyses.micronutrients.batch') }}?processes=" + selectedProcesses.join(',');
        window.location.href = batchUrl;
    });

    // Clear selection button
    $('#clearSelectionBtn').click(function() {
        $('.process-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateButtonVisibility();
    });

    function updateButtonVisibility() {
        var checkedCount = $('.process-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#processSelectedBtn').show();
            $('#clearSelectionBtn').show();
            
            // Mostrar contador de procesos seleccionados
            if (checkedCount > MAX_PROCESSES) {
                $('#processSelectedBtn').prop('disabled', true).text('Procesar Seleccionados (' + checkedCount + '/' + MAX_PROCESSES + ')');
            } else {
                $('#processSelectedBtn').prop('disabled', false).text('Procesar Seleccionados (' + checkedCount + ')');
            }
        } else {
            $('#processSelectedBtn').hide();
            $('#clearSelectionBtn').hide();
        }
    }

    function updateSelectAllState() {
        var totalCheckboxes = $('.process-checkbox').length;
        var checkedCheckboxes = $('.process-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    }

    // Initial state
    updateButtonVisibility();
});
</script>
@endpush
