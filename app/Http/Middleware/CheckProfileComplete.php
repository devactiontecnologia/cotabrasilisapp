<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user && $user->profile) {
            $profile = $user->profile;
            
            // Check if profile is incomplete
            if (!$profile->full_name || !$profile->phone || !$profile->cep || !$profile->street || 
                !$profile->neighborhood || !$profile->city || !$profile->state || !$profile->house_number || 
                !$profile->terms_accepted) {
                // Don't redirect if already on profile completion page
                if (!$request->routeIs('profile.complete')) {
                    return redirect()->route('profile.complete')
                        ->with('warning', 'Complete seu perfil para continuar usando a plataforma.');
                }
            }
        }
        
        return $next($request);
    }
}
