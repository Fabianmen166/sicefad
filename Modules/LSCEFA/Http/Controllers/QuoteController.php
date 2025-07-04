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
                        $subtotal = $serviceModel->price * $quantity;
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
                        $subtotal = $serviceModel->price * $quantity;
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

    public function showUploadForm($id)
    {
        $quote = Quote::with(['quoteServices'])->findOrFail($id);
        $unitCount = 1;
        $unitIndexes = [];
        foreach ($quote->quoteServices as $qs) {
            if (isset($qs->unit_index)) {
                $unitIndexes[] = $qs->unit_index;
            }
        }
        if (count($unitIndexes) > 0) {
            $unitCount = count(array_unique($unitIndexes));
        } else {
            $unitCount = $quote->quoteServices->count() > 0 ? $quote->quoteServices->count() : 1;
        }
        return view('quotes.upload', compact('quote', 'unitCount'));
    }

    public function upload(Request $request, $id)
    {
        dd($request->method());
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);
        try {
            $quote = Quote::findOrFail($id);
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $filename = 'quote_' . $quote->quote_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/comprobantes', $filename);
                $quote->file = $filename;
                $quote->save();
            }
            return redirect()->route('lscefa.quality.quotes.index')->with('success', 'Comprobante de pago subido exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }
} 