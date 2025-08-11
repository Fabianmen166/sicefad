@extends('lscefa::layouts.technical')

@section('title', 'Gestión de Análisis de Bases Cambiables')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Bases Cambiables</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('lscefa.technical.analyses.index') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Análisis de Bases Cambiables</li>
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
                                        $exchangeableBasesService = $process->serviceProcessDetails->filter(function($detail) {
                                            return str_contains(strtolower($detail->service->descripcion), 'bases cambiables') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'exchangeable bases') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'cationic exchange') ||
                                                   str_contains(strtolower($detail->service->descripcion), 'intercambio cationico');
                                        })->first();
                                    @endphp
                                    @if($exchangeableBasesService && $exchangeableBasesService->status === 'pending')
                                        <tr data-process-id="{{ $process->process_id }}" data-service-type="exchangeable_bases">
                                            <td><input type="checkbox" class="process-checkbox" value="{{ $process->process_id }}"></td>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $exchangeableBasesService->service->descripcion ?? 'Análisis de Bases Cambiables' }}</td>
                                            <td><span class="badge badge-warning">Pendiente</span></td>
                                            <td>
                                                <a href="{{ route('lscefa.technical.analyses.exchangeable_bases.process', ['processId' => $process->process_id, 'serviceId' => $exchangeableBasesService->service_id]) }}"
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
        function updateProcessButtonState() {
            var checkedCount = $('.process-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#processSelectedBtn').show();
                $('#clearSelectionBtn').show();
            } else {
                $('#processSelectedBtn').hide();
                $('#clearSelectionBtn').hide();
            }
        }

        $('#selectAll').on('change', function() {
            $('.process-checkbox').prop('checked', $(this).prop('checked'));
            updateProcessButtonState();
        });

        $('.process-checkbox').on('change', function() {
            var allChecked = $('.process-checkbox:checked').length === $('.process-checkbox').length;
            $('#selectAll').prop('checked', allChecked);
            updateProcessButtonState();
        });

        $('#clearSelectionBtn').on('click', function() {
            $('.process-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateProcessButtonState();
        });

        $('#processSelectedBtn').on('click', function() {
            var selectedProcesses = [];
            $('.process-checkbox:checked').each(function() {
                selectedProcesses.push($(this).val());
            });

            if (selectedProcesses.length === 0) {
                alert('Por favor, selecciona al menos un proceso para procesar.');
                return;
            }

            // Crear un formulario temporal para enviar los IDs de procesos seleccionados
            var form = $('<form>', {
                'method': 'POST',
                'action': '{{ route('lscefa.technical.analyses.exchangeable_bases.batch.post') }}'
            });

            // Agregar el token CSRF
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            }));

            // Agregar cada ID de proceso seleccionado
            selectedProcesses.forEach(function(processId) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'process_ids[]',
                    'value': processId
                }));
            });

            // Agregar el formulario al DOM y enviarlo
            $('body').append(form);
            form.submit();
        });

        updateProcessButtonState(); // Initial state on page load
    });
</script>
@endpush 