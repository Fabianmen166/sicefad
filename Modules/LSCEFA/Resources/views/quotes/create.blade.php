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
                <h5 class="card-header">Crear Nueva Cotización</h5>
                <div class="card-body">
                    <form method="POST" action="{{ route('lscefa.quality.quotes.store') }}" id="create-quote-form">
                        @csrf
                        <div class="mb-3">
                            <label for="quote_id" class="form-label">ID de la Cotización</label>
                            <input type="text" class="form-control" id="quote_id" name="quote_id" value="{{ old('quote_id') }}" required>
                            @error('quote_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Cliente</label>
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-2 mb-md-0">
                                    <select class="form-control" id="customer_id" name="customer_id" required>
                                        <option value="">Seleccione un cliente</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->customer_id }}"
                                                    data-discount="{{ $customer->customerType->discount_percentage ?? 0 }}"
                                                    data-applicant="{{ $customer->applicant }}"
                                                    data-tax="{{ $customer->tax_id }}"
                                                    {{ old('customer_id') == $customer->customer_id ? 'selected' : '' }}>
                                                {{ $customer->applicant }} - {{ $customer->tax_id }} ({{ $customer->customerType->name ?? 'Sin tipo' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 position-relative">
                                    <input type="text" id="customer_search" class="form-control" placeholder="Buscar (nombre o NIT/Cédula)">
                                    <div id="customer_suggestions" class="list-group position-absolute w-100" style="z-index:1000; max-height: 240px; overflow:auto;"></div>
                                </div>
                            </div>
                            @error('customer_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Unidades -->
                        <h5>Unidades</h5>
                        <div id="unit-container">
                            <div class="unit-row mb-4 border p-3" data-index="0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Unidad 1</h6>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-unit-btn" style="display:none;">
                                        <i class="fas fa-trash"></i> Eliminar unidad
                                    </button>
                                </div>
                                <!-- Servicios de la Unidad -->
                                <h6>Servicios</h6>
                                <div class="service-container">
                                    <div class="service-row mb-3" data-service-index="0">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="units_0_services_0" class="form-label">Servicio</label>
                                                <div class="input-group">
                                                    <select class="form-control service-select" name="units[0][services][0][service_id]" id="units_0_services_0">
                                                        <option value="">Seleccione un servicio</option>
                                                        @foreach ($services as $service)
                                                            <option value="{{ $service->services_id }}" data-price="{{ $service->precio }}">
                                                                {{ $service->descripcion }} ({{ $service->precio }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger delete-service-btn" style="display: none;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" class="form-control quantity" name="units[0][services][0][quantity]" value="1">
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
                                <!-- Paquetes de la Unidad -->
                                <h6>Paquetes de Servicios</h6>
                                <div class="package-container">
                                    <div class="package-row mb-3" data-package-index="0">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="units_0_packages_0" class="form-label">Paquete</label>
                                                <div class="input-group">
                                                    <select class="form-control package-select" name="units[0][packages][0][package_id]" id="units_0_packages_0">
                                                        <option value="">Seleccione un paquete</option>
                                                        @foreach ($servicePackages as $servicePackage)
                                                            <option value="{{ $servicePackage->service_package_id }}" data-price="{{ $servicePackage->price }}">
                                                                {{ $servicePackage->name }} ({{ $servicePackage->price }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger delete-package-btn" style="display: none;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" class="form-control quantity" name="units[0][packages][0][quantity]" value="1">
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
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary mb-3" id="add-unit-btn">Agregar Unidad</button>
                        <!-- Total -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Total: <span id="total-amount">0.00</span></h5>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-success">Crear Cotización</button>
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
     data-services="{{ json_encode($services) }}"
     data-service-packages="{{ json_encode($servicePackages) }}"></div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const unitContainer = document.getElementById('unit-container');
    const addUnitBtn = document.getElementById('add-unit-btn');
    const totalDisplay = document.getElementById('total-amount');
    let unitIndex = 1;
    let discountPercentage = 0;
    const servicesData = @json($services);
    const servicePackages = @json($servicePackages);
    document.getElementById('customer_id').addEventListener('change', (e) => {
        const selectedOption = e.target.selectedOptions[0];
        discountPercentage = parseFloat(selectedOption.dataset.discount || 0);
        calculateTotal();
    });
    function calculateSubtotal(row) {
        const select = row.querySelector('.service-select, .package-select');
        const subtotal = row.querySelector('.subtotal');
        if (!select || !subtotal) return 0;
        const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
        const subtotalValue = price;
        subtotal.value = subtotalValue.toFixed(2);
        return subtotalValue;
    }
    function calculateTotal() {
        let total = 0;
        const unitRows = unitContainer.querySelectorAll('.unit-row');
        unitRows.forEach(unitRow => {
            const serviceRows = unitRow.querySelectorAll('.service-row');
            const packageRows = unitRow.querySelectorAll('.package-row');
            serviceRows.forEach(row => {
                if (row.querySelector('.service-select')?.value) {
                    total += calculateSubtotal(row);
                }
            });
            packageRows.forEach(row => {
                if (row.querySelector('.package-select')?.value) {
                    total += calculateSubtotal(row);
                }
            });
        });
        if (discountPercentage > 0) {
            total = total * (1 - discountPercentage / 100);
        }
        totalDisplay.textContent = total.toFixed(2);
    }

    // Reindex all units and their fields after any structural change
    function reindexUnits() {
        const unitRows = unitContainer.querySelectorAll('.unit-row');
        unitRows.forEach((row, idx) => {
            row.dataset.index = idx;
            const title = row.querySelector('h6');
            if (title) { title.textContent = `Unidad ${idx + 1}`; }
            // Enable delete button if more than 1 unit exists
            const delBtn = row.querySelector('.delete-unit-btn');
            if (delBtn) {
                delBtn.style.display = (unitRows.length > 1) ? 'inline-block' : 'none';
            }
            // Update names and ids inside this unit
            const namedInputs = row.querySelectorAll('[name]');
            namedInputs.forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/units\[\d+\]/, `units[${idx}]`));
                }
            });
            // Update "for" labels and ids that contain the index pattern
            const idEls = row.querySelectorAll('[id]');
            idEls.forEach(el => {
                const id = el.getAttribute('id');
                if (id) {
                    el.setAttribute('id', id.replace(/units_\d+_/g, `units_${idx}_`));
                }
            });
            const labels = row.querySelectorAll('label[for]');
            labels.forEach(lb => {
                const f = lb.getAttribute('for');
                if (f) lb.setAttribute('for', f.replace(/units_\d+_/g, `units_${idx}_`));
            });
        });
        // Keep unitIndex consistent for adding new units
        unitIndex = unitContainer.querySelectorAll('.unit-row').length;
        calculateTotal();
    }

    function attachUnitHandlers(unitRow) {
        const delBtn = unitRow.querySelector('.delete-unit-btn');
        if (delBtn) {
            delBtn.addEventListener('click', () => {
                const unitRows = unitContainer.querySelectorAll('.unit-row');
                if (unitRows.length <= 1) {
                    Swal.fire('Aviso', 'Debe existir al menos una unidad.', 'info');
                    return;
                }
                unitRow.remove();
                reindexUnits();
            });
        }
    }
    // Add new service row
    function addServiceRow(unitRow, unitIndex, serviceIndex) {
        const serviceContainer = unitRow.querySelector('.service-container');
        const newRow = document.createElement('div');
        newRow.className = 'service-row mb-3';
        newRow.dataset.serviceIndex = serviceIndex;
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label for="units_${unitIndex}_services_${serviceIndex}" class="form-label">Servicio</label>
                    <div class="input-group">
                        <select class="form-control service-select" name="units[${unitIndex}][services][${serviceIndex}][service_id]" id="units_${unitIndex}_services_${serviceIndex}">
                            <option value="">Seleccione un servicio</option>
                            ${servicesData.map(s => `<option value="${s.services_id}" data-price="${s.precio}">${s.descripcion} (${s.precio})</option>`).join('')}
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger delete-service-btn" ${serviceIndex === 0 ? 'style="display: none;"' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control quantity" name="units[${unitIndex}][services][${serviceIndex}][quantity]" value="1">
                <div class="col-md-4">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control subtotal" readonly value="0">
                </div>
            </div>
        `;
        serviceContainer.appendChild(newRow);
        const select = newRow.querySelector('.service-select');
        const deleteBtn = newRow.querySelector('.delete-service-btn');
        select.addEventListener('change', () => {
            calculateSubtotal(newRow);
            calculateTotal();
        });
        deleteBtn.addEventListener('click', () => {
            newRow.remove();
            calculateTotal();
        });
        calculateSubtotal(newRow);
        calculateTotal();
    }
    // Add new package row
    function addPackageRow(unitRow, unitIndex, packageIndex) {
        const packageContainer = unitRow.querySelector('.package-container');
        const newRow = document.createElement('div');
        newRow.className = 'package-row mb-3';
        newRow.dataset.packageIndex = packageIndex;
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label for="units_${unitIndex}_packages_${packageIndex}" class="form-label">Paquete</label>
                    <div class="input-group">
                        <select class="form-control package-select" name="units[${unitIndex}][packages][${packageIndex}][package_id]" id="units_${unitIndex}_packages_${packageIndex}">
                            <option value="">Seleccione un paquete</option>
                            ${servicePackages.map(p => `<option value="${p.service_package_id}" data-price="${p.price}">${p.name} (${p.price})</option>`).join('')}
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger delete-package-btn" ${packageIndex === 0 ? 'style="display: none;"' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control quantity" name="units[${unitIndex}][packages][${packageIndex}][quantity]" value="1">
                <div class="col-md-4">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control subtotal" readonly value="0">
                </div>
            </div>
        `;
        packageContainer.appendChild(newRow);
        const select = newRow.querySelector('.package-select');
        const deleteBtn = newRow.querySelector('.delete-package-btn');
        select.addEventListener('change', () => {
            calculateSubtotal(newRow);
            calculateTotal();
        });
        deleteBtn.addEventListener('click', () => {
            newRow.remove();
            calculateTotal();
        });
        calculateSubtotal(newRow);
        calculateTotal();
    }
    // Add new unit
    addUnitBtn.addEventListener('click', () => {
        const newUnitRow = document.createElement('div');
        newUnitRow.className = 'unit-row mb-4 border p-3';
        newUnitRow.dataset.index = unitIndex;
        newUnitRow.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Unidad ${unitIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm delete-unit-btn">
                    <i class="fas fa-trash"></i> Eliminar unidad
                </button>
            </div>
            <h6>Servicios</h6>
            <div class="service-container">
                <div class="service-row mb-3" data-service-index="0">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Servicio</label>
                            <div class="input-group">
                                <select class="form-control service-select" name="units[${unitIndex}][services][0][service_id]">
                                    <option value="">Seleccione un servicio</option>
                                    ${servicesData.map(s => `<option value="${s.services_id}" data-price="${s.precio}">${s.descripcion} (${s.precio})</option>`).join('')}
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger delete-service-btn" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="form-control quantity" name="units[${unitIndex}][services][0][quantity]" value="1">
                        <div class="col-md-4">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control subtotal" readonly value="0">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3 add-service-btn">Agregar Servicio</button>
            <h6>Paquetes de Servicios</h6>
            <div class="package-container">
                <div class="package-row mb-3" data-package-index="0">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Paquete</label>
                            <div class="input-group">
                                <select class="form-control package-select" name="units[${unitIndex}][packages][0][package_id]">
                                    <option value="">Seleccione un paquete</option>
                                    ${servicePackages.map(p => `<option value="${p.service_package_id}" data-price="${p.price}">${p.name} (${p.price})</option>`).join('')}
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger delete-package-btn" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="form-control quantity" name="units[${unitIndex}][packages][0][quantity]" value="1">
                        <div class="col-md-4">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control subtotal" readonly value="0">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3 add-package-btn">Agregar Paquete</button>
        `;
        unitContainer.appendChild(newUnitRow);
        // Initialize service and package buttons for the new unit
        const addServiceBtn = newUnitRow.querySelector('.add-service-btn');
        const addPackageBtn = newUnitRow.querySelector('.add-package-btn');
        let serviceIndex = 1;
        let packageIndex = 1;
        // Attach delete unit handler
        attachUnitHandlers(newUnitRow);
        addServiceBtn.addEventListener('click', () => {
            addServiceRow(newUnitRow, unitIndex, serviceIndex);
            serviceIndex++;
        });
        addPackageBtn.addEventListener('click', () => {
            addPackageRow(newUnitRow, unitIndex, packageIndex);
            packageIndex++;
        });
        // Initialize first service and package rows
        const serviceRow = newUnitRow.querySelector('.service-row');
        const packageRow = newUnitRow.querySelector('.package-row');
        const serviceSelect = serviceRow.querySelector('.service-select');
        const serviceQuantity = serviceRow.querySelector('.quantity');
        const packageSelect = packageRow.querySelector('.package-select');
        const packageQuantity = packageRow.querySelector('.quantity');
        serviceSelect.addEventListener('change', () => {
            calculateSubtotal(serviceRow);
            calculateTotal();
        });
        serviceQuantity.addEventListener('input', () => {
            calculateSubtotal(serviceRow);
            calculateTotal();
        });
        packageSelect.addEventListener('change', () => {
            calculateSubtotal(packageRow);
            calculateTotal();
        });
        packageQuantity.addEventListener('input', () => {
            calculateSubtotal(packageRow);
            calculateTotal();
        });
        unitIndex++;
        reindexUnits();
    });
    // Initialize first unit
    const initialUnitRow = unitContainer.querySelector('.unit-row');
    const addServiceBtn = initialUnitRow.querySelector('.add-service-btn');
    const addPackageBtn = initialUnitRow.querySelector('.add-package-btn');
    let serviceIndex = 1;
    let packageIndex = 1;
    // Attach delete handler and visibility for initial unit
    attachUnitHandlers(initialUnitRow);
    reindexUnits();
    addServiceBtn.addEventListener('click', () => {
        addServiceRow(initialUnitRow, 0, serviceIndex);
        serviceIndex++;
    });
    addPackageBtn.addEventListener('click', () => {
        addPackageRow(initialUnitRow, 0, packageIndex);
        packageIndex++;
    });
    const initialServiceRow = initialUnitRow.querySelector('.service-row');
    const initialPackageRow = initialUnitRow.querySelector('.package-row');
    const initialServiceSelect = initialServiceRow.querySelector('.service-select');
    const initialServiceQuantity = initialServiceRow.querySelector('.quantity');
    const initialPackageSelect = initialPackageRow.querySelector('.package-select');
    const initialPackageQuantity = initialPackageRow.querySelector('.quantity');
    initialServiceSelect.addEventListener('change', () => {
        calculateSubtotal(initialServiceRow);
        calculateTotal();
    });
    initialServiceQuantity.addEventListener('input', () => {
        calculateSubtotal(initialServiceRow);
        calculateTotal();
    });
    initialPackageSelect.addEventListener('change', () => {
        calculateSubtotal(initialPackageRow);
        calculateTotal();
    });
    initialPackageQuantity.addEventListener('input', () => {
        calculateSubtotal(initialPackageRow);
        calculateTotal();
    });
    // Initial total calculation
    calculateTotal();

    // --- Búsqueda con sugerencias para clientes (por nombre y NIT/Cédula) ---
    const customerSelect = document.getElementById('customer_id');
    const customerSearch = document.getElementById('customer_search');
    const suggestionBox = document.getElementById('customer_suggestions');
    const customers = Array.from(customerSelect.options)
        .filter((o, idx) => idx > 0) // omitir placeholder
        .map(o => ({
            value: o.value,
            applicant: (o.getAttribute('data-applicant') || o.text).trim(),
            tax: (o.getAttribute('data-tax') || '').trim(),
            discount: o.getAttribute('data-discount') || ''
        }));

    function clearSuggestions() {
        suggestionBox.innerHTML = '';
        suggestionBox.style.display = 'none';
    }

    function renderSuggestions(items) {
        suggestionBox.innerHTML = '';
        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item list-group-item-action disabled';
            empty.textContent = 'Sin resultados';
            suggestionBox.appendChild(empty);
            suggestionBox.style.display = 'block';
            return;
        }
        items.slice(0, 12).forEach(it => {
            const a = document.createElement('button');
            a.type = 'button';
            a.className = 'list-group-item list-group-item-action';
            a.innerHTML = `<div><strong>${it.applicant}</strong></div><small>${it.tax}</small>`;
            a.addEventListener('click', () => {
                customerSelect.value = it.value;
                // Disparar cambio para recalcular descuento/total
                customerSelect.dispatchEvent(new Event('change'));
                // Reflejar en el input y cerrar
                customerSearch.value = `${it.applicant}`;
                clearSuggestions();
            });
            suggestionBox.appendChild(a);
        });
        suggestionBox.style.display = 'block';
    }

    customerSearch.addEventListener('input', () => {
        const q = customerSearch.value.trim().toLowerCase();
        if (!q) return clearSuggestions();
        const results = customers.filter(c =>
            (c.applicant || '').toLowerCase().includes(q) ||
            (c.tax || '').toLowerCase().includes(q)
        );
        renderSuggestions(results);
    });

    // Cerrar sugerencias al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!suggestionBox.contains(e.target) && e.target !== customerSearch) {
            clearSuggestions();
        }
    });
    // Enter selecciona la primera sugerencia disponible
    customerSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = suggestionBox.querySelector('.list-group-item-action:not(.disabled)');
            if (first) first.click();
        }
    });
});
</script>
@endpush
@endsection 