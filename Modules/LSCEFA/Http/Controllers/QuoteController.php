<?php
namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\LSCEFA\Models\Customer;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServicePackage;
use Modules\LSCEFA\Models\Quote;
use Modules\LSCEFA\Models\QuoteService;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\ServiceProcessDetail;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'user', 'quoteServices.service', 'quoteServices.servicePackage']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quote_id', 'like', "%$search%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('tax_id', 'like', "%$search%")
                         ->orWhere('applicant', 'like', "%$search%")
                      ;
                  });
            });
        }
        $quotes = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('lscefa::quotes.index', compact('quotes'));
    }

    public function create()
    {
        $customers = Customer::with('customerType')->get();
        $services = Service::all();
        $servicePackages = ServicePackage::all();
        return view('lscefa::quotes.create', compact('customers', 'services', 'servicePackages'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes estar autenticado para crear una cotización.');
        }
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|string|unique:quotes,quote_id',
            'customer_id' => 'required|exists:customers,customer_id',
            'units' => 'required|array|min:1',
            'units.*.services' => 'nullable|array',
            'units.*.services.*.service_id' => 'nullable|exists:services,services_id',
            'units.*.services.*.quantity' => 'nullable|integer|min:1',
            'units.*.packages' => 'nullable|array',
            'units.*.packages.*.package_id' => 'nullable|exists:service_packages,service_package_id',
            'units.*.packages.*.quantity' => 'nullable|integer|min:1',
        ]);
        $hasServiceOrPackage = false;
        foreach ($request->input('units', []) as $unit) {
            $services = $unit['services'] ?? [];
            $packages = $unit['packages'] ?? [];
            foreach ($services as $service) {
                if (!empty($service['service_id']) && ($service['quantity'] ?? 1) > 0) {
                    $hasServiceOrPackage = true;
                    break 2;
                }
            }
            foreach ($packages as $package) {
                if (!empty($package['package_id']) && ($package['quantity'] ?? 1) > 0) {
                    $hasServiceOrPackage = true;
                    break 2;
                }
            }
        }
        if (!$hasServiceOrPackage) {
            $validator->errors()->add('units', 'Al menos una unidad debe tener un servicio o paquete seleccionado con cantidad válida.');
        }
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $validatedData = $validator->validated();
            $customer = Customer::findOrFail($validatedData['customer_id']);
            $total = 0;
            DB::beginTransaction();
            $quote = Quote::create([
                'quote_id' => $validatedData['quote_id'],
                'customer_id' => $validatedData['customer_id'],
                'user_id' => Auth::id(),
                'total' => 0,
            ]);
            foreach ($validatedData['units'] as $unitIndex => $unit) {
                $services = $unit['services'] ?? [];
                $packages = $unit['packages'] ?? [];
                foreach ($services as $service) {
                    if (!empty($service['service_id']) && ($service['quantity'] ?? 1) > 0) {
                        $quantity = $service['quantity'] ?? 1;
                        $serviceModel = Service::findOrFail($service['service_id']);
                        $subtotal = $serviceModel->precio * $quantity;
                        $total += $subtotal;
                        QuoteService::create([
                            'quote_id' => $quote->quote_id,
                            'service_id' => $service['service_id'],
                            'service_package_id' => null,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);
                    }
                }
                foreach ($packages as $package) {
                    if (!empty($package['package_id']) && ($package['quantity'] ?? 1) > 0) {
                        $quantity = $package['quantity'] ?? 1;
                        $packageModel = ServicePackage::findOrFail($package['package_id']);
                        $subtotal = $packageModel->price * $quantity;
                        $total += $subtotal;
                        QuoteService::create([
                            'quote_id' => $quote->quote_id,
                            'service_id' => null,
                            'service_package_id' => $package['package_id'],
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);
                    }
                }
            }
            $discount = $customer->customerType->discount_percentage / 100 ?? 0;
            if ($discount > 0) {
                $total = $total * (1 - $discount);
            }
            $quote->total = $total;
            $quote->save();
            DB::commit();
            return redirect()->route('lscefa.quality.quotes.index')->with('success', 'Cotización creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Ocurrió un error al crear la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);
        $quote->delete();

        return redirect()->route('lscefa.quality.quotes.index')
            ->with('success', 'Cotización eliminada correctamente.');
    }

    public function edit($id)
    {
        $quote = Quote::with(['customer', 'quoteServices.service', 'quoteServices.servicePackage'])->findOrFail($id);
        $customers = Customer::with('customerType')->get();
        $services = Service::all();
        $servicePackages = ServicePackage::all();

        // Agrupar los quoteServices por unit_index para construir units
        $units = [];
        foreach ($quote->quoteServices as $qs) {
            $unitIndex = $qs->unit_index ?? 0;
            if (!isset($units[$unitIndex])) {
                $units[$unitIndex] = [
                    'services' => [],
                    'packages' => []
                ];
            }
            if ($qs->service_id) {
                $units[$unitIndex]['services'][] = [
                    'service_id' => $qs->service_id,
                    'quantity' => $qs->quantity,
                    'service' => $qs->service
                ];
            }
            if ($qs->service_package_id) {
                $units[$unitIndex]['packages'][] = [
                    'package_id' => $qs->service_package_id,
                    'quantity' => $qs->quantity,
                    'package' => $qs->servicePackage
                ];
            }
        }
        // Reindexar para evitar huecos en los índices
        $units = array_values($units);

        return view('lscefa::quotes.edit', compact('quote', 'customers', 'services', 'servicePackages', 'units'));
    }

    public function show($id)
    {
        $quote = Quote::with(['customer', 'user', 'quoteServices.service', 'quoteServices.servicePackage'])->findOrFail($id);
        return view('lscefa::quotes.show', compact('quote'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes estar autenticado para actualizar una cotización.');
        }
        $quote = Quote::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|string|unique:quotes,quote_id,' . $quote->quote_id . ',quote_id',
            'customer_id' => 'required|exists:customers,customer_id',
            'units' => 'required|array|min:1',
            'units.*.services' => 'nullable|array',
            'units.*.services.*.service_id' => 'nullable|exists:services,services_id',
            'units.*.services.*.quantity' => 'nullable|integer|min:1',
            'units.*.packages' => 'nullable|array',
            'units.*.packages.*.package_id' => 'nullable|exists:service_packages,service_package_id',
            'units.*.packages.*.quantity' => 'nullable|integer|min:1',
        ]);
        $hasServiceOrPackage = false;
        foreach ($request->input('units', []) as $unit) {
            $services = $unit['services'] ?? [];
            $packages = $unit['packages'] ?? [];
            foreach ($services as $service) {
                if (!empty($service['service_id']) && ($service['quantity'] ?? 1) > 0) {
                    $hasServiceOrPackage = true;
                    break 2;
                }
            }
            foreach ($packages as $package) {
                if (!empty($package['package_id']) && ($package['quantity'] ?? 1) > 0) {
                    $hasServiceOrPackage = true;
                    break 2;
                }
            }
        }
        if (!$hasServiceOrPackage) {
            $validator->errors()->add('units', 'Al menos una unidad debe tener un servicio o paquete seleccionado con cantidad válida.');
        }
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $validatedData = $validator->validated();
            $customer = Customer::findOrFail($validatedData['customer_id']);
            $total = 0;
            DB::beginTransaction();
            $quote->quote_id = $validatedData['quote_id'];
            $quote->customer_id = $validatedData['customer_id'];
            $quote->user_id = Auth::id();
            $quote->save();
            // Eliminar servicios/paquetes anteriores
            QuoteService::where('quote_id', $quote->quote_id)->delete();
            foreach ($validatedData['units'] as $unitIndex => $unit) {
                $services = $unit['services'] ?? [];
                $packages = $unit['packages'] ?? [];
                foreach ($services as $service) {
                    if (!empty($service['service_id']) && ($service['quantity'] ?? 1) > 0) {
                        $quantity = $service['quantity'] ?? 1;
                        $serviceModel = Service::findOrFail($service['service_id']);
                        $subtotal = $serviceModel->precio * $quantity;
                        $total += $subtotal;
                        QuoteService::create([
                            'quote_id' => $quote->quote_id,
                            'service_id' => $service['service_id'],
                            'service_package_id' => null,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);
                    }
                }
                foreach ($packages as $package) {
                    if (!empty($package['package_id']) && ($package['quantity'] ?? 1) > 0) {
                        $quantity = $package['quantity'] ?? 1;
                        $packageModel = ServicePackage::findOrFail($package['package_id']);
                        $subtotal = $packageModel->price * $quantity;
                        $total += $subtotal;
                        QuoteService::create([
                            'quote_id' => $quote->quote_id,
                            'service_id' => null,
                            'service_package_id' => $package['package_id'],
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);
                    }
                }
            }
            $discount = $customer->customerType->discount_percentage / 100 ?? 0;
            if ($discount > 0) {
                $total = $total * (1 - $discount);
            }
            $quote->total = $total;
            $quote->save();
            DB::commit();
            return redirect()->route('lscefa.quality.quotes.index')->with('success', 'Cotización actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Ocurrió un error al actualizar la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    // PDF y carga de archivos
    public function pdf($id)
    {
        $quote = Quote::with(['customer.customerType', 'user', 'quoteServices.service', 'quoteServices.servicePackage'])->findOrFail($id);
        $isAccredited = false;
        foreach ($quote->quoteServices as $quoteService) {
            if ($quoteService->service && $quoteService->service->acreditado) {
                $isAccredited = true;
                break;
            }
            if ($quoteService->servicePackage && $quoteService->servicePackage->acreditado) {
                $isAccredited = true;
                break;
            }
        }
        $admin = $quote->user;
        $tipoCliente = $quote->customer->customerType->name ?? 'externo';
        $clientText = $quote->customer->customerType->description ?? ($tipoCliente === 'interno'
            ? 'Cliente Interno: Este cliente es parte de la organización.'
            : 'Cliente Externo: Este cliente no pertenece a la organización.');
        // OPTIMIZACIÓN: Mapear todos los servicios incluidos en paquetes a sus nombres
        $serviceNameMap = [];
        $allServiceIds = [];
        foreach ($quote->quoteServices as $qs) {
            if ($qs->servicePackage && $qs->servicePackage->included_services) {
                $included = $qs->servicePackage->included_services;
                if (is_string($included)) {
                    $included = json_decode($included, true);
                }
                if (is_array($included)) {
                    foreach ($included as $includedService) {
                        if (is_numeric($includedService)) {
                            $allServiceIds[] = $includedService;
                        }
                    }
                }
            }
        }
        if (count($allServiceIds) > 0) {
            $services = \Modules\LSCEFA\Models\Service::whereIn('services_id', $allServiceIds)->get();
            foreach ($services as $service) {
                $serviceNameMap[$service->services_id] = $service->descripcion;
            }
        }
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('lscefa::quotes.pdf', compact('quote', 'isAccredited', 'admin', 'clientText', 'serviceNameMap'));
        return $pdf->download('cotizacion_' . $quote->quote_id . '.pdf');
    }

    public function uploadForm($quote_id)
    {
        $quote = Quote::with(['quoteServices'])->findOrFail($quote_id);
        $quoteServices = $quote->quoteServices ?? collect();
        // Calcular unitCount correctamente
        $unitIndexes = $quoteServices->pluck('unit_index')->unique()->values();
        $unitCount = $unitIndexes->count() > 0 ? $unitIndexes->max() + 1 : 1;
        // Agrupar servicios por terreno
        $servicesPerUnit = [];
        foreach ($quoteServices as $quoteService) {
            $unitIdx = $quoteService->unit_index ?? 0;
            $servicesPerUnit[$unitIdx][] = $quoteService;
        }
        $user = auth()->user();
        if (!$user || !(
            $user->havePermission('lscefa.quality.quotes.upload') ||
            $user->havePermission('lscefa.admin.quotes.upload') ||
            $user->havePermission('lscefa.quality.process.start') ||
            $user->havePermission('lscefa.admin.process.start')
        )) {
            abort(403, 'No tienes permisos para esta acción.');
        }
        return view('lscefa::quotes.upload', compact('quote', 'unitCount', 'servicesPerUnit'));
    }

    public function upload(Request $request, $quote_id)
    {
        $user = auth()->user();
        
        if (!$user || !(
            $user->havePermission('lscefa.quality.quotes.upload') ||
            $user->havePermission('lscefa.admin.quotes.upload') ||
            $user->havePermission('lscefa.quality.process.start') ||
            $user->havePermission('lscefa.admin.process.start')
        )) {
            abort(403, 'No tienes permisos para subir comprobantes.');
        }
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);
        $quote = Quote::findOrFail($quote_id);
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $filename = 'quote_' . $quote->quote_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Ruta donde debería guardarse
            $directory = storage_path('app/public/comprobantes');
            $path = 'comprobantes/' . $filename;
            $fullPath = storage_path('app/public/' . $path);
            
            \Log::info('=== INICIO DE SUBIDA DE ARCHIVO ===');
            \Log::info('Ruta de destino:', [
                'directorio' => $directory,
                'ruta_completa' => $fullPath,
                'directorio_padre' => dirname($fullPath),
                'directorio_padre_existe' => file_exists(dirname($fullPath)) ? 'Sí' : 'No',
                'permisos' => substr(sprintf('%o', fileperms(dirname($fullPath))), -4)
            ]);
            
            // Crear el directorio si no existe
            if (!file_exists($directory)) {
                \Log::info('Creando directorio: ' . $directory);
                mkdir($directory, 0755, true);
            }
            
            // Mover el archivo manualmente
            \Log::info('Intentando mover archivo a: ' . $fullPath);
            
            if ($file->move(dirname($fullPath), $filename)) {
                \Log::info('Archivo movido exitosamente a: ' . $fullPath);
                
                // Verificar si el archivo existe después de moverlo
                if (file_exists($fullPath)) {
                    \Log::info('El archivo existe en la ubicación esperada');
                } else {
                    \Log::error('El archivo no se encuentra en la ubicación esperada');
                    // Buscar el archivo en todo el directorio de almacenamiento
                    $found = false;
                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(storage_path('app')));
                    foreach ($files as $file) {
                        if ($file->isFile() && $file->getFilename() === $filename) {
                            \Log::error('El archivo se encuentra en: ' . $file->getPathname());
                            $found = true;
                        }
                    }
                    if (!$found) {
                        \Log::error('No se encontró el archivo en ningún lugar del directorio de almacenamiento');
                    }
                }
                
                $quoteFile = new \Modules\LSCEFA\Models\QuoteFile([
                    'filename' => $filename,
                    'path' => 'public/' . $path,
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
                $quote->files()->save($quoteFile);
                
                return redirect()->route('lscefa.quality.quotes.upload', $quote->quote_id)
                    ->with('success', 'Comprobante subido correctamente.');
            } else {
                \Log::error('Error al mover el archivo');
                \Log::error('Error de PHP: ' . json_encode(error_get_last()));
            }
        }
        
        return redirect()->route('lscefa.quality.quotes.upload', $quote->quote_id)
            ->with('error', 'No se pudo subir el comprobante.');
    }

    public function startProcess(Request $request, $quote_id)
    {
        $user = auth()->user();
        
        if (!$user || !(
            $user->havePermission('lscefa.quality.quotes.upload') ||
            $user->havePermission('lscefa.admin.quotes.upload') ||
            $user->havePermission('lscefa.quality.process.start') ||
            $user->havePermission('lscefa.admin.process.start')
        )) {
            abort(403, 'No tienes permisos para esta acción.');
        }

        $request->validate([
            'archivo' => 'nullable|file|mimes:pdf,jpg,png|max:40960',
            'comunicacion_cliente' => 'required|string',
            'dias_procesar' => 'required|integer|min:1|max:365',
            'unit_count' => 'required|integer|min:1',
            'archivo_comunicacion' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'descriptions.*' => 'required|string|min:3',
            'item_codes.*' => 'required|string|min:3',
            'services.*' => 'required|string',
        ], [
            'archivo.mimes' => 'El comprobante debe ser PDF o imagen.',
            'archivo.max' => 'El comprobante no puede superar los 40MB.',
            'comunicacion_cliente.required' => 'La comunicación con el cliente es obligatoria.',
            'archivo_comunicacion.mimes' => 'El archivo debe ser PDF, Word (.doc, .docx) o Excel (.xls, .xlsx).',
            'archivo_comunicacion.max' => 'El archivo no puede superar los 5MB.',
        ]);

        $quote = Quote::with(['quoteServices'])->where('quote_id', $quote_id)->firstOrFail();
        
        $unitCount = $request->input('unit_count');
        $services = $request->input('services', []);

        try {
            DB::beginTransaction();

            // Crear directorios si no existen (ambos por quote_id)
            $comprobantesPath = module_path('LSCEFA') . '/storage/app/comprobantes/' . $quote->quote_id;
            $comunicacionesPath = module_path('LSCEFA') . '/storage/app/comunicaciones/' . $quote->quote_id;
            
            if (!file_exists($comprobantesPath)) {
                mkdir($comprobantesPath, 0777, true);
            }
            if (!file_exists($comunicacionesPath)) {
                mkdir($comunicacionesPath, 0777, true);
            }

            // Guardar comprobante obligatorio
            $comprobanteFilename = null;
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $comprobanteFilename = 'quote_' . $quote->quote_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($comprobantesPath, $comprobanteFilename);
                $quote->file = $comprobanteFilename;
                $quote->save();
            }

            // Manejar el archivo de comunicación si se subió (guardar dentro de comunicaciones/{quote_id})
            $communicationFile = null;
            if ($request->hasFile('archivo_comunicacion')) {
                $file = $request->file('archivo_comunicacion');
                $filename = 'com_' . time() . '_' . $file->getClientOriginalName();
                $file->move($comunicacionesPath, $filename);
                $communicationFile = $filename;
            }

            for ($unitIndex = 0; $unitIndex < $unitCount; $unitIndex++) {
                $description = $request->input("descriptions.$unitIndex");
                $itemCode = $request->input("item_codes.$unitIndex");
                $selectedServiceIds = $services[$unitIndex] ?? [];
                if (is_string($selectedServiceIds)) {
                    $selectedServiceIds = json_decode($selectedServiceIds, true) ?? [];
                }

                // Crear el proceso (unidad)
                $process = Process::create([
                    'process_id' => 'PRC-' . time() . '-' . $unitIndex,
                    'quote_id' => $quote_id,
                    'item_code' => $itemCode,
                    'status' => 'pending',
                    'client_communication' => $request->input('comunicacion_cliente'),
                    'communication_file' => $communicationFile,
                    'processing_days' => $request->input('dias_procesar'),
                    'reception_date' => now(),
                    'description' => $description,
                    'sampling_place' => $request->input('lugar_muestreo'),
                    'sampling_date' => $request->input('fecha_muestreo'),
                    'reception_responsible' => auth()->user()->id,
                    'delivery_date' => now()->addDays($request->input('dias_procesar')),
                ]);

                // Crear los ServiceProcessDetail para cada servicio asociado a esta unidad
                $unitServices = $quote->quoteServices->where('unit_index', $unitIndex);
                foreach ($unitServices as $qs) {
                    if ($qs->service_id) { // Solo servicios individuales
                        ServiceProcessDetail::create([
                            'process_id' => $process->process_id,
                            'service_id' => $qs->service_id,
                            'status' => 'pending',
                            'result' => null,
                            'file' => null,
                            'observations' => null,
                        ]);
                    }
                    // Si es un paquete, agregar todos los servicios incluidos
                    if ($qs->service_package_id && $qs->servicePackage) {
                        $includedServices = $qs->servicePackage->included_services;
                        if (is_array($includedServices)) {
                            foreach ($includedServices as $includedService) {
                                if (is_numeric($includedService)) {
                                    ServiceProcessDetail::create([
                                        'process_id' => $process->process_id,
                                        'service_id' => $includedService,
                                        'status' => 'pending',
                                        'result' => null,
                                        'file' => null,
                                        'observations' => null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            // Redirigir a la vista global de procesos iniciados
            return redirect()->route('lscefa.quality.processes.index')
                ->with('success', 'Comprobante subido e inicio de procesos exitoso para todas las unidades.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al iniciar procesos: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al iniciar los procesos: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function processesIndex($quote_id)
    {
        $quote = Quote::with(['processes'])->findOrFail($quote_id);
        $processes = $quote->processes;
        return view('lscefa::quotes.processes_index', compact('quote', 'processes'));
    }

    public function allProcessesIndex(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->havePermission('lscefa.quality.processes.index') && !$user->havePermission('lscefa.admin.processes.index'))) {
            abort(403, 'No tienes permisos para ver el listado global de procesos.');
        }
        $processes = \Modules\LSCEFA\Models\Process::with('quote')->orderBy('created_at', 'desc')->paginate(20);
        return view('lscefa::quotes.processes_global', compact('processes'));
    }

    public function processShow($process_id)
    {
        $user = auth()->user();
        if (!$user || (!$user->havePermission('lscefa.quality.processes.index') && !$user->havePermission('lscefa.admin.processes.index'))) {
            abort(403, 'No tienes permisos para ver el detalle del proceso.');
        }
        $process = Process::with('quote')->findOrFail($process_id);
        return view('lscefa::quotes.process_show', compact('process'));
    }

    public function destroyProcess($process_id)
    {
        $user = auth()->user();
        if (!$user || (!$user->havePermission('lscefa.quality.processes.index') && !$user->havePermission('lscefa.admin.processes.index'))) {
            abort(403, 'No tienes permisos para eliminar procesos.');
        }
        $process = Process::findOrFail($process_id);
        $process->delete();
        return redirect()->route('lscefa.quality.processes.index')->with('success', 'Proceso eliminado correctamente.');
    }
}