@php
    $pendingConductivityAnalyses = [];
    foreach ($processes as $process) {
        $conductivityAnalyses = $process->serviceProcessDetails->where('status', 'pending')->where('service_id', 2);
        foreach ($conductivityAnalyses as $analysis) {
            $pendingConductivityAnalyses[] = [
                'codigo_item' => $process->item_code,
                'service_id' => $analysis->service_id,
                'analysis_id' => $analysis->id,
                'descripcion' => $analysis->service ? $analysis->service->descripcion : 'Servicio no encontrado',
                'cantidad' => 1,
            ];
        }
    }
@endphp

@if (empty($pendingConductivityAnalyses))
    <p>No hay análisis de conductividad pendientes.</p>
@else
    <form action="{{ route('lscefa.conductivity_analysis.batch_conductivity_analysis') }}" method="POST">
        @csrf
        <div class="alert alert-info">
            <p><strong>Instrucciones:</strong> Seleccione hasta 20 análisis de conductividad para procesar en un solo lote. Después de 20 análisis, se deben repetir los controles analíticos.</p>
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
                @foreach ($pendingConductivityAnalyses as $index => $conductivityAnalysis)
                    <tr>
                        <td>
                            <input type="checkbox" name="analyses[]" value="{{ $conductivityAnalysis['analysis_id'] }}" class="conductivity-checkbox">
                        </td>
                        <td>{{ $conductivityAnalysis['codigo_item'] }}</td>
                        <td>{{ $conductivityAnalysis['descripcion'] }}</td>
                        <td>{{ $conductivityAnalysis['cantidad'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary" id="process-batch-btn" disabled>
            Procesar Lote de Análisis de Conductividad
        </button>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.conductivity-checkbox');
            const submitButton = document.getElementById('process-batch-btn');

            function updateButtonState() {
                const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                if (checkedCount > 0 && checkedCount <= 20) {
                    submitButton.disabled = false;
                } else {
                    submitButton.disabled = true;
                }

                if (checkedCount > 20) {
                    alert('No puede seleccionar más de 20 análisis de conductividad a la vez.');
                    Array.from(checkboxes).filter(cb => cb.checked).slice(20).forEach(cb => cb.checked = false);
                }
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateButtonState));
        });
    </script>
@endif 