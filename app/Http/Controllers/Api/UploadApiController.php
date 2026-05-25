<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadApiController extends Controller
{
    /**
     * Store temporary upload (proof) and return path.
     * Public endpoint used by AJAX in the registration flow.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            ]);

            $file = $request->file('file');
            $filename = Str::random(12) . '_' . $file->getClientOriginalName();
            // store in public disk under uploads/tmp
            $path = $file->storeAs('uploads/tmp', $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ]);
        } catch (\Exception $e) {
            Log::error('Upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivo.',
            ], 500);
        }
    }
}

