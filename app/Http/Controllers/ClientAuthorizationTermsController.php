<?php

namespace App\Http\Controllers;

use App\Models\PlatformAuthorizationDocument;
use Illuminate\Support\Facades\Auth;

class ClientAuthorizationTermsController extends Controller
{
    /**
     * Termos: modelos Cota Brasilis + termo de hospedagem enviado no cadastro.
     */
    public function show()
    {
        $profile = Auth::user()?->profile;
        $storagePath = $profile?->hospitality_authorization_term_path
            ?: $profile?->gestor_hospitality_authorization_term_path;

        $termUrl = $storagePath ? asset('storage/' . $storagePath) : null;
        $ext = $storagePath ? strtolower(pathinfo($storagePath, PATHINFO_EXTENSION) ?: '') : '';
        $downloadName = 'Termo-de-Autorizacao-de-Hospedagem-para-Terceiros' . ($ext !== '' ? '.' . $ext : '');

        $platformAuthorizationDocuments = PlatformAuthorizationDocument::ordered()->get();

        return view('client.authorization-terms', [
            'termUrl' => $termUrl,
            'downloadName' => $downloadName,
            'hasTerm' => (bool) $storagePath,
            'platformAuthorizationDocuments' => $platformAuthorizationDocuments,
        ]);
    }
}
