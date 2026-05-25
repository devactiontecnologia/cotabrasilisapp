<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PublicMediaController extends Controller
{
    /**
     * Serve arquivos do disco public (evita depender de public/storage symlink).
     */
    public function show(string $path): Response
    {
        $path = str_replace(['\\', '..'], ['/', ''], $path);
        $path = ltrim($path, '/');

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
