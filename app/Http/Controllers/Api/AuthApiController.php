<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\KYCValidation;
use App\Services\CPFValidationService;
use App\Services\FileUploadService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

class AuthApiController extends Controller
{
    /**
     * Login - retorna token Sanctum
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        $user = Auth::user();
        if (!$user->profile) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Usuário sem perfil completo. Entre em contato com o suporte.',
            ], 403);
        }

        if ($user->is_blocked ?? false) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Conta bloqueada. Entre em contato com o suporte.',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userResponse($user),
        ]);
    }

    /**
     * Cria UploadedFile a partir de string base64 (para uso no app mobile).
     */
    private function createUploadedFileFromBase64(string $base64, string $defaultName, string $defaultMime = 'image/jpeg'): ?UploadedFile
    {
        $decoded = base64_decode($base64, true);
        if ($decoded === false || strlen($decoded) === 0) {
            return null;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'cb_');
        if ($tmp === false) {
            return null;
        }
        file_put_contents($tmp, $decoded);
        $ext = $defaultMime === 'application/pdf' ? 'pdf' : 'jpg';
        $name = pathinfo($defaultName, PATHINFO_EXTENSION) ? $defaultName : $defaultName . '.' . $ext;
        $file = new UploadedFile($tmp, $name, $defaultMime, 0, true);
        return $file;
    }

    /**
     * Registro via API (app mobile) – aceita fotos em base64 e cria usuário + perfil.
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|min:6|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_type' => 'required|in:curioso,inteligente,sabio',
            'full_name' => 'required|string|min:6|max:255',
            'cpf' => 'required|string|min:11|max:14',
            'phone' => 'required|string|max:20',
            'cep' => 'required|string|min:8|max:9',
            'street' => 'required|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'house_number' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'complement' => 'required|string|max:120',
            'ingress_date' => 'required|date|before_or_equal:today',
            'whatsapp' => 'nullable|string|max:20',
            'has_quota' => 'required|in:0,1,2,3',
            'user_photo_base64' => 'required|string',
            'document_photo_base64' => 'required|string',
            'document_type' => 'required|in:rg,cnh',
            'accept_terms' => 'required|accepted',
            'accept_promotional_periods' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cpfService = new CPFValidationService();
        $cpfValidation = $cpfService->validateCPF($request->cpf);
        if (!$cpfValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $cpfValidation['message'],
                'errors' => ['cpf' => [$cpfValidation['message']]],
            ], 422);
        }

        $userPhotoFile = $this->createUploadedFileFromBase64(
            $request->user_photo_base64,
            'user_photo.jpg',
            $request->input('user_photo_mime', 'image/jpeg')
        );
        $documentPhotoFile = $this->createUploadedFileFromBase64(
            $request->document_photo_base64,
            'document.jpg',
            $request->input('document_photo_mime', 'image/jpeg')
        );

        if (!$userPhotoFile || !$documentPhotoFile) {
            return response()->json([
                'success' => false,
                'message' => 'Fotos inválidas. Envie imagens em base64 (JPG ou PNG).',
            ], 422);
        }

        $tempPaths = [$userPhotoFile->getRealPath(), $documentPhotoFile->getRealPath()];

        try {
            $fileService = new FileUploadService();
            $userPhoto = $fileService->uploadUserPhoto($userPhotoFile);
            $documentPhoto = $fileService->uploadDocumentPhoto($documentPhotoFile, $request->document_type);
        } catch (\Throwable $e) {
            Log::error('API register upload error: ' . $e->getMessage());
            foreach ($tempPaths as $p) {
                if (is_string($p) && file_exists($p)) {
                    @unlink($p);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar fotos. Use JPG, PNG ou PDF.',
            ], 422);
        }

        foreach ($tempPaths as $p) {
            if (is_string($p) && file_exists($p)) {
                @unlink($p);
            }
        }

        if (!($userPhoto['valid'] ?? true) || !isset($userPhoto['path'])) {
            return response()->json([
                'success' => false,
                'message' => $userPhoto['message'] ?? 'Foto do usuário inválida.',
            ], 422);
        }
        if (!($documentPhoto['valid'] ?? true) || !isset($documentPhoto['path'])) {
            return response()->json([
                'success' => false,
                'message' => $documentPhoto['message'] ?? 'Documento inválido.',
            ], 422);
        }

        $cep = $request->cep;
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) === 8) {
            $cep = substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
        }

        $hasQuota = (int) $request->has_quota;
        $allowedUses = $request->allowed_uses;
        if (!is_array($allowedUses)) {
            $allowedUses = $hasQuota === 0 ? ['rent', 'buy'] : ($hasQuota === 1 ? ['rent', 'exchange', 'sell', 'buy'] : ['rent', 'buy']);
        }
        if ($hasQuota === 0) {
            $allowedUses = array_values(array_intersect($allowedUses, ['rent', 'buy']));
            if (empty($allowedUses)) {
                $allowedUses = ['rent', 'buy'];
            }
        }
        if (in_array($hasQuota, [2, 3], true)) {
            $allowedUses = array_values(array_intersect($allowedUses ?? [], ['rent', 'buy']));
            if (empty($allowedUses)) {
                $allowedUses = ['rent', 'buy'];
            }
        }

        try {
            // Provisório: mesmo critério do cadastro web — conta aprovada na criação.
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'whatsapp' => $request->whatsapp,
                'ingress_date' => $request->ingress_date,
                'profile_approval_status' => User::PROFILE_APPROVAL_APPROVED,
                'profile_approved_at' => now(),
                'show_approval_success_modal' => false,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'profile_type' => $request->profile_type,
                'full_name' => $request->full_name,
                'cpf' => $cpfValidation['cpf'],
                'phone' => $request->phone,
                'cep' => $cep,
                'street' => $request->street,
                'neighborhood' => $request->neighborhood,
                'city' => $request->city,
                'state' => $request->state,
                'house_number' => $request->house_number,
                'complement' => $request->complement,
                'user_photo_path' => $userPhoto['path'],
                'rg_photo_path' => $request->document_type === 'rg' ? $documentPhoto['path'] : null,
                'cnh_photo_path' => $request->document_type === 'cnh' ? $documentPhoto['path'] : null,
                'has_quota' => $hasQuota,
                'quota_status' => null,
                'quota_payment_deadline' => null,
                'kyc_completed' => true,
                'kyc_completed_at' => now(),
                'kyc_status' => 'approved',
                'allowed_uses' => in_array($hasQuota, [2, 3], true) ? null : $allowedUses,
                'gestor_allowed_uses' => in_array($hasQuota, [2, 3], true) ? $allowedUses : null,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
            ]);

            KYCValidation::create([
                'user_id' => $user->id,
                'document_type' => $request->document_type,
                'document_photo_path' => $documentPhoto['path'],
                'validation_status' => 'pending',
            ]);

            app()->terminating(function () use ($user) {
                try {
                    $emailService = new EmailService();
                    $emailService->sendWelcomeEmail($user, null, null);
                } catch (\Throwable $e) {
                    Log::error('API register welcome email error: ' . $e->getMessage());
                }
            });

            Auth::login($user);
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso.',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->userResponse($user),
            ], 201);
        } catch (\Exception $e) {
            Log::error('API register error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar conta. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Logout - revoga tokens
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    private function userResponse($user)
    {
        $user->load('profile');
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp' => $user->whatsapp,
            'profile' => $user->profile ? [
                'id' => $user->profile->id,
                'full_name' => $user->profile->full_name,
                'profile_type' => $user->profile->profile_type,
                'kyc_completed' => $user->profile->kyc_completed ?? false,
            ] : null,
        ];
    }
}
