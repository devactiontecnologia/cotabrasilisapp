<?php

namespace App\Http\Controllers;

use App\Models\PlatformAuthorizationDocument;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class AdminPlatformDocumentsController extends Controller
{
    public function edit()
    {
        $documents = PlatformAuthorizationDocument::ordered()->get();

        return view('admin.platform-documents.edit', compact('documents'));
    }

    public function update(Request $request, FileUploadService $fileUploadService)
    {
        $slugs = PlatformAuthorizationDocument::query()->pluck('slug')->all();
        $rules = [];
        foreach ($slugs as $slug) {
            $rules['documents.' . $slug] = 'nullable|file|mimes:pdf,jpeg,jpg,png|max:10240';
        }
        $request->validate($rules);

        $updated = false;
        foreach (PlatformAuthorizationDocument::all() as $doc) {
            $file = $request->file('documents.' . $doc->slug);
            if (!$file) {
                continue;
            }
            $result = $fileUploadService->uploadPlatformAuthorizationDocument($file);
            if (!($result['valid'] ?? false)) {
                return redirect()
                    ->route('admin.platform-documents.edit')
                    ->withErrors(['documents.' . $doc->slug => $result['message'] ?? 'Arquivo inválido.'])
                    ->withInput();
            }
            if (!empty($doc->file_path)) {
                $fileUploadService->deleteFile($doc->file_path);
            }
            $doc->update(['file_path' => $result['path']]);
            $updated = true;
        }

        return redirect()
            ->route('admin.platform-documents.edit')
            ->with($updated ? 'success' : 'info', $updated ? 'Documentos padrão atualizados.' : 'Nenhum arquivo novo foi selecionado.');
    }
}
