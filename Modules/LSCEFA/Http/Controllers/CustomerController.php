<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\Customer;
use Modules\LSCEFA\Models\CustomerType;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('customerType');
        
        if ($request->has('customer_type_filter') && $request->customer_type_filter != '') {
            $query->where('customer_type_id', $request->customer_type_filter);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_id', 'like', "%$search%")
                  ->orWhere('tax_id', 'like', "%$search%")
                  ->orWhere('applicant', 'like', "%$search%")
                ;
            });
        }
        
        $customers = $query->paginate(15);
        $customerTypes = CustomerType::all();
        
        return view('lscefa::customers.index', compact('customers', 'customerTypes'));
    }

    public function create()
    {
        $customerTypes = CustomerType::all();
        return view('lscefa::customers.create', compact('customerTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'tax_id' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'customer_type_id' => 'required|exists:customer_types,customer_type_id'
        ]);

        Customer::create($request->all());

        return redirect()->route('lscefa.quality.customers.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function edit(Customer $customer)
    {
        $customerTypes = CustomerType::all();
        return view('lscefa::customers.edit', compact('customer', 'customerTypes'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'applicant' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'tax_id' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'customer_type_id' => 'required|exists:customer_types,customer_type_id'
        ]);

        $customer->update($request->all());

        return redirect()->route('lscefa.quality.customers.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('lscefa.quality.customers.index')
                ->with('success', 'Cliente eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->route('lscefa.quality.customers.index')
                    ->with('error', 'No se puede eliminar el cliente porque existen registros asociados.');
            }
            throw $e;
        }
    }

    public function searchAjax(Request $request)
    {
        $query = Customer::with('customerType');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_id', 'like', "%$search%")
                  ->orWhere('tax_id', 'like', "%$search%")
                  ->orWhere('applicant', 'like', "%$search%")
                ;
            });
        }
        $customers = $query->get();
        return view('lscefa::customers.partials.table', compact('customers'))->render();
    }
} 