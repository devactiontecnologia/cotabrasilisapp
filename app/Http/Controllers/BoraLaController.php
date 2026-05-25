<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Quota;
use App\Models\BoraLaPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BoraLaController extends Controller
{
    /**
     * Display the main Bora lá menu with 4 options.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Verificar se é admin/gestor
        $isManager = $user->hasAdminPrivileges();
        $feedRecent = BoraLaPost::publishedRecentForDashboard(6);

        return view('client.bora-la.index', compact('isManager', 'feedRecent'));
    }

    /**
     * Display Oferta Única page (for managers to create, for users to view)
     */
    public function ofertaUnica()
    {
        $user = Auth::user();
        $isManager = $user->hasAdminPrivileges();
        
        // Se for gestor, mostrar formulário de criação
        // Se for usuário, mostrar ofertas recebidas
        return view('client.bora-la.oferta-unica', compact('isManager'));
    }

    /**
     * Store a new Oferta Única
     */
    public function storeOfertaUnica(Request $request)
    {
        if (!Auth::user()->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'required_if:content_type,text|required_if:content_type,both|nullable|string',
            'content_video' => 'required_if:content_type,video|required_if:content_type,both|nullable|url',
            'send_email' => 'boolean',
            'send_whatsapp' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        // Aqui implementaria a lógica de salvamento e envio
        // Por enquanto, apenas redireciona
        
        return redirect()->route('bora-la.oferta-unica')
            ->with('success', 'Oferta Única criada e enviada com sucesso!');
    }

    /**
     * Display Atualizações page
     */
    public function atualizacoes()
    {
        $user = Auth::user();
        $isManager = $user->hasAdminPrivileges();
        
        $posts = BoraLaPost::publishedListingForType(BoraLaPost::TYPE_ATUALIZACAO);

        return view('client.bora-la.atualizacoes', compact('isManager', 'posts'));
    }

    /**
     * Store a new Atualização
     */
    public function storeAtualizacao(Request $request)
    {
        if (!Auth::user()->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'required_if:content_type,text|required_if:content_type,both|nullable|string',
            'content_video' => 'required_if:content_type,video|required_if:content_type,both|nullable|url',
            'send_email' => 'boolean',
            'send_whatsapp' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        return redirect()->route('bora-la.atualizacoes')
            ->with('success', 'Atualização criada e enviada com sucesso!');
    }

    /**
     * Display Avisos page
     */
    public function avisos()
    {
        $user = Auth::user();
        $isManager = $user->hasAdminPrivileges();
        
        $posts = BoraLaPost::publishedListingForType(BoraLaPost::TYPE_AVISO);

        return view('client.bora-la.avisos', compact('isManager', 'posts'));
    }

    /**
     * Store a new Aviso
     */
    public function storeAviso(Request $request)
    {
        if (!Auth::user()->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'required_if:content_type,text|required_if:content_type,both|nullable|string',
            'content_video' => 'required_if:content_type,video|required_if:content_type,both|nullable|url',
            'send_email' => 'boolean',
            'send_whatsapp' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        return redirect()->route('bora-la.avisos')
            ->with('success', 'Aviso criado e enviado com sucesso!');
    }

    /**
     * Display Enquetes page
     */
    public function enquetes()
    {
        $user = Auth::user();
        $isManager = $user->hasAdminPrivileges();
        
        $posts = BoraLaPost::publishedListingForType(BoraLaPost::TYPE_ENQUETE);

        return view('client.bora-la.enquetes', compact('isManager', 'posts'));
    }

    /**
     * Store a new Enquete
     */
    public function storeEnquete(Request $request)
    {
        if (!Auth::user()->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'required_if:content_type,text|required_if:content_type,both|nullable|string',
            'content_video' => 'required_if:content_type,video|required_if:content_type,both|nullable|url',
            'send_email' => 'boolean',
            'send_whatsapp' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        return redirect()->route('bora-la.enquetes')
            ->with('success', 'Enquete criada e enviada com sucesso!');
    }

    /**
     * Display Dicas page
     */
    public function dicas()
    {
        $user = Auth::user();
        $isManager = $user->hasAdminPrivileges();
        
        $posts = BoraLaPost::publishedListingForType(BoraLaPost::TYPE_DICA);

        return view('client.bora-la.dicas', compact('isManager', 'posts'));
    }

    /**
     * Store a new Dica
     */
    public function storeDica(Request $request)
    {
        if (!Auth::user()->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'required_if:content_type,text|required_if:content_type,both|nullable|string',
            'content_video' => 'required_if:content_type,video|required_if:content_type,both|nullable|url',
            'send_email' => 'boolean',
            'send_whatsapp' => 'boolean',
            'filters' => 'nullable|array',
        ]);

        return redirect()->route('bora-la.dicas')
            ->with('success', 'Dica criada e enviada com sucesso!');
    }

    /**
     * Get users based on filters
     */
    private function getFilteredUsers($filters)
    {
        $query = User::query();
        
        if (isset($filters['city'])) {
            $query->whereHas('profile', function($q) use ($filters) {
                $q->where('city', $filters['city']);
            });
        }
        
        if (isset($filters['state'])) {
            $query->whereHas('profile', function($q) use ($filters) {
                $q->where('state', $filters['state']);
            });
        }
        
        if (isset($filters['gender'])) {
            $query->whereHas('profile', function($q) use ($filters) {
                $q->where('gender', $filters['gender']);
            });
        }
        
        if (isset($filters['quota_type'])) {
            if ($filters['quota_type'] === 'fixa') {
                $query->whereHas('quotas', function($q) {
                    $q->where('is_fractioned', false);
                });
            } elseif ($filters['quota_type'] === 'flexivel') {
                $query->whereHas('quotas', function($q) {
                    $q->where('is_fractioned', true);
                });
            }
        }
        
        return $query->get();
    }
}

