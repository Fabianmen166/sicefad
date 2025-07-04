<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\Quote;

class QuoteSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = Quote::query()->with(['customer', 'user', 'quoteServices.service', 'quoteServices.servicePackage']);
        if ($request->filled('id')) {
            $query->where('quote_id', 'like', '%' . $request->id . '%');
        }
        if ($request->filled('nit')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('tax_id', 'like', '%' . $request->nit . '%');
            });
        }
        if ($request->filled('applicant')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('applicant', 'like', '%' . $request->applicant . '%');
            });
        }
        if ($request->filled('email')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }
        $quotes = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($quotes);
    }
} 