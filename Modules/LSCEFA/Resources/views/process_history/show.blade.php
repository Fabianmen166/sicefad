@extends('lscefa::layouts.master')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Historial del Proceso {{ $process->process_id }}</h4>
    <a href="{{ route('lscefa.quality.processes.index') }}" class="btn btn-secondary">Volver</a>
  </div>

  {{-- Resumen de cabecera --}}
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
            <dt class="col-sm-5">Estado</dt>
            <dd class="col-sm-7 text-capitalize">{{ $process->status }}</dd>
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
    </div>
  </div>

  {{-- Comunicaciones --}}
  <div class="card shadow-sm mb-3">
    <div class="card-header">Comunicaciones</div>
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">Comunicación con cliente</dt>
        <dd class="col-sm-9">{{ $process->client_communication ?? 'N/D' }}</dd>
        <dt class="col-sm-3">Archivo de comunicación</dt>
        <dd class="col-sm-9">
          @if($process->communication_file)
            <span class="mr-2">{{ $process->communication_file }}</span>
          @else
            <span class="text-muted">No adjunto</span>
          @endif
        </dd>
      </dl>
    </div>
  </div>

  {{-- Servicios y análisis --}}
  <div class="card shadow-sm mb-3">
    <div class="card-header">Servicios y estados de análisis</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Servicio</th>
              <th>Estado SPD</th>
              <th>Resultado</th>
              <th>pH</th>
              <th>Conductividad</th>
              <th>Fósforo</th>
            </tr>
          </thead>
          <tbody>
            @forelse($services as $s)
              <tr>
                <td>{{ $s['description'] }}</td>
                <td class="text-capitalize">{{ $s['status'] }}</td>
                <td>{{ is_array($s['result']) ? json_encode($s['result']) : $s['result'] }}</td>
                <td>
                  @if($s['ph'])
                    <div><small>Fecha:</small> {{ $s['ph']['fecha_analisis'] }}</div>
                    <div><small>Revisión:</small> {{ $s['ph']['review_status'] ?? 'N/D' }}</div>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  @if($s['conductivity'])
                    <div><small>Fecha:</small> {{ $s['conductivity']['fecha_analisis'] }}</div>
                    <div><small>Revisión:</small> {{ $s['conductivity']['review_status'] ?? 'N/D' }}</div>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  @if($s['phosphorus'])
                    <div><small>Fecha:</small> {{ $s['phosphorus']['fecha_analisis'] }}</div>
                    <div><small>Revisión:</small> {{ $s['phosphorus']['review_status'] ?? 'N/D' }}</div>
                    <div><small>mg/kg:</small> {{ $s['phosphorus']['fosforo_disponible_mg_kg'] }}</div>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">Sin servicios.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Informe previsualización --}}
  <div class="card shadow-sm mb-3">
    <div class="card-header">Informe (vista previa)</div>
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
@endsection
