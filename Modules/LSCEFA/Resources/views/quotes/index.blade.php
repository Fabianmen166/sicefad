@extends('lscefa::layouts.master')
@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Gestión de Cotizaciones</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="card-subtitle text-muted">Listado completo de cotizaciones registradas</h6>
                <a href="{{ route('lscefa.quality.quotes.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle mr-2"></i>Nueva Cotización
                </a>
            </div>
            <form action="{{ route('lscefa.quality.quotes.index') }}" method="GET" class="form-inline mb-3">
                <input type="text" name="search" class="form-control mr-2" placeholder="Buscar por ID, NIT o solicitante" value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Buscar</button>
                @if(request('search'))
                    <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-secondary ml-2">Limpiar</a>
                @endif
            </form>

            @if (count($quotes) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" class="text-nowrap">ID</th>
                                <th scope="col" class="text-nowrap">Cliente (NIT)</th>
                                <th scope="col" class="text-nowrap">Solicitante</th>
                                <th scope="col" class="text-nowrap">Tipo de Cliente</th>
                                <th scope="col" class="text-nowrap text-right">Total</th>
                                <th scope="col" class="text-nowrap text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td class="align-middle">{{ $quote->quote_id ?? 'N/A' }}</td>
                                    <td class="align-middle">{{ $quote->customer->tax_id ?? 'N/A' }}</td>
                                    <td class="align-middle">{{ $quote->customer->applicant ?? 'N/A' }}</td>
                                    <td class="align-middle">{{ $quote->customer->customerType->name ?? 'N/A' }}</td>
                                    <td class="align-middle text-right">${{ number_format($quote->total, 2) }}</td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group" role="group">
                                            <button id="actionDropdown{{ $quote->quote_id }}" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionDropdown{{ $quote->quote_id }}">
                                                <a class="dropdown-item" href="{{ route('lscefa.quality.quotes.show', $quote->quote_id) }}">
                                                    <i class="fas fa-eye mr-2"></i>Ver Detalles
                                                </a>
                                                <a class="dropdown-item" href="{{ route('lscefa.quality.quotes.edit', $quote->quote_id) }}">
                                                    <i class="fas fa-edit mr-2"></i>Editar
                                                </a>
                                                <a class="dropdown-item" href="{{ route('lscefa.quality.quotes.pdf', $quote->quote_id) }}">
                                                    <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                                                </a>
                                                <a class="dropdown-item" href="{{ route('lscefa.quality.quotes.upload.form', $quote->quote_id) }}">
                                                    <i class="fas fa-upload mr-2"></i>Subir Comprobante
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('lscefa.quality.quotes.destroy', $quote->quote_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Está seguro de eliminar esta cotización?')">
                                                        <i class="fas fa-trash-alt mr-2"></i>Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $quotes->links() }}
                </div>
            @else
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>No se encontraron cotizaciones registradas</h5>
                    <p class="mb-0">Puede crear una nueva cotización haciendo clic en el botón superior</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Inicializar tooltips de Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Cerrar dropdowns al hacer clic fuera
    $(document).click(function(e) {
        if (!$(e.target).closest('.btn-group').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });
});
</script>
@endsection