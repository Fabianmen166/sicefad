@php
    $pendingConductivityAnalyses = [];
    foreach ($processes as $process) {
        // Filtrar servicios que contengan 'conductividad' en la descripción
        $conductivityAnalyses = $process->serviceProcessDetails->filter(function($detail) {
            return $detail->status === 'pending' && 
                   $detail->service && 
                   stripos($detail->service->descripcion, 'conductividad') !== false;
        });
        
        foreach ($conductivityAnalyses as $analysis) {
            $pendingConductivityAnalyses[] = [
                'codigo_item' => $process->item_code ?? 'N/A',
                'service_id' => $analysis->service_id,
                'analysis_id' => $analysis->id,
                'descripcion' => $analysis->service->descripcion ?? 'Servicio sin descripción',
                'cantidad' => 1,
            ];
        }
    }
    
    // Agregar logs para depuración
    \Illuminate\Support\Facades\Log::info('Análisis de conductividad pendientes encontrados:', [
        'total' => count($pendingConductivityAnalyses),
        'analisis' => $pendingConductivityAnalyses
    ]);
@endphp

@if (empty($pendingConductivityAnalyses))
    <div class="alert alert-info m-4">No hay análisis de conductividad pendientes.</div>
@else
    <form action="{{ route('lscefa.conductivity_analysis.batch_conductivity_analysis') }}" method="POST">
        @csrf
        <div class="alert alert-info m-4">
            <p class="mb-0"><strong>Instrucciones:</strong> Seleccione hasta 20 análisis de conductividad para procesar en un solo lote. Después de 20 análisis, se deben repetir los controles analíticos.</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">
                            <input type="checkbox" id="select-all-conductivity" class="form-check-input">
                        </th>
                        <th>Código Ítem</th>
                        <th>Servicio</th>
                        <th class="text-center">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingConductivityAnalyses as $index => $analysis)
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" 
                                       name="analyses[]" 
                                       value="{{ $analysis['analysis_id'] }}" 
                                       class="form-check-input conductivity-checkbox"
                                       data-item-code="{{ $analysis['codigo_item'] }}"
                                       {{ $index >= 20 ? 'disabled' : '' }}>
                            </td>
                            <td class="align-middle">{{ $analysis['codigo_item'] }}</td>
                            <td class="align-middle">{{ $analysis['descripcion'] }}</td>
                            <td class="text-center align-middle">{{ $analysis['cantidad'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary" id="process-batch-btn">
                <i class="fas fa-flask"></i> Procesar análisis seleccionados
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.conductivity-checkbox');
            const selectAllCheckbox = document.getElementById('select-all-conductivity');
            const submitBtn = document.getElementById('process-batch-btn');
            const maxSelections = 20;
            let selectedCount = 0;

            // Actualizar contador de seleccionados
            function updateSelectedCount() {
                selectedCount = document.querySelectorAll('.conductivity-checkbox:checked').length;
                document.getElementById('selected-count').textContent = selectedCount;
                
                // Deshabilitar checkboxes no seleccionados cuando se alcanza el máximo
                if (selectedCount >= maxSelections) {
                    checkboxes.forEach(checkbox => {
                        if (!checkbox.checked) {
                            checkbox.disabled = true;
                        }
                    });
                } else {
                    checkboxes.forEach(checkbox => {
                        if (!checkbox.hasAttribute('data-disabled')) {
                            checkbox.disabled = false;
                        }
                    });
                }
            }

            // Manejar selección/deselección de todos
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkableCheckboxes = Array.from(checkboxes).filter(cb => !cb.disabled);
                    const allChecked = checkableCheckboxes.every(cb => cb.checked);
                    
                    checkableCheckboxes.forEach(checkbox => {
                        if (selectedCount < maxSelections || allChecked) {
                            checkbox.checked = !allChecked;
                        }
                    });
                    
                    updateSelectedCount();
                });
            }

            // Actualizar contador al cambiar selección
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Validar envío del formulario
            if (submitBtn) {
                const form = submitBtn.closest('form');
                form.addEventListener('submit', function(e) {
                    const selected = document.querySelectorAll('.conductivity-checkbox:checked').length;
                    if (selected === 0) {
                        e.preventDefault();
                        alert('Por favor seleccione al menos un análisis para procesar.');
                    } else if (selected > maxSelections) {
                        e.preventDefault();
                        alert(`No puede seleccionar más de ${maxSelections} análisis a la vez.`);
                    }
                });
            }

            // Inicializar contador
            updateSelectedCount();
        });
    </script>
    @endpush
@endif