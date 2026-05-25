<?php

namespace App\Http\Controllers;

use App\Models\PlatformAuthorizationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\UserProfile;
use App\Services\FileUploadService;

class UserProfileController extends Controller
{
    // Middleware is handled in routes/web.php

    /**
     * Show the profile completion form.
     */
    public function showComplete()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        return view('profile.complete', compact('profile'));
    }

    /**
     * Complete user profile with additional information.
     */
    public function complete(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        $validator = Validator::make($request->all(), [
            'cnh_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'rg_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'user_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'quota_contract_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'quota_paid_off' => 'boolean',
            'hotel_operational' => 'boolean',
            'terms_accepted' => 'required|accepted',
        ], [
            'cnh_photo.mimes' => 'A foto da CNH deve ser um arquivo JPEG, JPG ou PNG.',
            'cnh_photo.max' => 'A foto da CNH não pode ser maior que 2MB.',
            'rg_photo.mimes' => 'A foto do RG deve ser um arquivo JPEG, JPG ou PNG.',
            'rg_photo.max' => 'A foto do RG não pode ser maior que 2MB.',
            'user_photo.mimes' => 'A foto do usuário deve ser um arquivo JPEG, JPG ou PNG.',
            'user_photo.max' => 'A foto do usuário não pode ser maior que 2MB.',
            'quota_contract_photo.mimes' => 'O contrato da cota deve ser um arquivo JPEG, JPG ou PNG.',
            'quota_contract_photo.max' => 'O contrato da cota não pode ser maior que 2MB.',
            'terms_accepted.required' => 'Você deve aceitar os termos de autorização.',
            'terms_accepted.accepted' => 'Você deve aceitar os termos de autorização.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'quota_paid_off' => $request->has('quota_paid_off'),
            'hotel_operational' => $request->has('hotel_operational'),
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ];

        // Handle file uploads
        if ($request->hasFile('cnh_photo')) {
            $data['cnh_photo_path'] = $request->file('cnh_photo')->store('documents', 'public');
        }

        if ($request->hasFile('rg_photo')) {
            $data['rg_photo_path'] = $request->file('rg_photo')->store('documents', 'public');
        }

        if ($request->hasFile('user_photo')) {
            $storedPath = $this->storeUserPhoto($request->file('user_photo'), $profile);
            if ($storedPath === null) {
                return redirect()->back()
                    ->withErrors(['user_photo' => 'Não foi possível salvar a foto. Use JPG ou PNG.'])
                    ->withInput();
            }
            $data['user_photo_path'] = $storedPath;
        }

        if ($request->hasFile('quota_contract_photo')) {
            $data['quota_contract_photo_path'] = $request->file('quota_contract_photo')->store('contracts', 'public');
            $data['has_quota'] = true;
        }

        $profile->update($data);

        return redirect()->route('dashboard')
            ->with('success', 'Perfil completado com sucesso!');
    }

    /**
     * Show user profile.
     */
    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $platformAuthorizationDocuments = PlatformAuthorizationDocument::ordered()->get();

        return view('profile.show', compact('profile', 'platformAuthorizationDocuments'));
    }

    /**
     * Show profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        return view('profile.edit', compact('profile'));
    }

    /**
     * Update user profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        $validator = Validator::make($request->all(), [
            'profile_type' => 'required|in:curioso,inteligente,sabio',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'cep' => 'required|string|size:9|regex:/^\d{5}-\d{3}$/',
            'street' => 'required|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'house_number' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'complement' => 'nullable|string|max:120',
            'whatsapp' => 'nullable|string|max:20',
            'cnh_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'rg_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'user_photo' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
        ], [
            'full_name.required' => 'O nome completo é obrigatório.',
            'phone.required' => 'O telefone é obrigatório.',
            'cep.required' => 'O CEP é obrigatório.',
            'cep.size' => 'O CEP deve ter 9 caracteres.',
            'cep.regex' => 'O CEP deve estar no formato 00000-000.',
            'street.required' => 'A rua é obrigatória.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            'city.required' => 'A cidade é obrigatória.',
            'state.required' => 'O estado é obrigatório.',
            'state.max' => 'O estado deve ter no máximo 255 caracteres.',
            'house_number.required' => 'O número da residência é obrigatório.',
            'house_number.regex' => 'O número da residência deve conter apenas números.',
            'cnh_photo.mimes' => 'A foto da CNH deve ser um arquivo JPEG, JPG ou PNG.',
            'cnh_photo.max' => 'A foto da CNH não pode ser maior que 2MB.',
            'rg_photo.mimes' => 'A foto do RG deve ser um arquivo JPEG, JPG ou PNG.',
            'rg_photo.max' => 'A foto do RG não pode ser maior que 2MB.',
            'user_photo.mimes' => 'A foto do usuário deve ser um arquivo JPEG, JPG ou PNG.',
            'user_photo.max' => 'A foto do usuário não pode ser maior que 2MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'profile_type' => $request->profile_type,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'cep' => $request->cep,
            'street' => $request->street,
            'neighborhood' => $request->neighborhood,
            'city' => $request->city,
            'state' => $request->state,
            'house_number' => $request->house_number,
            'complement' => $request->complement,
        ];

        // Update user WhatsApp
        $user->update(['whatsapp' => $request->whatsapp]);

        // Handle file uploads
        if ($request->hasFile('cnh_photo')) {
            if ($profile->cnh_photo_path) {
                Storage::disk('public')->delete($profile->cnh_photo_path);
            }
            $data['cnh_photo_path'] = $request->file('cnh_photo')->store('documents', 'public');
        }

        if ($request->hasFile('rg_photo')) {
            if ($profile->rg_photo_path) {
                Storage::disk('public')->delete($profile->rg_photo_path);
            }
            $data['rg_photo_path'] = $request->file('rg_photo')->store('documents', 'public');
        }

        if ($request->hasFile('user_photo')) {
            $storedPath = $this->storeUserPhoto($request->file('user_photo'), $profile);
            if ($storedPath === null) {
                return redirect()->back()
                    ->withErrors(['user_photo' => 'Não foi possível salvar a foto. Use JPG ou PNG.'])
                    ->withInput();
            }
            $data['user_photo_path'] = $storedPath;
        }

        $profile->update($data);

        return redirect()->route('profile.show')
            ->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Atualiza apenas o tipo de perfil (Curioso / Inteligente / Sábio) a partir da página "Meu perfil".
     */
    public function updateProfileType(Request $request)
    {
        $request->validate([
            'profile_type' => 'required|in:curioso,inteligente,sabio',
        ], [
            'profile_type.required' => 'Selecione um tipo de perfil.',
            'profile_type.in' => 'Tipo de perfil inválido.',
        ]);

        $profile = Auth::user()->profile;
        if (!$profile) {
            return redirect()->route('dashboard')
                ->with('error', 'Perfil não encontrado.');
        }

        $profile->update(['profile_type' => $request->profile_type]);

        return redirect()->route('profile.show')
            ->with('success', 'Tipo de perfil atualizado. Taxas, publicação de cotas e demais limites passam a seguir o novo perfil.');
    }

    /**
     * Salva foto de perfil no mesmo padrão do cadastro (user_photos + otimização).
     */
    private function storeUserPhoto(\Illuminate\Http\UploadedFile $file, ?UserProfile $profile): ?string
    {
        if ($profile?->user_photo_path) {
            Storage::disk('public')->delete($profile->user_photo_path);
        }

        $uploaded = (new FileUploadService())->uploadUserPhoto($file);
        if (empty($uploaded['valid']) || $uploaded['valid'] === false) {
            return null;
        }

        return $uploaded['path'] ?? null;
    }

    /**
     * Get profile configuration based on profile type.
     */
    public function getProfileConfig()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        return response()->json($profile->getProfileConfig());
    }
}
