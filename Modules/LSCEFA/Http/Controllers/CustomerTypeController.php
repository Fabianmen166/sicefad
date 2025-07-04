<?php

namespace Modules\LSCEFA\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LSCEFA\Models\CustomerType;

class CustomerTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'lscefa.role:lscefa.quality,lscefa.admin']);
    }

    public function index()
    {
        $customerTypes = CustomerType::all();
        return view('lscefa::customer_types.index', compact('customerTypes'));
    }

    public function create()
    {
        return view('lscefa::customer_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_types,name',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'required|string'
        ]);

        CustomerType::create($validated);

        return redirect()->route('lscefa.quality.customer_types.index')
            ->with('success', 'Tipo de cliente creado exitosamente.');
    }

    public function edit(CustomerType $customerType)
    {
        return view('lscefa::customer_types.edit', compact('customerType'));
    }

    public function update(Request $request, CustomerType $customerType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_types,name,' . $customerType->customer_type_id . ',customer_type_id',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'required|string'
        ]);

        $customerType->update($validated);

        return redirect()->route('lscefa.quality.customer_types.index')
            ->with('success', 'Tipo de cliente actualizado exitosamente.');
    }

    public function destroy(CustomerType $customerType)
    {
        try {
            $customerType->delete();
            return redirect()->route('lscefa.quality.customer_types.index')
                ->with('success', 'Tipo de cliente eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') { // Violación de restricción de integridad
                return redirect()->route('lscefa.quality.customer_types.index')
                    ->with('error', 'No se puede eliminar el tipo de cliente porque existen clientes asociados.');
            }
            throw $e;
        }
    }
} 