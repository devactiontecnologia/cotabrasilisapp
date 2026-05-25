<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Retorna o usuário autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('profile');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'profile' => $user->profile ? [
                    'id' => $user->profile->id,
                    'full_name' => $user->profile->full_name,
                    'profile_type' => $user->profile->profile_type,
                    'kyc_completed' => $user->profile->kyc_completed ?? false,
                    'cpf' => substr($user->profile->cpf ?? '', 0, 3) . '.***.***-**',
                    'city' => $user->profile->city ?? null,
                    'state' => $user->profile->state ?? null,
                ] : null,
            ],
        ]);
    }
}
