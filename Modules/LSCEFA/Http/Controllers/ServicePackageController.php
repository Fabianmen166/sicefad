<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\ServicePackage;
use Modules\LSCEFA\Models\Service;

class ServicePackageController extends Controller
{
    public function index(Request $request)
    {
        $query = ServicePackage::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $servicePackages = $query->paginate(15);
        $services = Service::all();
        return view('lscefa::service_packages.index', compact('servicePackages', 'services'));
    }

    public function create()
    {
        $services = Service::all();
        return view('lscefa::service_packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'accredited' => 'boolean',
            'included_services' => 'array'
        ]);

        ServicePackage::create($request->all());

        return redirect()->route('lscefa.quality.service_packages.index')
            ->with('success', 'Paquete de servicio creado exitosamente.');
    }

    public function edit(ServicePackage $servicePackage)
    {
        $services = Service::all();
        return view('lscefa::service_packages.edit', compact('servicePackage', 'services'));
    }

    public function update(Request $request, ServicePackage $servicePackage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'accredited' => 'boolean',
            'included_services' => 'array'
        ]);

        $servicePackage->update($request->all());

        return redirect()->route('lscefa.quality.service_packages.index')
            ->with('success', 'Paquete de servicio actualizado exitosamente.');
    }

    public function destroy(ServicePackage $servicePackage)
    {
        try {
            $servicePackage->delete();
            return redirect()->route('lscefa.quality.service_packages.index')
                ->with('success', 'Paquete de servicio eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->route('lscefa.quality.service_packages.index')
                    ->with('error', 'No se puede eliminar el paquete de servicio porque existen registros asociados.');
            }
            throw $e;
        }
    }
} 