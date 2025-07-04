<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\LSCEFA\Models\Quote;
use Modules\LSCEFA\Models\QuoteFile;

class QuoteFileController extends Controller
{
    public function index($quote_id)
    {
        $quote = Quote::findOrFail($quote_id);
        $files = $quote->files()->get();
        return response()->json($files);
    }

    public function store(Request $request, $quote_id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);
        $quote = Quote::findOrFail($quote_id);
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $filename = 'quote_' . $quote->quote_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/comprobantes/' . $quote->quote_id, $filename);
            $quoteFile = new QuoteFile([
                'filename' => $filename,
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
            $quote->files()->save($quoteFile);
            return response()->json(['success' => true, 'file' => $quoteFile]);
        }
        return response()->json(['success' => false], 400);
    }

    public function destroy($quote_id, $file_id)
    {
        $file = QuoteFile::where('quote_id', $quote_id)->findOrFail($file_id);
        Storage::delete($file->path);
        $file->delete();
        return response()->json(['success' => true]);
    }
} 