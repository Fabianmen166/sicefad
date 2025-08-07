<?php

namespace Modules\LSCEFA\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LSCEFA\Models\Service;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'lscefa.role:lscefa.quality,lscefa.admin']);
    }

    public function index(Request $request)
    {
        $query = Service::query();
        if ($request->filled('search')) {
            $query->where('descripcion', 'like', "%{$request->search}%");
        }
        $services = $query->paginate(15);
        return view('lscefa::services.index', compact('services'));
    }

    public function create()
    {
        return view('lscefa::services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);

        // Manejar el campo acreditado
        $validated['acreditado'] = $request->input('acreditado') ? true : false;

        Service::create($validated);
        return redirect()->route('lscefa.quality.services.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Service $service)
    {
        return view('lscefa::services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);

        // Manejar el campo acreditado
        $validated['acreditado'] = $request->input('acreditado') ? true : false;

        $service->update($validated);
        return redirect()->route('lscefa.quality.services.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }
}