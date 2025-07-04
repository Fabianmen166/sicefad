@extends('lscefa::layouts.master')
@section('content')
<div class="container">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <br><br>
        <div class="container">
            <div class="card">
                <h5 class="card-header">Editar Cotización #{{ $quote->quote_id }}</h5>
                <div class="card-body">
                    <form method="POST" action="{{ route('lscefa.quality.quotes.update', $quote->quote_id) }}" id="edit-quote-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="quote_id" class="form-label">ID de la Cotización</label>
                            <input type="text" class="form-control" id="quote_id" name="quote_id" value="{{ old('quote_id', $quote->quote_id) }}" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Cliente</label>
                            <select class="form-control" id="customer_id" name="customer_id" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->customer_id }}" data-discount="{{ $customer->customerType->discount_percentage ?? 0 }}" {{ old('customer_id', $quote->customer_id) == $customer->customer_id ? 'selected' : '' }}>
                                        {{ $customer->applicant }} - {{ $customer->tax_id }} ({{ $customer->customerType->name ?? 'Sin tipo' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Unidades -->
                        <h5>Unidades</h5>
                        <div id="unit-container">
                            @foreach($units as $unitIndex => $unit)
                            <div class="unit-row mb-4 border p-3" data-index="{{ $unitIndex }}">
                                <h6>Unidad {{ $unitIndex + 1 }}</h6>
                                <!-- Servicios de la Unidad -->
                                <h6>Servicios</h6>
                                <div class="service-container">
                                    @foreach($unit['services'] as $serviceIndex => $service)
                                    <div class="service-row mb-3" data-service-index="{{ $serviceIndex }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="units_{{ $unitIndex }}_services_{{ $serviceIndex }}" class="form-label">Servicio</label>
                                                <select class="form-control service-select" name="units[{{ $unitIndex }}][services][{{ $serviceIndex }}][service_id]" id="units_{{ $unitIndex }}_services_{{ $serviceIndex }}">
                                                    <option value="">Seleccione un servicio</option>
                                                    @foreach ($services as $serviceOption)
                                                        <option value="{{ $serviceOption->services_id }}" data-price="{{ $serviceOption->precio }}" {{ $service['service_id'] == $serviceOption->services_id ? 'selected' : '' }}>
                                                            {{ $serviceOption->descripcion }} ({{ $serviceOption->precio }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="units_{{ $unitIndex }}_quantities_{{ $serviceIndex }}" class="form-label">Cantidad</label>
                                                <input type="number" class="form-control quantity" name="units[{{ $unitIndex }}][services][{{ $serviceIndex }}][quantity]" id="units_{{ $unitIndex }}_quantities_{{ $serviceIndex }}" min="1" value="{{ $service['quantity'] }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Subtotal</label>
                                                <input type="text" class="form-control subtotal" readonly value="{{ number_format(($service['service']->precio ?? 0) * $service['quantity'], 2) }}">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger delete-service-btn" {{ $serviceIndex == 0 ? 'style="display: none;"' : '' }}>Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary mb-3 add-service-btn">Agregar Servicio</button>
                                <!-- Paquetes de la Unidad -->
                                <h6>Paquetes de Servicios</h6>
                                <div class="package-container">
                                    @foreach($unit['packages'] as $packageIndex => $package)
                                    <div class="package-row mb-3" data-package-index="{{ $packageIndex }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="units_{{ $unitIndex }}_packages_{{ $packageIndex }}" class="form-label">Paquete</label>
                                                <select class="form-control package-select" name="units[{{ $unitIndex }}][packages][{{ $packageIndex }}][package_id]" id="units_{{ $unitIndex }}_packages_{{ $packageIndex }}">
                                                    <option value="">Seleccione un paquete</option>
                                                    @foreach ($servicePackages as $packageOption)
                                                        <option value="{{ $packageOption->service_package_id }}" data-price="{{ $packageOption->price }}" {{ $package['package_id'] == $packageOption->service_package_id ? 'selected' : '' }}>
                                                            {{ $packageOption->name }} ({{ $packageOption->price }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="units_{{ $unitIndex }}_package_quantities_{{ $packageIndex }}" class="form-label">Cantidad</label>
                                                <input type="number" class="form-control quantity" name="units[{{ $unitIndex }}][packages][{{ $packageIndex }}][quantity]" id="units_{{ $unitIndex }}_package_quantities_{{ $packageIndex }}" min="1" value="{{ $package['quantity'] }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Subtotal</label>
                                                <input type="text" class="form-control subtotal" readonly value="{{ number_format(($package['package']->price ?? 0) * $package['quantity'], 2) }}">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger delete-package-btn" {{ $packageIndex == 0 ? 'style="display: none;"' : '' }}>Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary mb-3 add-package-btn">Agregar Paquete</button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary mb-3" id="add-unit-btn">Agregar Unidad</button>
                        <!-- Total -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Total: <span id="total-amount">{{ number_format($quote->total, 2) }}</span></h5>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-success">Actualizar Cotización</button>
                        <a href="{{ route('lscefa.quality.quotes.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
            <a href="{{ route('lscefa.quality.dashboard') }}" class="back-btn">Volver al Dashboard</a>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div id="services-data" style="display: none;"
     data-services='@json($services)'
     data-service-packages='@json($servicePackages)'></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener datos del DOM
    const servicesData = JSON.parse(document.getElementById('services-data').dataset.services);
    const servicePackages = JSON.parse(document.getElementById('services-data').dataset.servicePackages);
    
    // Variables principales
    const unitContainer = document.getElementById('unit-container');
    const addUnitBtn = document.getElementById('add-unit-btn');
    const totalDisplay = document.getElementById('total-amount');
    let unitIndex = {{ count($units) }};
    let discountPercentage = {{ $quote->customer->customerType->discount_percentage ?? 0 }};

    // Configurar evento para el select de cliente
    document.getElementById('customer_id').addEventListener('change', function(e) {
        const selectedOption = e.target.selectedOptions[0];
        discountPercentage = parseFloat(selectedOption.dataset.discount) || 0;
        calculateTotal();
    });

    // Funciones de cálculo
    function calculateSubtotal(row) {
        const select = row.querySelector('.service-select, .package-select');
        const quantity = row.querySelector('.quantity');
        const subtotal = row.querySelector('.subtotal');
        
        if (!select || !quantity || !subtotal) return 0;
        
        const price = parseFloat(select.selectedOptions[0]?.dataset.price) || 0;
        const qty = parseInt(quantity.value) || 1;
        const subtotalValue = price * qty;
        
        subtotal.value = subtotalValue.toFixed(2);
        return subtotalValue;
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.unit-row').forEach(function(unitRow) {
            unitRow.querySelectorAll('.service-row, .package-row').forEach(function(row) {
                if (row.querySelector('select')?.value) {
                    total += calculateSubtotal(row);
                }
            });
        });
        
        if (discountPercentage > 0) {
            total = total * (1 - discountPercentage / 100);
        }
        
        totalDisplay.textContent = total.toFixed(2);
    }

    // Función para agregar fila de servicio
    function addServiceRow(unitRow, unitIndex, serviceIndex) {
        const serviceContainer = unitRow.querySelector('.service-container');
        const newRow = document.createElement('div');
        newRow.className = 'service-row mb-3';
        newRow.dataset.serviceIndex = serviceIndex;
        
        let options = '';
        servicesData.forEach(function(service) {
            options += `<option value="${service.services_id}" data-price="${service.precio}">${service.descripcion} (${service.precio})</option>`;
        });
        
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label for="units_${unitIndex}_services_${serviceIndex}" class="form-label">Servicio</label>
                    <select class="form-control service-select" name="units[${unitIndex}][services][${serviceIndex}][service_id]" id="units_${unitIndex}_services_${serviceIndex}">
                        <option value="">Seleccione un servicio</option>
                        ${options}
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="units_${unitIndex}_quantities_${serviceIndex}" class="form-label">Cantidad</label>
                    <input type="number" class="form-control quantity" name="units[${unitIndex}][services][${serviceIndex}][quantity]" id="units_${unitIndex}_quantities_${serviceIndex}" min="1" value="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control subtotal" readonly value="0">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger delete-service-btn">Eliminar</button>
                </div>
            </div>
        `;
        
        serviceContainer.appendChild(newRow);
        
        const select = newRow.querySelector('.service-select');
        const quantity = newRow.querySelector('.quantity');
        const deleteBtn = newRow.querySelector('.delete-service-btn');
        
        select.addEventListener('change', function() {
            calculateSubtotal(newRow);
            calculateTotal();
        });
        
        quantity.addEventListener('input', function() {
            calculateSubtotal(newRow);
            calculateTotal();
        });
        
        deleteBtn.addEventListener('click', function() {
            newRow.remove();
            calculateTotal();
        });
        
        calculateSubtotal(newRow);
        calculateTotal();
    }

    // Función para agregar fila de paquete
    function addPackageRow(unitRow, unitIndex, packageIndex) {
        const packageContainer = unitRow.querySelector('.package-container');
        const newRow = document.createElement('div');
        newRow.className = 'package-row mb-3';
        newRow.dataset.packageIndex = packageIndex;
        
        let options = '';
        servicePackages.forEach(function(pkg) {
            options += `<option value="${pkg.service_package_id}" data-price="${pkg.price}">${pkg.name} (${pkg.price})</option>`;
        });
        
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label for="units_${unitIndex}_packages_${packageIndex}" class="form-label">Paquete</label>
                    <select class="form-control package-select" name="units[${unitIndex}][packages][${packageIndex}][package_id]" id="units_${unitIndex}_packages_${packageIndex}">
                        <option value="">Seleccione un paquete</option>
                        ${options}
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="units_${unitIndex}_package_quantities_${packageIndex}" class="form-label">Cantidad</label>
                    <input type="number" class="form-control quantity" name="units[${unitIndex}][packages][${packageIndex}][quantity]" id="units_${unitIndex}_package_quantities_${packageIndex}" min="1" value="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control subtotal" readonly value="0">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger delete-package-btn">Eliminar</button>
                </div>
            </div>
        `;
        
        packageContainer.appendChild(newRow);
        
        const select = newRow.querySelector('.package-select');
        const quantity = newRow.querySelector('.quantity');
        const deleteBtn = newRow.querySelector('.delete-package-btn');
        
        select.addEventListener('change', function() {
            calculateSubtotal(newRow);
            calculateTotal();
        });
        
        quantity.addEventListener('input', function() {
            calculateSubtotal(newRow);
            calculateTotal();
        });
        
        deleteBtn.addEventListener('click', function() {
            newRow.remove();
            calculateTotal();
        });
        
        calculateSubtotal(newRow);
        calculateTotal();
    }

    // Agregar nueva unidad
    addUnitBtn.addEventListener('click', function() {
        const newUnitRow = document.createElement('div');
        newUnitRow.className = 'unit-row mb-4 border p-3';
        newUnitRow.dataset.index = unitIndex;
        
        let serviceOptions = '';
        servicesData.forEach(function(service) {
            serviceOptions += `<option value="${service.services_id}" data-price="${service.precio}">${service.descripcion} (${service.precio})</option>`;
        });
        
        let packageOptions = '';
        servicePackages.forEach(function(pkg) {
            packageOptions += `<option value="${pkg.service_package_id}" data-price="${pkg.price}">${pkg.name} (${pkg.price})</option>`;
        });
        
        newUnitRow.innerHTML = `
            <h6>Unidad ${unitIndex + 1}</h6>
            <h6>Servicios</h6>
            <div class="service-container">
                <div class="service-row mb-3" data-service-index="0">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="units_${unitIndex}_services_0" class="form-label">Servicio</label>
                            <select class="form-control service-select" name="units[${unitIndex}][services][0][service_id]" id="units_${unitIndex}_services_0">
                                <option value="">Seleccione un servicio</option>
                                ${serviceOptions}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="units_${unitIndex}_quantities_0" class="form-label">Cantidad</label>
                            <input type="number" class="form-control quantity" name="units[${unitIndex}][services][0][quantity]" id="units_${unitIndex}_quantities_0" min="1" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control subtotal" readonly value="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger delete-service-btn" style="display: none;">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3 add-service-btn">Agregar Servicio</button>
            <h6>Paquetes de Servicios</h6>
            <div class="package-container">
                <div class="package-row mb-3" data-package-index="0">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="units_${unitIndex}_packages_0" class="form-label">Paquete</label>
                            <select class="form-control package-select" name="units[${unitIndex}][packages][0][package_id]" id="units_${unitIndex}_packages_0">
                                <option value="">Seleccione un paquete</option>
                                ${packageOptions}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="units_${unitIndex}_package_quantities_0" class="form-label">Cantidad</label>
                            <input type="number" class="form-control quantity" name="units[${unitIndex}][packages][0][quantity]" id="units_${unitIndex}_package_quantities_0" min="1" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control subtotal" readonly value="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger delete-package-btn" style="display: none;">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3 add-package-btn">Agregar Paquete</button>
        `;
        
        unitContainer.appendChild(newUnitRow);
        
        // Configurar botones para la nueva unidad
        const addServiceBtn = newUnitRow.querySelector('.add-service-btn');
        const addPackageBtn = newUnitRow.querySelector('.add-package-btn');
        let serviceIndex = 1;
        let packageIndex = 1;
        
        addServiceBtn.addEventListener('click', function() {
            addServiceRow(newUnitRow, unitIndex, serviceIndex);
            serviceIndex++;
        });
        
        addPackageBtn.addEventListener('click', function() {
            addPackageRow(newUnitRow, unitIndex, packageIndex);
            packageIndex++;
        });
        
        // Configurar la primera fila de servicio
        const serviceRow = newUnitRow.querySelector('.service-row');
        const serviceSelect = serviceRow.querySelector('.service-select');
        const serviceQuantity = serviceRow.querySelector('.quantity');
        
        serviceSelect.addEventListener('change', function() {
            calculateSubtotal(serviceRow);
            calculateTotal();
        });
        
        serviceQuantity.addEventListener('input', function() {
            calculateSubtotal(serviceRow);
            calculateTotal();
        });
        
        // Configurar la primera fila de paquete
        const packageRow = newUnitRow.querySelector('.package-row');
        const packageSelect = packageRow.querySelector('.package-select');
        const packageQuantity = packageRow.querySelector('.quantity');
        
        packageSelect.addEventListener('change', function() {
            calculateSubtotal(packageRow);
            calculateTotal();
        });
        
        packageQuantity.addEventListener('input', function() {
            calculateSubtotal(packageRow);
            calculateTotal();
        });
        
        unitIndex++;
        calculateTotal();
    });

    // Configurar unidades existentes
    const unitRows = unitContainer.querySelectorAll('.unit-row');
    unitRows.forEach(function(unitRow, index) {
        const addServiceBtn = unitRow.querySelector('.add-service-btn');
        const addPackageBtn = unitRow.querySelector('.add-package-btn');
        let serviceIndex = unitRow.querySelectorAll('.service-row').length;
        let packageIndex = unitRow.querySelectorAll('.package-row').length;
        
        addServiceBtn.addEventListener('click', function() {
            addServiceRow(unitRow, index, serviceIndex);
            serviceIndex++;
        });
        
        addPackageBtn.addEventListener('click', function() {
            addPackageRow(unitRow, index, packageIndex);
            packageIndex++;
        });
        
        // Configurar filas de servicio existentes
        const serviceRows = unitRow.querySelectorAll('.service-row');
        serviceRows.forEach(function(serviceRow) {
            const select = serviceRow.querySelector('.service-select');
            const quantity = serviceRow.querySelector('.quantity');
            const deleteBtn = serviceRow.querySelector('.delete-service-btn');
            
            if (select) {
                select.addEventListener('change', function() {
                    calculateSubtotal(serviceRow);
                    calculateTotal();
                });
            }
            
            if (quantity) {
                quantity.addEventListener('input', function() {
                    calculateSubtotal(serviceRow);
                    calculateTotal();
                });
            }
            
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    serviceRow.remove();
                    calculateTotal();
                });
            }
        });
        
        // Configurar filas de paquete existentes
        const packageRows = unitRow.querySelectorAll('.package-row');
        packageRows.forEach(function(packageRow) {
            const select = packageRow.querySelector('.package-select');
            const quantity = packageRow.querySelector('.quantity');
            const deleteBtn = packageRow.querySelector('.delete-package-btn');
            
            if (select) {
                select.addEventListener('change', function() {
                    calculateSubtotal(packageRow);
                    calculateTotal();
                });
            }
            
            if (quantity) {
                quantity.addEventListener('input', function() {
                    calculateSubtotal(packageRow);
                    calculateTotal();
                });
            }
            
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    packageRow.remove();
                    calculateTotal();
                });
            }
        });
    });

    // Calcular total inicial
    calculateTotal();
});
</script>
@endpush
@endsection