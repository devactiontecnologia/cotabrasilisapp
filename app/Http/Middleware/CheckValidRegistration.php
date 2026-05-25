<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckValidRegistration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Administradores e moderadores podem acessar sem perfil completo (painel admin)
            if ($user instanceof User && ($user->is_admin || $user->role === 'admin' || $user->role === 'moderator')) {
                return $next($request);
            }
            // Se o perfil já foi aprovado pela equipe, não bloquear por validações legadas de cadastro.
            if ($user instanceof User && $user->isProfileApproved()) {
                return $next($request);
            }
            $profile = $user->profile;
            
            // Verificar se o cadastro é válido
            if (!$this->isRegistrationValid($user, $profile)) {
                // Identificar qual campo está faltando para dar mensagem mais específica
                $missingFields = $this->getMissingFields($user, $profile);
                
                // Bloquear acesso total até regularização
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors([
                        'email' => 'Seu cadastro está incompleto. Campos faltando: ' . implode(', ', $missingFields) . '. Entre em contato com o suporte ou complete seu cadastro.'
                    ]);
            }
        }

        return $next($request);
    }
    
    /**
     * Obter lista de campos faltando
     */
    private function getMissingFields($user, $profile): array
    {
        $missing = [];
        
        if (!$profile) {
            return ['Perfil não encontrado'];
        }
        
        $requiredFields = [
            'full_name' => 'Nome Completo',
            'cpf' => 'CPF',
            'phone' => 'Telefone',
            'cep' => 'CEP',
            'street' => 'Rua',
            'neighborhood' => 'Bairro',
            'city' => 'Cidade',
            'state' => 'Estado',
            'house_number' => 'Número da Casa',
            'user_photo_path' => 'Foto do Usuário',
            'has_quota' => 'Informação sobre Cota',
            'profile_type' => 'Tipo de Perfil'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (in_array($field, ['has_quota'])) {
                if ($profile->$field === null) {
                    $missing[] = $label;
                }
            } else {
                if (empty($profile->$field)) {
                    $missing[] = $label;
                }
            }
        }
        
        // Verificar documento (RG OU CNH - não ambos)
        if (empty($profile->rg_photo_path) && empty($profile->cnh_photo_path)) {
            $missing[] = 'Foto do RG ou CNH';
        }
        
        // Verificar contrato da cota apenas se é proprietário (has_quota = '1' ou 1)
        // has_quota pode ser: '0' (não possui), '1' (proprietário), '2' (gestor), ou boolean
        $hasQuotaValue = $profile->getRawOriginal('has_quota') ?? $profile->has_quota;
        $isOwner = $hasQuotaValue == '1' || $hasQuotaValue === 1 || $hasQuotaValue === true;
        if ($isOwner) {
            $hasContract = !empty($profile->quota_contract_photo_path) || 
                          (!empty($profile->quota_contracts) && is_array($profile->quota_contracts) && count($profile->quota_contracts) > 0);
            if (!$hasContract) {
                $missing[] = 'Contrato da Cota';
            }
        }
        
        // Verificar documento de autorização se é gestor (2) ou titular que delega (3)
        $needsGestorAuthDoc = $hasQuotaValue == '2' || $hasQuotaValue === 2
            || $hasQuotaValue == '3' || $hasQuotaValue === 3;
        if ($needsGestorAuthDoc) {
            if (empty($profile->gestor_authorization_document_path)) {
                $missing[] = 'Documento de Autorização do Gestor';
            }
        }
        
        if (!empty($profile->is_authorized_user) && $profile->is_authorized_user == true && empty($profile->authorization_document_path)) {
            $missing[] = 'Documento de Autorização';
        }
        
        if ($profile->kyc_status === 'rejected') {
            $missing[] = 'KYC Rejeitado';
        }
        
        if (!$this->isValidCPF($profile->cpf)) {
            $missing[] = 'CPF Inválido';
        }
        
        // Verificar se termos foram aceitos
        if (!$profile->terms_accepted) {
            $missing[] = 'Aceite dos Termos';
        }
        
        return $missing;
    }

    /**
     * Verificar se o cadastro do usuário é válido
     */
    private function isRegistrationValid($user, $profile): bool
    {
        // Se não tem perfil, cadastro inválido
        if (!$profile) {
            return false;
        }

        // Verificar campos obrigatórios
        $requiredFields = [
            'full_name',
            'cpf',
            'phone',
            'cep',
            'street',
            'neighborhood',
            'city',
            'state',
            'house_number',
            'user_photo_path',
            'has_quota',
            'profile_type'
        ];

        foreach ($requiredFields as $field) {
            // Para campos booleanos, verificar se não é null
            if (in_array($field, ['has_quota'])) {
                if ($profile->$field === null) {
                    return false;
                }
            } else {
                if (empty($profile->$field)) {
                    return false;
                }
            }
        }

        // Verificar documento (RG OU CNH - não ambos)
        if (empty($profile->rg_photo_path) && empty($profile->cnh_photo_path)) {
            return false;
        }

        // Verificar se tem cota mas não tem contrato (apenas se has_quota = '1' ou 1 - proprietário)
        // has_quota pode ser: '0' (não possui), '1' (proprietário), '2' (gestor), ou boolean
        $hasQuotaValue = $profile->getRawOriginal('has_quota') ?? $profile->has_quota;
        // Verificar se é proprietário (has_quota = '1' ou 1)
        $isOwner = $hasQuotaValue == '1' || $hasQuotaValue === 1 || $hasQuotaValue === true;
        if ($isOwner) {
            $hasContract = !empty($profile->quota_contract_photo_path) || 
                          (!empty($profile->quota_contracts) && is_array($profile->quota_contracts) && count($profile->quota_contracts) > 0);
            if (!$hasContract) {
                return false;
            }
        }
        
        // Gestor (2) ou titular que delega gestão (3): mesmo documento em gestor_authorization_document_path
        $needsGestorAuthDoc = $hasQuotaValue == '2' || $hasQuotaValue === 2
            || $hasQuotaValue == '3' || $hasQuotaValue === 3;
        if ($needsGestorAuthDoc) {
            if (empty($profile->gestor_authorization_document_path)) {
                return false;
            }
        }

        // Verificar se é usuário autorizado mas não tem autorização
        // Só verificar se is_authorized_user for explicitamente true
        if (!empty($profile->is_authorized_user) && $profile->is_authorized_user == true && empty($profile->authorization_document_path)) {
            return false;
        }

        // Verificar se KYC foi rejeitado
        if ($profile->kyc_status === 'rejected') {
            return false;
        }

        // Verificar se CPF é válido e único
        if (!$this->isValidCPF($profile->cpf)) {
            return false;
        }

        // Verificar se termos foram aceitos
        if (!$profile->terms_accepted) {
            return false;
        }

        return true;
    }

    /**
     * Validar CPF
     */
    private function isValidCPF($cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validar dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}
