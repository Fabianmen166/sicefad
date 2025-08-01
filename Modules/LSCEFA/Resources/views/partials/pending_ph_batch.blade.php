@php
    $pendingPhAnalyses = [];
    foreach ($processes as $process) {
        $phAnalyses = $process->serviceProcessDetails->where('status', 'pending')->where('service_id', 1);
        foreach ($phAnalyses as $analysis) {
            $pendingPhAnalyses[] = [
                'codigo_item' => $process->item_code,
                'service_id' => $analysis->service_id,
                'analysis_id' => $analysis->id,
                'descripcion' => $analysis->service->descripcion,
                'cantidad' => 1,
            ];
        }
    }
@endphp

@if (empty($pendingPhAnalyses))
    <p>No hay análisis de pH pendientes.</p>
@else
    <form action="{{ route('lscefa.ph_analysis.batch_ph_analysis') }}" method="POST">
        @csrf
        <div class="alert alert-info">
            <p><strong>Instrucciones:</strong> Seleccione hasta 20 análisis de pH para procesar en un solo lote. Después de 20 análisis, se deben repetir los controles analíticos.</p>
        </div>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Código Ítem</th>
                    <th>Servicio</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingPhAnalyses as $index => $phAnalysis)
                    <tr>
                        <td>
                            <input type="checkbox" name="analyses[]" value="{{ $phAnalysis['analysis_id'] }}" class="ph-checkbox">
                        </td>
                        <td>{{ $phAnalysis['codigo_item'] }}</td>
                        <td>{{ $phAnalysis['descripcion'] }}</td>
                        <td>{{ $phAnalysis['cantidad'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary" id="process-batch-btn" disabled>
            Procesar Lote de Análisis de pH
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.ph-checkbox');
            const submitBtn = document.getElementById('process-batch-btn');
            
            function updateSubmitButton() {
                const checkedCount = document.querySelectorAll('.ph-checkbox:checked').length;
                submitBtn.disabled = checkedCount === 0;
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSubmitButton);
            });
            
            updateSubmitButton();
        });
    </script>
@endif 