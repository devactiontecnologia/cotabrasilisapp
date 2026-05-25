<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckKYCCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for guests
        if (!Auth::check()) {
            return $next($request);
        }
        
        $user = Auth::user();
        
        // Skip for admin users
        if ($user->hasAdminPrivileges()) {
            return $next($request);
        }
        
        // Skip for certain routes that don't require KYC
        $excludedRoutes = [
            'logout',
            'profile.complete',
            'kyc.*',
            'register',
            'login',
            'password.*',
            'verification.*'
        ];
        
        if ($this->isExcludedRoute($request, $excludedRoutes)) {
            return $next($request);
        }
        
        // Check if user has profile
        if (!$user->profile) {
            return redirect()->route('profile.complete')
                ->with('warning', 'Complete seu cadastro para continuar usando a plataforma.');
        }
        
        // Check if KYC is complete
        if (!$user->profile->isKYCComplete()) {
            $missingFields = $this->getMissingKYCFields($user->profile);
            
            return redirect()->route('profile.complete')
                ->with('warning', 'Complete a verificação de identidade (KYC) para continuar.')
                ->with('missing_fields', $missingFields);
        }
        
        return $next($request);
    }
    
    /**
     * Check if current route is excluded from KYC check.
     */
    private function isExcludedRoute(Request $request, array $excludedRoutes): bool
    {
        $currentRoute = $request->route()->getName();
        
        foreach ($excludedRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get missing KYC fields for user.
     */
    private function getMissingKYCFields($profile): array
    {
        $missing = [];
        
        if (empty($profile->user_photo_path)) {
            $missing[] = 'Foto do usuário';
        }
        
        if (empty($profile->rg_photo_path) && empty($profile->cnh_photo_path)) {
            $missing[] = 'Foto do RG ou CNH';
        }
        
        if (empty($profile->quota_contract_photo_path)) {
            $missing[] = 'Foto do contrato da cota';
        }
        
        if ($profile->is_authorized_user && empty($profile->authorization_document_path)) {
            $missing[] = 'Documento de autorização';
        }
        
        if (empty($profile->gov_br_signature)) {
            $missing[] = 'Assinatura digital via Gov.br';
        }
        
        return $missing;
    }
}
