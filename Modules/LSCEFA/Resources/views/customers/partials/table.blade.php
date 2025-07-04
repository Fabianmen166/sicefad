<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Solicitante</th>
            <th>Contacto</th>
            <th>Teléfono</th>
            <th>NIT</th>
            <th>Correo</th>
            <th>Tipo de Cliente</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->customer_id }}</td>
            <td>{{ $customer->applicant }}</td>
            <td>{{ $customer->contact }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->tax_id }}</td>
            <td>{{ $customer->email }}</td>
            <td>{{ $customer->customerType->name ?? 'N/A' }}</td>
            <td>
                <a href="{{ route('lscefa.quality.customers.edit', $customer->customer_id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('lscefa.quality.customers.destroy', $customer->customer_id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este cliente?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 