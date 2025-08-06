@php
    $pendingPhAnalyses = [];
    foreach ($processes as $process) {
        // Filtrar servicios que contengan 'ph' en la descripción
        $phAnalyses = $process->serviceProcessDetails->filter(function($detail) {
            return $detail->status === 'pending' && 
                   $detail->service && 
                   stripos($detail->service->descripcion, 'ph') !== false;
        });
        
        foreach ($phAnalyses as $analysis) {
            $pendingPhAnalyses[] = [
                'codigo_item' => $process->item_code ?? 'N/A',
                'service_id' => $analysis->service_id,
                'analysis_id' => $analysis->id,
                'descripcion' => $analysis->service->descripcion ?? 'Servicio sin descripción',
                'cantidad' => 1,
            ];
        }
    }
    
    // Agregar logs para depuración
    \Illuminate\Support\Facades\Log::info('Análisis de pH pendientes encontrados:', [
        'total' => count($pendingPhAnalyses),
        'analisis' => $pendingPhAnalyses
    ]);
@endphp

@if (empty($pendingPhAnalyses))
    <div class="alert alert-info m-4">No hay análisis de pH pendientes.</div>
@else
    <form action="{{ route('lscefa.ph_analysis.batch_ph_analysis') }}" method="POST">
        @csrf
        <div class="alert alert-info m-4">
            <p class="mb-0"><strong>Instrucciones:</strong> Seleccione hasta 20 análisis de pH para procesar en un solo lote. Después de 20 análisis, se deben repetir los controles analíticos.</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th>Código Ítem</th>
                        <th>Servicio</th>
                        <th class="text-center">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingPhAnalyses as $index => $phAnalysis)
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" 
                                       name="analyses[]" 
                                       value="{{ $phAnalysis['analysis_id'] }}" 
                                       class="form-check-input ph-checkbox"
                                       data-item-code="{{ $phAnalysis['codigo_item'] }}">
                            </td>
                            <td class="align-middle">{{ $phAnalysis['codigo_item'] }}</td>
                            <td class="align-middle">{{ $phAnalysis['descripcion'] }}</td>
                            <td class="text-center align-middle">{{ $phAnalysis['cantidad'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-top">
            <button type="submit" class="btn btn-primary" id="process-batch-btn" disabled>
                <i class="fas fa-tasks mr-1"></i> Procesar Lote de Análisis de pH
            </button>
            <span id="selected-count" class="text-muted ml-3">0 análisis seleccionados</span>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.ph-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');
            const submitBtn = document.getElementById('process-batch-btn');
            const selectedCountSpan = document.getElementById('selected-count');
            const maxSelections = 20;
            
            // Actualizar contador y estado del botón
            function updateSelectionUI() {
                const checkedCount = document.querySelectorAll('.ph-checkbox:checked').length;
                const isMaxReached = checkedCount >= maxSelections;
                
                // Actualizar contador
                selectedCountSpan.textContent = `${checkedCount} de ${maxSelections} análisis seleccionados`;
                
                // Actualizar estado del botón
                submitBtn.disabled = checkedCount === 0;
                
                // Deshabilitar checkboxes no seleccionados si se alcanzó el máximo
                checkboxes.forEach(checkbox => {
                    if (isMaxReached && !checkbox.checked) {
                        checkbox.disabled = true;
                    } else {
                        checkbox.disabled = false;
                    }
                });
                
                // Actualizar estado del checkbox "Seleccionar todos"
                if (checkedCount === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else if (checkedCount === checkboxes.length) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    selectAllCheckbox.indeterminate = true;
                }
            }
            
            // Manejar clic en checkbox individual
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectionUI);
            });
            
            // Manejar clic en "Seleccionar todos"
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                checkboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = isChecked;
                    }
                });
                updateSelectionUI();
            });
            
            // Inicializar la interfaz
            updateSelectionUI();
            
            // Mostrar notificación si se intenta seleccionar más del máximo
            submitBtn.addEventListener('click', function(e) {
                const checkedCount = document.querySelectorAll('.ph-checkbox:checked').length;
                if (checkedCount > maxSelections) {
                    e.preventDefault();
                    alert(`Por favor seleccione un máximo de ${maxSelections} análisis por lote.`);
                }
            });
        });
    </script>
@endif