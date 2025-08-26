@extends('lscefa::layouts.master')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">Historial del Proceso {{ $process->process_id }}</h4>
      <div class="small text-muted">Ítem: {{ $process->item_code }} · Estado: <span class="badge badge-{{ $process->status === 'completed' ? 'success' : ($process->status === 'in_progress' ? 'info' : 'secondary') }} text-capitalize">{{ $process->status }}</span></div>
    </div>
    <div class="btn-group">
      <a href="{{ route('lscefa.quality.reports.show', $process->process_id) }}" class="btn btn-outline-primary btn-sm">Ver Informe</a>
      <a href="{{ route('lscefa.quality.reports.pdf', $process->process_id) }}" class="btn btn-primary btn-sm" target="_blank">Descargar PDF</a>
      <a href="{{ route('lscefa.quality.processes.index') }}" class="btn btn-secondary btn-sm">Volver</a>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="tab-resumen" data-toggle="tab" href="#pane-resumen" role="tab">Resumen</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="tab-informe" data-toggle="tab" href="#pane-informe" role="tab">Informe</a>
    </li>
  </ul>
  <div class="tab-content pt-3">
    {{-- Pestaña: Resumen --}}
    <div class="tab-pane fade show active" id="pane-resumen" role="tabpanel" aria-labelledby="tab-resumen">
      <div class="row">
        <div class="col-md-6">
          <div class="card shadow-sm mb-3">
            <div class="card-header">Información del Proceso</div>
            <div class="card-body">
              <dl class="row mb-0">
                <dt class="col-sm-5">Código de Ítem</dt>
                <dd class="col-sm-7">{{ $process->item_code }}</dd>
                <dt class="col-sm-5">Descripción</dt>
                <dd class="col-sm-7">{{ $process->description }}</dd>
                <dt class="col-sm-5">Recepción</dt>
                <dd class="col-sm-7">{{ $process->reception_date }}</dd>
                <dt class="col-sm-5">Entrega</dt>
                <dd class="col-sm-7">{{ $process->delivery_date }}</dd>
                <dt class="col-sm-5">Lugar muestreo</dt>
                <dd class="col-sm-7">{{ $process->sampling_place }}</dd>
                <dt class="col-sm-5">Fecha muestreo</dt>
                <dd class="col-sm-7">{{ $process->sampling_date }}</dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm mb-3">
            <div class="card-header">Cotización</div>
            <div class="card-body">
              @if($quote)
              <dl class="row mb-0">
                <dt class="col-sm-5"># Cotización</dt>
                <dd class="col-sm-7">{{ $quote->quote_id }}</dd>
                <dt class="col-sm-5">Cliente</dt>
                <dd class="col-sm-7">{{ $customer->applicant ?? ($quote->customer->applicant ?? 'N/D') }}</dd>
                <dt class="col-sm-5">Tipo Cliente</dt>
                <dd class="col-sm-7">{{ $quote->customer->customerType->name ?? 'N/D' }}</dd>
                <dt class="col-sm-5">Total</dt>
                <dd class="col-sm-7">${{ number_format($quote->total ?? 0, 0, ',', '.') }}</dd>
              </dl>
              <a class="btn btn-outline-primary btn-sm mt-2" href="{{ route('lscefa.quality.quotes.show', $quote->quote_id) }}">Ver Cotización</a>
              @else
              <div class="text-muted">Sin cotización asociada.</div>
              @endif
            </div>
          </div>

          {{-- Comunicaciones --}}
          <div class="card shadow-sm mb-3">
            <div class="card-header">Comunicaciones</div>
            <div class="card-body">
              <dl class="row mb-0">
                <dt class="col-sm-4">Comunicación con cliente</dt>
                <dd class="col-sm-8">{{ $process->client_communication ?? 'N/D' }}</dd>
                <dt class="col-sm-4">Archivo de comunicación</dt>
                <dd class="col-sm-8">
                  @if($process->communication_file)
                    <span class="mr-2">{{ $process->communication_file }}</span>
                  @else
                    <span class="text-muted">No adjunto</span>
                  @endif
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    

    {{-- Pestaña: Informe --}}
    <div class="tab-pane fade" id="pane-informe" role="tabpanel" aria-labelledby="tab-informe">
      <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Informe (vista previa)</span>
          <div>
            <a href="{{ route('lscefa.quality.reports.show', $process->process_id) }}" class="btn btn-outline-primary btn-sm">Abrir en módulo de informes</a>
            <a href="{{ route('lscefa.quality.reports.pdf', $process->process_id) }}" class="btn btn-primary btn-sm" target="_blank">Descargar PDF</a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead>
                <tr>
                  <th>Ensayo</th>
                  <th>Resultado</th>
                  <th>Unidad</th>
                  <th>Técnica</th>
                  <th>Documento</th>
                  <th>Fecha análisis</th>
                </tr>
              </thead>
              <tbody>
                @forelse($reportRows as $r)
                  <tr>
                    <td>{{ $r['ensayo'] ?? '' }}</td>
                    <td>{{ $r['resultado'] ?? '' }}</td>
                    <td>{{ $r['unidad'] ?? '' }}</td>
                    <td>{{ $r['tecnica'] ?? '' }}</td>
                    <td>{{ $r['documento'] ?? '' }}</td>
                    <td>{{ $r['fecha_analisis'] ?? '' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-muted">Sin datos de informe.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
