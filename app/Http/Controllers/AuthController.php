<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\KYCValidation;
use App\Models\Quota;
use App\Services\CPFValidationService;
use App\Services\FileUploadService;
use App\Services\OCRValidationService;
use App\Services\EmailService;
use App\Models\Hotel;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        $hotels = Hotel::orderBy('name')->get();
        
        // Buscar taxas de êxito para cada perfil
        $successFees = [
            'curioso' => \App\Models\SuccessFee::getActiveFeesForProfile('curioso'),
            'inteligente' => \App\Models\SuccessFee::getActiveFeesForProfile('inteligente'),
            'sabio' => \App\Models\SuccessFee::getActiveFeesForProfile('sabio'),
        ];

        return view('auth.register', compact('hotels', 'successFees'));
    }

    // Step 1: show
    public function showRegisterStep1()
    {
        return view('auth.register-step1');
    }

    // Step 1: post
    public function postRegisterStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:6|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // check email uniqueness
        if (User::where('email', $request->email)->exists()) {
            return redirect()->back()->withErrors(['email' => 'Cadastre outro e-mail: este e-mail já está em uso na plataforma.'])->withInput();
        }

        // store in session
        session()->put('register.step1', [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // stored temporarily; will be hashed at final submit
        ]);

        return redirect()->route('register.step2');
    }

    // Step 2: show
    public function showRegisterStep2()
    {
        return view('auth.register-step2');
    }

    // Step 2: post
    public function postRegisterStep2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|min:6|max:255',
            'cpf' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validate CPF (use CPFValidationService if available)
        try {
            $cpfService = new CPFValidationService();
            $cpfValidation = $cpfService->validateCPF($request->cpf);
            if (!$cpfValidation['valid']) {
                return redirect()->back()->withErrors(['cpf' => $cpfValidation['message']])->withInput();
            }
            $cpfFormatted = $cpfValidation['cpf'];
        } catch (\Exception $e) {
            $cpfFormatted = $request->cpf;
        }

        // check cpf uniqueness in user_profiles
        if (\App\Models\UserProfile::where('cpf', $cpfFormatted)->exists()) {
            // keep entered data in session but return error
            session()->put('register.step2', [
                'full_name' => $request->full_name,
                'cpf' => $request->cpf,
                'phone' => $request->phone,
            ]);
            return redirect()->back()->withErrors(['cpf' => 'CPF já cadastrado no sistema.'])->withInput();
        }

        // store step2 in session
        session()->put('register.step2', [
            'full_name' => $request->full_name,
            'cpf' => $cpfFormatted,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'ingress_date' => $request->ingress_date,
        ]);

        // redirect to main register page to continue with remaining steps
        return redirect()->route('register.step3');
    }

    // Step 3: show/post
    public function showRegisterStep3()
    {
        return view('auth.register-step3');
    }

    public function postRegisterStep3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session()->put('register.step3', $request->only(['street','number','city','state','postal_code','country']));

        return redirect()->route('register.step4');
    }

    // Step 4: show/post
    public function showRegisterStep4()
    {
        return view('auth.register-step4');
    }

    public function postRegisterStep4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            // document_file optional (uploaded later or via ajax)
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session()->put('register.step4', $request->only(['document_type','document_number']));

        return redirect()->route('register.step5');
    }

    // Step 5: show/post
    public function showRegisterStep5()
    {
        return view('auth.register-step5');
    }

    public function postRegisterStep5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quota_status' => 'required|string',
            'weeks_count' => 'required|integer|min:1|max:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session()->put('register.step5', $request->only(['quota_status','weeks_count']));

        return redirect()->route('register.step6');
    }

    // Step 6: show/post
    public function showRegisterStep6()
    {
        return view('auth.register-step6');
    }

    public function postRegisterStep6(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fraction_type' => 'required|string',
            'agree_terms' => 'accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        session()->put('register.step6', [
            'fraction_type' => $request->fraction_type,
            'agree_terms' => $request->has('agree_terms'),
        ]);

        // All steps stored in session — redirect to main register page to continue with file uploads and finalization
        return redirect()->route('register');
    }

    // Finalize registration: create user, profile and persist data from session + request
    public function finalizeRegistration(Request $request)
    {
        $step1 = session('register.step1', []);
        $step2 = session('register.step2', []);
        $step3 = session('register.step3', []);
        $step4 = session('register.step4', []);
        $step5 = session('register.step5', []);
        $step6 = session('register.step6', []);

        // Temporary debug logging to diagnose "Dados incompletos" issues.
        // Will log session-captured steps and a safe subset of request data (passwords omitted).
        try {
            $safeRequest = $request->except(['password', 'password_confirmation']);
            Log::info('FinalizeRegistration debug - session steps', [
                'step1' => $step1,
                'step2' => $step2,
                'step3' => $step3,
                'step4' => $step4,
                'step5' => $step5,
                'step6' => $step6,
                'session_id' => session()->getId(),
            ]);
            Log::info('FinalizeRegistration debug - request keys and sample', [
                'request_keys' => array_keys($safeRequest),
                'sample' => [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'cpf' => $request->input('cpf'),
                    'quota_status' => $request->input('quota_status'),
                    'hotel_operational' => $request->input('hotel_operational'),
                ],
            ]);
        } catch (\Throwable $logEx) {
            // swallow logging errors to avoid breaking finalize flow
            Log::warning('FinalizeRegistration debug logging failed: ' . $logEx->getMessage());
        }

        // If session steps are empty (user filled form in a single page), try to read from request inputs as fallback
        if (empty($step1)) {
            $step1 = array_filter($request->only(['name', 'email', 'password']));
        }
        if (empty($step2)) {
            $step2 = array_filter($request->only(['full_name', 'cpf', 'phone']));
        }
        if (empty($step3)) {
            // collect address fields using the actual form names
            $step3 = array_filter($request->only([
                'cep', 'street', 'neighborhood', 'city', 'state', 'house_number', 'complement', 'country'
            ]));
            // normalize keys: map house_number -> number for legacy usage if needed
            if (isset($step3['house_number']) && !isset($step3['number'])) {
                $step3['number'] = $step3['house_number'];
            }
            if (isset($step3['cep']) && !isset($step3['postal_code'])) {
                $step3['postal_code'] = $step3['cep'];
            }
        }
        if (empty($step4)) {
            $step4 = array_filter($request->only(['document_type', 'document_number']));
        }
        if (empty($step5)) {
            $step5 = array_filter($request->only(['quota_status', 'weeks_count', 'hotel_name']));
        }
        if (empty($step6)) {
            $step6 = array_filter($request->only(['fraction_type', 'agree_terms']));
        }

        // Basic server validation
        $validator = Validator::make(array_merge($step1, $step2, $step3, $step4, $step5, $step6), [
            'email' => 'required|email',
            'name' => 'required|string',
            'password' => 'required|string|min:8',
            'cpf' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('register')->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            // create user
            // Provisório: cadastro entra já aprovado (fluxo admin de aprovações permanece disponível).
            $user = User::create([
                'name' => $step1['name'],
                'email' => $step1['email'],
                'password' => Hash::make($step1['password']),
                'whatsapp' => $step2['phone'] ?? null,
                'profile_approval_status' => User::PROFILE_APPROVAL_APPROVED,
                'profile_approved_at' => now(),
                'show_approval_success_modal' => false,
            ]);

            // prepare profile payload
            $profilePayload = [
                'user_id' => $user->id,
                'profile_type' => $request->input('profile_type', $step6['fraction_type'] ?? 'curioso'),
                'full_name' => $step2['full_name'] ?? null,
                'cpf' => $step2['cpf'] ?? null,
                'phone' => $step2['phone'] ?? null,
                'cep' => $step3['postal_code'] ?? ($step3['cep'] ?? null),
                'street' => $step3['street'] ?? null,
                'neighborhood' => $step3['neighborhood'] ?? null,
                'city' => $step3['city'] ?? null,
                'state' => $step3['state'] ?? null,
                'house_number' => $step3['number'] ?? null,
                'complement' => $step3['complement'] ?? null,
                'has_quota' => !empty($step5) ? 1 : 0,
                'quota_status' => $step5['quota_status'] ?? null,
                'quota_details' => json_encode(array_filter([
                    'step5' => $step5,
                    'step6' => $step6,
                    'fraction_details' => $request->input('fraction_details_json') ?? null,
                ])),
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
            ];

            // handle uploaded document from the final request (or step4)
            // handle uploaded files from the final request and from step4 fallback
            // 1) user photo
            if ($request->hasFile('user_photo')) {
                $p = $request->file('user_photo')->store('user_photos', 'public');
                $profilePayload['user_photo_path'] = $p;
            }

            // 2) document photo (RG or CNH) - prefer step4.document_type if available
            $docType = $step4['document_type'] ?? $request->input('document_type', null);
            if ($request->hasFile('document_photo')) {
                $p = $request->file('document_photo')->store('documents', 'public');
                if ($docType === 'rg') {
                    $profilePayload['rg_photo_path'] = $p;
                } else {
                    $profilePayload['cnh_photo_path'] = $p;
                }
            }

            // 3) quota contracts (multiple)
            $storedContracts = [];
            if ($request->hasFile('quota_contracts')) {
                foreach ($request->file('quota_contracts') as $file) {
                    if ($file && $file->isValid()) {
                        $storedContracts[] = $file->store('quota_contracts', 'public');
                    }
                }
            }
            if (!empty($storedContracts)) {
                $profilePayload['quota_contract_photo_path'] = $storedContracts[0];
                $profilePayload['quota_contracts'] = json_encode($storedContracts);
            }

            // 4) hospitality authorization term
            if ($request->hasFile('hospitality_authorization_term')) {
                $p = $request->file('hospitality_authorization_term')->store('hospitality_authorizations', 'public');
                $profilePayload['hospitality_authorization_term_path'] = $p;
            } elseif ($request->hasFile('gestor_hospitality_authorization_term')) {
                $p = $request->file('gestor_hospitality_authorization_term')->store('hospitality_authorizations', 'public');
                $profilePayload['gestor_hospitality_authorization_term_path'] = $p;
            }

            $profile = \App\Models\UserProfile::create($profilePayload);

            // If the registrant provided quota information, create a Quota record here as well
            try {
                if ($request->filled('has_quota') && in_array((string) $request->input('has_quota'), ['1', '2', '3'], true)) {
                    $hotelId = $request->input('has_quota') == '1' ? $request->input('owner_hotel_id') : $request->input('gestor_hotel_id');
                    $hotel = Hotel::find($hotelId);

                    if ($hotel) {
                        $quotaRooms = $request->input('has_quota') == '1' ? (int) $request->input('owner_quota_rooms', 1) : (int) $request->input('gestor_quota_rooms', 1);
                        $numberOfGuests = 0;

                        // Sum people from room fields (owner or gestor)
                        if ($request->input('has_quota') == '1') {
                            for ($i = 1; $i <= $quotaRooms; $i++) {
                                $numberOfGuests += (int) ($request->input("owner_room_{$i}_people") ?? 2);
                            }
                        } else {
                            for ($i = 1; $i <= $quotaRooms; $i++) {
                                $numberOfGuests += (int) ($request->input("gestor_room_{$i}_people") ?? 2);
                            }
                        }
                        if ($numberOfGuests == 0) {
                            $numberOfGuests = $quotaRooms * 2;
                        }

                        $weeksCount = $request->input('has_quota') == '1' ? (int) $request->input('owner_quota_weeks_count', 1) : (int) $request->input('gestor_quota_weeks_count', 1);

                        $startDate = now()->addMonth()->startOfMonth();
                        $endDate = $startDate->copy()->addDays($weeksCount * 7 - 1);

                        $fractionDetails = $this->processFractionDetails($request);
                        $fractionTypeFromDetails = $fractionDetails['fraction_type'] ?? null;
                        $fractionWeeksFromDetails = $fractionDetails['fraction_weeks'] ?? [];

                        $seasonalityMap = [
                            'baixa' => 'low',
                            'media' => 'medium',
                            'alta' => 'high',
                            'Altíssima' => 'peak'
                        ];
                        $seasonality = $request->input('has_quota') == '1'
                            ? ($seasonalityMap[$request->input('owner_quota_seasonality')] ?? 'medium')
                            : ($seasonalityMap[$request->input('gestor_quota_seasonality')] ?? 'medium');

                        $allowedUses = $request->input('has_quota') == '1'
                            ? ($request->input('allowed_uses') ?? ['rent', 'exchange', 'sell'])
                            : ($request->input('gestor_allowed_uses') ?? ['rent', 'exchange']);

                        $paymentStatus = $request->input('has_quota') == '1'
                            ? ($request->input('quota_status') ?? 'unpaid')
                            : ($request->input('gestor_quota_status') ?? 'unpaid');

                        $contractPath = $profilePayload['quota_contract_photo_path'] ?? ($profilePayload['quota_contracts'][0] ?? null);
                        if (!$contractPath) {
                            $contractPath = 'default/contract.jpg';
                        }

                        $isFractioned = false;
                        if (!empty($fractionWeeksFromDetails)) {
                            $isFractioned = true;
                        } elseif (!empty($fractionTypeFromDetails) && $fractionTypeFromDetails !== '7' && $fractionTypeFromDetails !== 7) {
                            $isFractioned = true;
                        }

                        $quotaData = [
                            'user_id' => $user->id,
                            'hotel_name' => $hotel->name,
                            'location' => ($hotel->city ?? '') . ', ' . ($hotel->state ?? ''),
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'number_of_guests' => $numberOfGuests,
                            'rental_price' => null,
                            'is_exchange' => false,
                            'observations' => $request->input('has_quota') == '1' ? ($request->input('owner_quota_observations') ?? '') : ($request->input('gestor_quota_observations') ?? ''),
                            'contract_photo_path' => $contractPath,
                            'status' => 'available',
                            'is_fractioned' => (bool) $isFractioned,
                            'fraction_details' => $fractionDetails,
                            'weeks' => $weeksCount,
                            'number_of_rooms' => $quotaRooms,
                            'seasonality' => $seasonality,
                            'payment_status' => $paymentStatus,
                            'is_owner' => $request->input('has_quota') == '1' || $request->input('has_quota') == '3',
                            'is_published' => false,
                            'quota_status' => 'active',
                            'allowed_uses' => $allowedUses,
                        ];

                        $createdQuota = Quota::create($quotaData);
                        Log::info('FinalizeRegistration created quota id: ' . ($createdQuota->id ?? 'null'));
                    } else {
                        Log::warning('FinalizeRegistration: hotel not found for hotel_id=' . $hotelId);
                    }
                }
            } catch (\Throwable $qEx) {
                // Don't break registration if quota creation fails; log and continue
                Log::error('FinalizeRegistration quota creation failed: ' . $qEx->getMessage());
            }

            DB::commit();

            // try send welcome + review status emails (non-blocking)
            try {
                $emailService = new EmailService();
                $quotaInfo = [
                    'is_gestor' => $step5['is_gestor'] ?? false,
                    'is_fractioned' => !empty($step5['weeks_count']) && intval($step5['weeks_count']) > 1,
                    'hotel_name' => $step5['hotel_name'] ?? null,
                    'profile_type' => $profilePayload['profile_type'] ?? null,
                    'allowed_uses' => $step4['allowed_uses'] ?? [],
                ];
                $emailService->sendWelcomeEmail($user, $step1['password'] ?? null, $quotaInfo);
                $emailService->sendAccountApprovedEmail($user);
            } catch (\Throwable $mailEx) {
                Log::warning('Registration emails failed: ' . $mailEx->getMessage());
            }

            // clear registration session
            session()->forget('register');

            return redirect()->route('register.success');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Finalize registration failed: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('register')->withErrors(['registration' => 'Erro ao finalizar cadastro. Tente novamente.']);
        }
    }

    /**
     * AJAX: validate CPF and check uniqueness (used by multi-step client-side flow)
     */
    public function checkCpf(Request $request)
    {
        $cpf = $request->input('cpf');
        if (!$cpf) {
            return response()->json(['valid' => false, 'duplicate' => false, 'message' => 'CPF não informado.'], 422);
        }

        try {
            $cpfService = new CPFValidationService();
            $cpfValidation = $cpfService->validateCPF($cpf);
            if (!$cpfValidation['valid']) {
                return response()->json(['valid' => false, 'duplicate' => false, 'message' => $cpfValidation['message']]);
            }
            $cpfFormatted = $cpfValidation['cpf'];
        } catch (\Throwable $e) {
            // If validation service fails, fallback to basic check
            $cpfFormatted = $cpf;
        }

        $exists = \App\Models\UserProfile::where('cpf', $cpfFormatted)->exists();

        if ($exists) {
            return response()->json(['valid' => true, 'duplicate' => true, 'message' => 'CPF já cadastrado no sistema.']);
        }

        return response()->json(['valid' => true, 'duplicate' => false, 'message' => 'CPF válido e disponível.']);
    }

    /**
     * AJAX: validate Email format and check uniqueness
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(['valid' => false, 'duplicate' => false, 'message' => 'E-mail não informado.'], 422);
        }

        // Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['valid' => false, 'duplicate' => false, 'message' => 'E-mail inválido.']);
        }

        $exists = User::where('email', $email)->exists();
        if ($exists) {
            return response()->json(['valid' => true, 'duplicate' => true, 'message' => 'E-mail já cadastrado no sistema.']);
        }

        return response()->json(['valid' => true, 'duplicate' => false, 'message' => 'E-mail disponível.']);
    }

    public function registerSuccess()
    {
        return view('auth.register-success');
    }

    /**
     * Validação AJAX no cadastro de gestor (has_quota = 2): titular em "Possuo Cota e Autorizarei Outra pessoa geri-la"
     * (has_quota = 3) deve ter informado o CPF do gestor igual ao CPF de quem está se cadastrando.
     */
    public function checkDelegatedGestor(Request $request)
    {
        $resolved = $this->resolveDelegatedGestorAuthorization($request);

        if (! $resolved['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $resolved['message'],
            ]);
        }

        return response()->json([
            'ok' => true,
            'owner_name' => $resolved['owner_name'],
            'owner_user_id' => $resolved['owner_user_id'],
            'document_download_name' => $resolved['download_name'],
        ]);
    }

    /**
     * Download do documento de autorização do titular (cadastro de gestor autorizado).
     */
    public function downloadDelegatedGestorDocument(Request $request)
    {
        $resolved = $this->resolveDelegatedGestorAuthorization($request);

        if (! $resolved['ok']) {
            return response()->json(['message' => $resolved['message']], 422);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($resolved['path'])) {
            return response()->json([
                'message' => 'Arquivo de autorização não encontrado no servidor. Peça ao titular que envie o documento novamente.',
            ], 404);
        }

        return $disk->download($resolved['path'], $resolved['download_name']);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        // Validate CPF
        $cpfService = new CPFValidationService();
        $cpfValidation = $cpfService->validateCPF($request->cpf);
        
        if (!$cpfValidation['valid']) {
            return redirect()->back()
                ->withErrors(['cpf' => $cpfValidation['message']])
                ->withInput();
        }

        $rules = [
            'name' => 'required|string|min:6|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_type' => 'required|in:curioso,inteligente,sabio',
            'full_name' => 'required|string|min:6|max:255',
            'cpf' => 'required|string|size:14',
            'phone' => 'required|string|max:20',
            'cep' => 'required|string|size:9|regex:/^\d{5}-\d{3}$/',
            'street' => 'required|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'house_number' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'complement' => 'required|string|max:120',
            'ingress_date' => 'required|date|before_or_equal:today',
            'whatsapp' => 'nullable|string|max:20',
            'has_quota' => 'required|in:0,1,2,3',
            'quota_status' => 'required_if:has_quota,1|in:paid,unpaid',
            'quota_payment_deadline' => 'nullable|date',
            'user_photo' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'document_photo' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'document_type' => 'required|in:rg,cnh',
            'quota_contract' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'accept_terms' => 'required|accepted',
            'accept_promotional_periods' => 'nullable|boolean',
            'gov_br_signature' => 'nullable|string',
            // Novos campos
            'hotel_operational' => 'nullable|boolean',
            'allowed_uses' => 'required_unless:has_quota,2,3|array',
            'allowed_uses.*' => 'in:rent,exchange,sell,buy',
            // Suporte a múltiplos contratos
            'quota_contracts' => 'required_if:has_quota,1|array',
            'quota_contracts.*' => 'file|mimes:pdf,jpeg,jpg,png|max:10240',
            'hospitality_authorization_term' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            // Detalhes da cota (opcional, mas valida formatos)
            'quota_details' => 'nullable|array',
            'quota_details.hotel_id' => 'nullable|integer|exists:hotels,id',
            'quota_details.hotel_name' => 'nullable|string|max:255',
            'quota_details.number_of_rooms' => 'nullable|integer|min:1',
            'quota_details.size' => 'nullable|string|max:50',
            'quota_details.seasonality' => 'nullable|string|max:100',
            'quota_details.notes' => 'nullable|string|max:1000',
            // Campos do proprietário da cota
            'owner_hotel_id' => 'required_if:has_quota,1|integer|exists:hotels,id',
            'owner_quota_rooms' => 'required_if:has_quota,1|in:1,2,3',
            'owner_quota_balcony' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_size' => 'required_if:has_quota,1|string|max:50',
            'owner_quota_jacuzzi' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_kitchen' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_vista_mar' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_spa' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_piscina' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_academia' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_lareira' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_adega' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_area_kids' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_area_trabalho' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_wifi' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_parking' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_breakfast' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_sofa_mais' => 'required_if:has_quota,1|in:0,1',
            'owner_quota_seasonality' => 'required_if:has_quota,1|in:baixa,media,alta,pico',
            'owner_quota_type' => 'required_if:has_quota,1|in:fixa,flexivel,fix_flexivel',
            'owner_quota_weeks_count' => 'required_if:has_quota,1|integer|min:1|max:6',
            'owner_weeks' => 'nullable|array',
            'owner_quota_observations' => 'nullable|string|max:1000',
            'owner_quota_number' => 'required_if:has_quota,1|string|max:50',
            'owner_quota_block' => 'required_if:has_quota,1|string|max:50',
            'owner_apartment_number' => 'required_if:has_quota,1|string|max:50',
            // Campos do gestor (has_quota 2 ou 3: mesmo formulário)
            'gestor_hotel_operational' => 'required_if:has_quota,2|boolean',
            'gestor_quota_status' => 'required_if:gestor_hotel_operational,1|in:paid,unpaid',
            'gestor_quota_payment_deadline' => 'nullable|date',
            'gestor_authorization_document' => 'nullable|required_if:has_quota,3|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'gestor_quota_contracts' => 'required_if:has_quota,2|array|min:1',
            'gestor_quota_contracts.*' => 'file|mimes:pdf,jpeg,jpg,png|max:10240',
            'owner_delegate_possession_confirmation' => 'required_if:has_quota,3|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'gestor_delegate_cpf' => 'nullable|required_if:has_quota,3|string|max:20',
            'gestor_linked_owner_user_id' => 'nullable|integer|exists:users,id',
            'gestor_quota_number' => 'required_if:has_quota,2|required_if:has_quota,3|string|max:50',
            'gestor_quota_block' => 'required_if:has_quota,2|required_if:has_quota,3|string|max:50',
            'gestor_apartment_number' => 'required_if:has_quota,2|required_if:has_quota,3|string|max:50',
            'gestor_hospitality_authorization_term' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'gestor_hotel_id' => 'required_if:has_quota,2|integer|exists:hotels,id',
            'gestor_quota_rooms' => 'required_if:has_quota,2|in:1,2,3',
            'gestor_quota_balcony' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_size' => 'required_if:has_quota,2|string|max:50',
            'gestor_quota_jacuzzi' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_kitchen' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_vista_mar' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_spa' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_piscina' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_academia' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_lareira' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_adega' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_area_kids' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_area_trabalho' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_wifi' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_parking' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_breakfast' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_sofa_mais' => 'required_if:has_quota,2|in:0,1',
            'gestor_quota_seasonality' => 'required_if:has_quota,2|in:baixa,media,alta,pico',
            'gestor_quota_type' => 'required_if:has_quota,2|in:fixa,flexivel,fix_flexivel',
            'gestor_quota_weeks_count' => 'required_if:has_quota,2|integer|min:1|max:6',
            'gestor_weeks' => 'nullable|array',
            'gestor_quota_observations' => 'nullable|string|max:1000',
            'gestor_allowed_uses' => 'required_if:has_quota,2|required_if:has_quota,3|array|min:1',
            'gestor_allowed_uses.*' => 'in:rent,exchange',
            // Fracionamento da cota
            'fraction_type' => 'nullable|in:3_4,4_3,2_2_3,2_5,3_2_2,7',
        ];

        // Validação dinâmica dos campos de configuração dos quartos
        // Validar quartos do proprietário (apenas se tiver cota e quartos selecionados)
        if ($request->has('has_quota') && $request->has_quota == '1' && $request->has('owner_quota_rooms') && $request->owner_quota_rooms) {
            $numRooms = (int) $request->owner_quota_rooms;
            if ($numRooms > 0) {
                for ($i = 1; $i <= $numRooms; $i++) {
                    $rules["owner_room_{$i}_suite"] = 'required|in:0,1';
                    $rules["owner_room_{$i}_double_bed"] = 'required|in:0,1,2';
                    $rules["owner_room_{$i}_single_bed"] = 'required|in:0,1,2,3,4,5';
                    $rules["owner_room_{$i}_sofa_bed"] = 'required|in:0,1,2';
                    $rules["owner_room_{$i}_bunk_bed"] = 'required|in:0,1,2,3,4,5';
                    $rules["owner_room_{$i}_people"] = 'required|in:1,2,3,4,5,6,7,8,9,10';
                }
            }
        }

        if ($request->has('has_quota') && $request->has_quota == '1') {
            $weeksCount = (int) $request->owner_quota_weeks_count;
            $dayList = '01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31';
            $monthList = '01,02,03,04,05,06,07,08,09,10,11,12';
            
            // Verificar se há fracionamento - se houver, os campos owner_weeks são opcionais
            $hasFractionation = $request->has('fraction_type') && $request->fraction_type && $request->fraction_type !== '7';
            $hasFractionWeeks = $request->has('fraction_weeks') && !empty($request->input('fraction_weeks'));
            
            // Verificar se há fracionamento por semana individual
            $hasFractionTypeWeek = false;
            for ($week = 1; $week <= $weeksCount; $week++) {
                if ($request->has("fraction_type_week_{$week}") && $request->input("fraction_type_week_{$week}") !== '7') {
                    $hasFractionTypeWeek = true;
                    break;
                }
            }

            for ($week = 1; $week <= $weeksCount; $week++) {
                $base = "owner_weeks.{$week}";
                
                // Verificar se esta semana específica tem fracionamento
                $weekHasFraction = $hasFractionTypeWeek && $request->has("fraction_type_week_{$week}") && $request->input("fraction_type_week_{$week}") !== '7';
                $weekHasFractionData = $hasFractionWeeks && isset($request->input('fraction_weeks')[$week]);
                
                // Se há fracionamento (geral ou para esta semana específica), tornar os campos opcionais
                if ($hasFractionation || $hasFractionWeeks || $weekHasFraction || $weekHasFractionData) {
                    $rules["{$base}.authorize"] = 'nullable|in:yes,no';
                    $rules["{$base}.start_day"] = "nullable|in:{$dayList}";
                    $rules["{$base}.end_day"] = "nullable|in:{$dayList}";
                    $rules["{$base}.month"] = "nullable|in:{$monthList}";
                    $rules["{$base}.year"] = "nullable|in:2025,2026";
                    $rules["{$base}.proof"] = "nullable|file|mimes:pdf,jpeg,jpg,png|max:10240";
                } else {
                    // Sem fracionamento, manter validação original
                    $rules["{$base}.authorize"] = 'required|in:yes,no';
                    $rules["{$base}.start_day"] = "required_if:{$base}.authorize,yes|nullable|in:{$dayList}";
                    $rules["{$base}.end_day"] = "required_if:{$base}.authorize,yes|nullable|in:{$dayList}";
                    $rules["{$base}.month"] = "required_if:{$base}.authorize,yes|nullable|in:{$monthList}";
                    $rules["{$base}.year"] = "required_if:{$base}.authorize,yes|nullable|in:2025,2026";
                    $rules["{$base}.proof"] = "required_if:{$base}.authorize,yes|nullable|file|mimes:pdf,jpeg,jpg,png|max:10240";
                }
            }
        }

        // Validar quartos do gestor (apenas se tiver cota, quartos selecionados E os campos existirem no request)
        if ($request->has('has_quota') && (string) $request->has_quota === '2' && $request->has('gestor_quota_rooms') && $request->gestor_quota_rooms) {
            $numRooms = (int) $request->gestor_quota_rooms;
            if ($numRooms > 0) {
                // Só valida se os campos existirem no request (foram criados dinamicamente)
                for ($i = 1; $i <= $numRooms; $i++) {
                    if ($request->has("gestor_room_{$i}_suite")) {
                        $rules["gestor_room_{$i}_suite"] = 'required|in:0,1';
                    }
                    if ($request->has("gestor_room_{$i}_double_bed")) {
                        $rules["gestor_room_{$i}_double_bed"] = 'required|in:0,1,2';
                    }
                    if ($request->has("gestor_room_{$i}_single_bed")) {
                        $rules["gestor_room_{$i}_single_bed"] = 'required|in:0,1,2,3,4,5';
                    }
                    if ($request->has("gestor_room_{$i}_sofa_bed")) {
                        $rules["gestor_room_{$i}_sofa_bed"] = 'required|in:0,1,2';
                    }
                    if ($request->has("gestor_room_{$i}_bunk_bed")) {
                        $rules["gestor_room_{$i}_bunk_bed"] = 'required|in:0,1,2,3,4,5';
                    }
                    if ($request->has("gestor_room_{$i}_people")) {
                        $rules["gestor_room_{$i}_people"] = 'required|in:1,2,3,4,5,6,7,8,9,10';
                    }
                }
            }
        }

        if ($request->has('has_quota') && (string) $request->has_quota === '2') {
            $weeksCount = (int) $request->gestor_quota_weeks_count;
            $dayList = '01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31';
            $monthList = '01,02,03,04,05,06,07,08,09,10,11,12';

            for ($week = 1; $week <= $weeksCount; $week++) {
                $base = "gestor_weeks.{$week}";
                $rules["{$base}.authorize"] = 'required|in:yes,no';
                $rules["{$base}.start_day"] = "required_if:{$base}.authorize,yes|nullable|in:{$dayList}";
                $rules["{$base}.end_day"] = "required_if:{$base}.authorize,yes|nullable|in:{$dayList}";
                $rules["{$base}.month"] = "required_if:{$base}.authorize,yes|nullable|in:{$monthList}";
                $rules["{$base}.year"] = "required_if:{$base}.authorize,yes|nullable|in:2025,2026";
                $rules["{$base}.proof"] = "required_if:{$base}.authorize,yes|nullable|file|mimes:pdf,jpeg,jpg,png|max:10240";
            }
        }

        // Validação dinâmica dos campos de ação dos períodos de fracionamento
        // Verificar se há fracionamento geral ou por semana individual
        $hasFractionation = $request->has('fraction_type') && $request->fraction_type && $request->fraction_type !== '7';
        $fractionWeeks = $request->input('fraction_weeks', []);
        $weeksCount = $request->has('owner_quota_weeks_count') ? (int) $request->owner_quota_weeks_count : 0;
        
        // Verificar fracionamento por semana individual
        $hasFractionTypeWeek = false;
        if ($weeksCount > 0) {
            for ($week = 1; $week <= $weeksCount; $week++) {
                if ($request->has("fraction_type_week_{$week}") && $request->input("fraction_type_week_{$week}") !== '7') {
                    $hasFractionTypeWeek = true;
                    break;
                }
            }
        }
        
        if ($hasFractionation || $hasFractionTypeWeek || !empty($fractionWeeks)) {
            // Se há fracionamento geral, usar fraction_type
            if ($hasFractionation) {
                $parts = explode('_', $request->fraction_type);
                foreach ($fractionWeeks as $weekNumber => $periods) {
                    foreach ($parts as $index => $days) {
                        $periodNumber = $index + 1;
                        $base = "fraction_weeks.{$weekNumber}.periods.{$periodNumber}";

                        $rules["{$base}.start"] = 'required|date';
                        $rules["{$base}.end"] = "required|date|after_or_equal:{$base}.start";
                        $rules["{$base}.enabled"] = 'nullable|boolean';
                        $rules["{$base}.action"] = "required_if:{$base}.enabled,1|nullable|in:rent,exchange,rent_exchange";
                    }
                }
            } else {
                // Se há fracionamento por semana individual, validar cada semana separadamente
                foreach ($fractionWeeks as $weekNumber => $periods) {
                    // Obter o tipo de fracionamento para esta semana
                    $weekFractionType = $request->input("fraction_type_week_{$weekNumber}", null);
                    if ($weekFractionType && $weekFractionType !== '7') {
                        $parts = explode('_', $weekFractionType);
                        foreach ($parts as $index => $days) {
                            $periodNumber = $index + 1;
                            $base = "fraction_weeks.{$weekNumber}.periods.{$periodNumber}";

                            $rules["{$base}.start"] = 'required|date';
                            $rules["{$base}.end"] = "required|date|after_or_equal:{$base}.start";
                            $rules["{$base}.enabled"] = 'nullable|boolean';
                            $rules["{$base}.action"] = "required_if:{$base}.enabled,1|nullable|in:rent,exchange,rent_exchange";
                        }
                    }
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        // Validações customizadas
        $customErrors = [];
        
        // Validação do prazo de quitação da cota
        if ($request->quota_status === 'unpaid' && !empty($request->quota_payment_deadline)) {
            if (strtotime($request->quota_payment_deadline) <= strtotime('today')) {
                $customErrors['quota_payment_deadline'] = 'O prazo para quitação deve ser uma data posterior a hoje.';
            }
        }
        
        // Validação do contrato da cota
        if ($request->has_quota == '1') {
            if (!$request->hasFile('quota_contract') && !$request->hasFile('quota_contracts')) {
                $customErrors['quota_contract'] = 'O contrato da cota é obrigatório quando você possui cota.';
            }
        }

        if ((string) $request->has_quota === '2' && !$request->hasFile('gestor_quota_contracts')) {
            $customErrors['gestor_quota_contracts'] = 'O documento de confirmação da posse da cota é obrigatório para gestores autorizados.';
        }

        // Validação: período da semana deve ser superior ao período atual
        // Se mês/ano da semana for inferior ao mês atual, só permite a partir de janeiro do próximo ano
        $today = Carbon::now()->startOfDay();
        $validateWeeksPeriodRule = function (array $weeksInput, string $prefix) use (&$customErrors, $today) {
            foreach ($weeksInput as $weekNumber => $weekData) {
                if (!is_array($weekData) || (($weekData['authorize'] ?? null) !== 'yes')) {
                    continue;
                }

                $day = isset($weekData['start_day']) ? (int) $weekData['start_day'] : null;
                $month = isset($weekData['month']) ? (int) $weekData['month'] : null;
                $year = isset($weekData['year']) ? (int) $weekData['year'] : null;

                if (!$day || !$month || !$year) {
                    continue;
                }

                if ($year < (int) $today->year || ($year === (int) $today->year && $month < (int) $today->month)) {
                    $customErrors["{$prefix}.{$weekNumber}.month"] = 'O período da Cota a ser cadastrada precisa ser posterior à data vigente.';
                    continue;
                }

                try {
                    $startDate = Carbon::createFromDate($year, $month, $day)->startOfDay();
                    if ($startDate->lte($today)) {
                        $customErrors["{$prefix}.{$weekNumber}.start_day"] = 'O período da Cota a ser cadastrada precisa ser posterior à data vigente.';
                    }
                } catch (\Throwable $e) {
                    // Ignora datas inválidas aqui; a validação padrão dos campos já trata formatos.
                }
            }
        };

        if ((string) $request->has_quota === '1') {
            $validateWeeksPeriodRule((array) $request->input('owner_weeks', []), 'owner_weeks');
        }
        if ((string) $request->has_quota === '2') {
            $validateWeeksPeriodRule((array) $request->input('gestor_weeks', []), 'gestor_weeks');
        }

        if ((string) $request->has_quota === '3') {
            $rawGestor = (string) ($request->gestor_delegate_cpf ?? '');
            $cleanGestor = $cpfService->cleanCPF($rawGestor);
            if ($cleanGestor === '' || ! $cpfService->isValidCPFFormat($cleanGestor)) {
                $customErrors['gestor_delegate_cpf'] = 'Informe o CPF do gestor no formato válido (11 dígitos).';
            } elseif ($cleanGestor === $cpfService->cleanCPF((string) $request->cpf)) {
                $customErrors['gestor_delegate_cpf'] = 'O CPF do gestor deve ser diferente do seu CPF de titular.';
            }
        }

        if ((string) $request->has_quota === '2') {
            $linkedId = (int) ($request->input('gestor_linked_owner_user_id') ?? 0);
            if ($linkedId <= 0) {
                $customErrors['gestor_linked_owner_user_id'] = 'Use o botão Verificar no CPF do proprietário para confirmar que você é o gestor autorizado.';
            } else {
                $ownerProfile = UserProfile::query()
                    ->where('user_id', $linkedId)
                    ->where('has_quota', 3)
                    ->first();
                $gestorClean = $cpfService->cleanCPF($cpfValidation['cpf']);
                if (! $ownerProfile || ! $this->cpfMatchesStored($ownerProfile->gestor_delegate_cpf ?? null, $gestorClean, $cpfService)) {
                    $customErrors['gestor_linked_owner_user_id'] = 'Não foi possível confirmar o vínculo com o titular. Verifique o CPF do proprietário e tente novamente.';
                } elseif (trim((string) ($ownerProfile->gestor_authorization_document_path ?? '')) === '') {
                    $customErrors['gestor_linked_owner_user_id'] = 'O titular ainda não possui documento de autorização cadastrado na plataforma.';
                }
            }
        }

        if ($validator->fails() || !empty($customErrors)) {
            $errors = $validator->errors();
            foreach ($customErrors as $field => $message) {
                $errors->add($field, $message);
            }
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        // Regra adicional: se não possui cota, não pode escolher vender ou trocar
        if ((string)$request->has_quota === '0') {
            $invalidUses = collect($request->allowed_uses ?? [])->intersect(['sell', 'exchange']);
            if ($invalidUses->isNotEmpty()) {
                return redirect()->back()
                    ->withErrors(['allowed_uses' => 'Sem cota, você só pode escolher Alugar e Comprar.'])
                    ->withInput();
            }
        }
        
        // Regra adicional para gestor: não pode escolher vender ou trocar
        if (in_array((string) $request->has_quota, ['2', '3'], true)) {
            $invalidUses = collect($request->gestor_allowed_uses ?? [])->intersect(['sell', 'buy']);
            if ($invalidUses->isNotEmpty()) {
                return redirect()->back()
                    ->withErrors(['gestor_allowed_uses' => 'Como gestor, você só pode escolher Alugar e Trocar.'])
                    ->withInput();
            }
        }

        try {
            // Create user
            // Provisório: cadastro entra já aprovado (fluxo admin de aprovações permanece disponível).
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

            // Upload files
            $fileService = new FileUploadService();
            
            $userPhoto = $fileService->uploadUserPhoto($request->file('user_photo'));
            $documentPhoto = $fileService->uploadDocumentPhoto($request->file('document_photo'), $request->document_type);
            
            $quotaContract = null;
            $quotaContracts = [];
            if ($request->has_quota) {
                if ($request->hasFile('quota_contracts')) {
                    foreach ($request->file('quota_contracts') as $uploadedFile) {
                        $uploaded = $fileService->uploadQuotaContract($uploadedFile);
                        if ($uploaded && isset($uploaded['path'])) {
                            $quotaContracts[] = $uploaded['path'];
                        }
                    }
                    if (!empty($quotaContracts)) {
                        $quotaContract = ['path' => $quotaContracts[0]]; // mantém compatibilidade com campo antigo
                    }
                } elseif ($request->hasFile('quota_contract')) {
                    $quotaContract = $fileService->uploadQuotaContract($request->file('quota_contract'));
                    if ($quotaContract && isset($quotaContract['path'])) {
                        $quotaContracts[] = $quotaContract['path'];
                    }
                } elseif ((string) $request->has_quota === '2' && $request->hasFile('gestor_quota_contracts')) {
                    foreach ($request->file('gestor_quota_contracts') as $uploadedFile) {
                        $uploaded = $fileService->uploadQuotaContract($uploadedFile);
                        if ($uploaded && isset($uploaded['path'])) {
                            $quotaContracts[] = $uploaded['path'];
                        }
                    }
                    if (!empty($quotaContracts)) {
                        $quotaContract = ['path' => $quotaContracts[0]];
                    }
                }
            }
            
            // Processar documento de autorização do gestor
            $gestorAuthorizationDocument = null;
            if (in_array((string) $request->has_quota, ['2', '3'], true) && $request->hasFile('gestor_authorization_document')) {
                $gestorAuthorizationDocument = $fileService->uploadAuthorizationDocument($request->file('gestor_authorization_document'));
            } elseif ((string) $request->has_quota === '2') {
                $linkedOwnerId = (int) $request->input('gestor_linked_owner_user_id');
                $linkedOwnerProfile = $linkedOwnerId > 0
                    ? UserProfile::query()->where('user_id', $linkedOwnerId)->where('has_quota', 3)->first()
                    : null;
                $ownerDocPath = trim((string) ($linkedOwnerProfile->gestor_authorization_document_path ?? ''));
                if ($ownerDocPath !== '') {
                    $gestorAuthorizationDocument = ['path' => $ownerDocPath];
                }
            }

            if ((string) $request->has_quota === '3' && $request->hasFile('owner_delegate_possession_confirmation')) {
                $ownerDelegatePossessionConfirmation = $fileService->uploadQuotaContract($request->file('owner_delegate_possession_confirmation'));
                if ($ownerDelegatePossessionConfirmation && isset($ownerDelegatePossessionConfirmation['path'])) {
                    $quotaContracts[] = $ownerDelegatePossessionConfirmation['path'];
                    if (!$quotaContract || !isset($quotaContract['path'])) {
                        $quotaContract = ['path' => $ownerDelegatePossessionConfirmation['path']];
                    }
                }
            }

            // Processar termo de autorização de hospedagem para terceiros (proprietário)
            $hospitalityAuthorizationTerm = null;
            if ($request->has_quota == '1' && $request->hasFile('hospitality_authorization_term')) {
                $hospitalityAuthorizationTerm = $fileService->uploadHospitalityAuthorizationTerm($request->file('hospitality_authorization_term'));
            }

            // Processar termo de autorização de hospedagem para terceiros (gestor)
            $gestorHospitalityAuthorizationTerm = null;
            if (in_array((string) $request->has_quota, ['2', '3'], true) && $request->hasFile('gestor_hospitality_authorization_term')) {
                $gestorHospitalityAuthorizationTerm = $fileService->uploadHospitalityAuthorizationTerm($request->file('gestor_hospitality_authorization_term'));
            }

            $ownerWeeksData = [];
            if ($request->has_quota == '1') {
                $ownerWeeksInput = $request->input('owner_weeks', []);
                $weeksCount = (int) $request->owner_quota_weeks_count;

                for ($week = 1; $week <= $weeksCount; $week++) {
                    $weekInput = $ownerWeeksInput[$week] ?? [];
                    $authorize = $weekInput['authorize'] ?? 'no';
                    $weekEntry = [
                        'authorize' => $authorize,
                        'start_day' => $weekInput['start_day'] ?? null,
                        'end_day' => $weekInput['end_day'] ?? null,
                        'month' => $weekInput['month'] ?? null,
                        'year' => $weekInput['year'] ?? null,
                        'proof_path' => null,
                    ];

                    if ($authorize === 'yes' && $request->hasFile("owner_weeks.{$week}.proof")) {
                        $uploaded = $fileService->uploadPeriodProof($request->file("owner_weeks.{$week}.proof"));
                        if (!empty($uploaded['valid']) && !$uploaded['valid']) {
                            throw new \RuntimeException($uploaded['message'] ?? 'Falha ao enviar comprovação de período.');
                        }
                        if (!empty($uploaded['path'])) {
                            $weekEntry['proof_path'] = $uploaded['path'];
                        }
                    } elseif ($authorize === 'yes' && $request->filled("owner_weeks.{$week}.proof_uploaded")) {
                        // proof already uploaded via AJAX to temporary storage
                        $weekEntry['proof_path'] = $request->input("owner_weeks.{$week}.proof_uploaded");
                    }

                    $ownerWeeksData[$week] = $weekEntry;
                }
            }

            $gestorWeeksData = [];
            if ((string) $request->has_quota === '2') {
                $gestorWeeksInput = $request->input('gestor_weeks', []);
                $weeksCount = (int) $request->gestor_quota_weeks_count;

                for ($week = 1; $week <= $weeksCount; $week++) {
                    $weekInput = $gestorWeeksInput[$week] ?? [];
                    $authorize = $weekInput['authorize'] ?? 'no';
                    $weekEntry = [
                        'authorize' => $authorize,
                        'start_day' => $weekInput['start_day'] ?? null,
                        'end_day' => $weekInput['end_day'] ?? null,
                        'month' => $weekInput['month'] ?? null,
                        'year' => $weekInput['year'] ?? null,
                        'proof_path' => null,
                    ];

                    if ($authorize === 'yes' && $request->hasFile("gestor_weeks.{$week}.proof")) {
                        $uploaded = $fileService->uploadPeriodProof($request->file("gestor_weeks.{$week}.proof"));
                        if (!empty($uploaded['valid']) && !$uploaded['valid']) {
                            throw new \RuntimeException($uploaded['message'] ?? 'Falha ao enviar comprovação de período.');
                        }
                        if (!empty($uploaded['path'])) {
                            $weekEntry['proof_path'] = $uploaded['path'];
                        }
                    } elseif ($authorize === 'yes' && $request->filled("gestor_weeks.{$week}.proof_uploaded")) {
                        $weekEntry['proof_path'] = $request->input("gestor_weeks.{$week}.proof_uploaded");
                    }

                    $gestorWeeksData[$week] = $weekEntry;
                }
            }

            $quotaDetails = $request->quota_details ?? [];
            
            // Coletar dados dos quartos do proprietário
            if ($request->has_quota == '1') {
                $quotaDetails['owner_weeks_count'] = (int) $request->owner_quota_weeks_count;
                $quotaDetails['owner_weeks'] = $ownerWeeksData;
                
                // Coletar comodidades/detalhes adicionais do proprietário (preserva "0" e "1")
                $ownerQuotaDetailFields = [
                    'owner_quota_balcony',
                    'owner_quota_vista_mar',
                    'owner_quota_spa',
                    'owner_quota_piscina',
                    'owner_quota_academia',
                    'owner_quota_lareira',
                    'owner_quota_adega',
                    'owner_quota_area_kids',
                    'owner_quota_area_trabalho',
                    'owner_quota_wifi',
                ];
                foreach ($ownerQuotaDetailFields as $field) {
                    if ($request->exists($field)) {
                        $quotaDetails[$field] = (int) $request->input($field);
                    }
                }
                
                // Coletar dados dos quartos
                $roomsData = [];
                $quotaRooms = (int) $request->owner_quota_rooms;
                $totalPeople = 0;
                $totalDoubleBed = 0;
                $totalSingleBed = 0;
                $totalSofaBed = 0;
                $totalBunkBed = 0;
                
                for ($i = 1; $i <= $quotaRooms; $i++) {
                    $roomData = [
                        'suite' => (int) ($request->input("owner_room_{$i}_suite") ?? 0),
                        'double_bed' => (int) ($request->input("owner_room_{$i}_double_bed") ?? 0),
                        'single_bed' => (int) ($request->input("owner_room_{$i}_single_bed") ?? 0),
                        'sofa_bed' => (int) ($request->input("owner_room_{$i}_sofa_bed") ?? 0),
                        'bunk_bed' => (int) ($request->input("owner_room_{$i}_bunk_bed") ?? 0),
                        'people' => (int) ($request->input("owner_room_{$i}_people") ?? 2),
                    ];
                    $roomsData[$i] = $roomData;
                    $totalPeople += $roomData['people'];
                    $totalDoubleBed += $roomData['double_bed'];
                    $totalSingleBed += $roomData['single_bed'];
                    $totalSofaBed += $roomData['sofa_bed'];
                    $totalBunkBed += $roomData['bunk_bed'];
                }
                
                $quotaDetails['owner_rooms'] = $roomsData;
                $quotaDetails['owner_rooms_data'] = [
                    'total_people' => $totalPeople,
                    'total_double_bed' => $totalDoubleBed,
                    'total_single_bed' => $totalSingleBed,
                    'total_sofa_bed' => $totalSofaBed,
                    'total_bunk_bed' => $totalBunkBed,
                ];
            }

            // Coletar dados dos quartos do gestor (fluxo completo só no perfil gestor clássico)
            if ((string) $request->has_quota === '2') {
                $quotaDetails['gestor_weeks_count'] = (int) $request->gestor_quota_weeks_count;
                $quotaDetails['gestor_weeks'] = $gestorWeeksData;
                
                // Coletar comodidades/detalhes adicionais do gestor (preserva "0" e "1")
                $gestorQuotaDetailFields = [
                    'gestor_quota_balcony',
                    'gestor_quota_vista_mar',
                    'gestor_quota_spa',
                    'gestor_quota_piscina',
                    'gestor_quota_academia',
                    'gestor_quota_lareira',
                    'gestor_quota_adega',
                    'gestor_quota_area_kids',
                    'gestor_quota_area_trabalho',
                    'gestor_quota_wifi',
                ];
                foreach ($gestorQuotaDetailFields as $field) {
                    if ($request->exists($field)) {
                        $quotaDetails[$field] = (int) $request->input($field);
                    }
                }
                
                // Coletar dados dos quartos
                $roomsData = [];
                $quotaRooms = (int) $request->gestor_quota_rooms;
                $totalPeople = 0;
                $totalDoubleBed = 0;
                $totalSingleBed = 0;
                $totalSofaBed = 0;
                $totalBunkBed = 0;
                
                for ($i = 1; $i <= $quotaRooms; $i++) {
                    if ($request->has("gestor_room_{$i}_suite")) {
                        $roomData = [
                            'suite' => (int) ($request->input("gestor_room_{$i}_suite") ?? 0),
                            'double_bed' => (int) ($request->input("gestor_room_{$i}_double_bed") ?? 0),
                            'single_bed' => (int) ($request->input("gestor_room_{$i}_single_bed") ?? 0),
                            'sofa_bed' => (int) ($request->input("gestor_room_{$i}_sofa_bed") ?? 0),
                            'bunk_bed' => (int) ($request->input("gestor_room_{$i}_bunk_bed") ?? 0),
                            'people' => (int) ($request->input("gestor_room_{$i}_people") ?? 2),
                        ];
                        $roomsData[$i] = $roomData;
                        $totalPeople += $roomData['people'];
                        $totalDoubleBed += $roomData['double_bed'];
                        $totalSingleBed += $roomData['single_bed'];
                        $totalSofaBed += $roomData['sofa_bed'];
                        $totalBunkBed += $roomData['bunk_bed'];
                    }
                }
                
                $quotaDetails['gestor_rooms'] = $roomsData;
                $quotaDetails['gestor_rooms_data'] = [
                    'total_people' => $totalPeople,
                    'total_double_bed' => $totalDoubleBed,
                    'total_single_bed' => $totalSingleBed,
                    'total_sofa_bed' => $totalSofaBed,
                    'total_bunk_bed' => $totalBunkBed,
                ];
            }

            if ($request->filled('fraction_type')) {
                $quotaDetails['fraction_type'] = $request->fraction_type;
                $quotaDetails['fraction_weeks'] = $request->input('fraction_weeks', []);
            }

            // Create user profile
            $profile = UserProfile::create([
                'user_id' => $user->id,
                'profile_type' => $request->profile_type,
                'full_name' => $request->full_name,
                'cpf' => $cpfValidation['cpf'],
                'phone' => $request->phone,
                'cep' => $request->cep,
                'street' => $request->street,
                'neighborhood' => $request->neighborhood,
                'city' => $request->city,
                'state' => $request->state,
                'house_number' => $request->house_number,
                'complement' => $request->complement,
                'user_photo_path' => $userPhoto['path'],
                'rg_photo_path' => $request->document_type === 'rg' ? $documentPhoto['path'] : null,
                'cnh_photo_path' => $request->document_type === 'cnh' ? $documentPhoto['path'] : null,
                'quota_contract_photo_path' => $quotaContract ? $quotaContract['path'] : null,
                'has_quota' => $request->has_quota,
                'quota_status' => $request->quota_status,
                'quota_payment_deadline' => $request->quota_payment_deadline,
                'gov_br_signature' => $request->gov_br_signature,
                'gov_br_signature_at' => now(),
                'kyc_completed' => true,
                'kyc_completed_at' => now(),
                'kyc_status' => 'approved',
                'hotel_operational' => $this->parseBooleanValue($request->hotel_operational),
                'allowed_uses' => $request->allowed_uses,
                'quota_contracts' => !empty($quotaContracts) ? $quotaContracts : null,
                'quota_contract_photo_path' => $quotaContract ? $quotaContract['path'] : null,
                'quota_details' => $quotaDetails,
                'terms_accepted' => (bool) $request->accept_terms,
                'terms_accepted_at' => $request->accept_terms ? now() : null,
                'is_quota_owner' => $request->has_quota == '1' || $request->has_quota == '3',
                'is_authorized_user' => (string) $request->has_quota === '2',
                // Campos do proprietário da cota
                'owner_hotel_id' => $request->has_quota == '1' ? $request->owner_hotel_id : null,
                'owner_quota_rooms' => $request->has_quota == '1' ? $request->owner_quota_rooms : null,
                'owner_quota_people' => $request->has_quota == '1' && isset($quotaDetails['owner_rooms_data']) ? (string) $quotaDetails['owner_rooms_data']['total_people'] : null,
                'owner_quota_double_bed' => $request->has_quota == '1' && isset($quotaDetails['owner_rooms_data']) ? (string) $quotaDetails['owner_rooms_data']['total_double_bed'] : null,
                'owner_quota_single_bed' => $request->has_quota == '1' && isset($quotaDetails['owner_rooms_data']) ? (string) $quotaDetails['owner_rooms_data']['total_single_bed'] : null,
                'owner_quota_sofa_bed' => $request->has_quota == '1' && isset($quotaDetails['owner_rooms_data']) ? (string) $quotaDetails['owner_rooms_data']['total_sofa_bed'] : null,
                'owner_quota_size' => $request->has_quota == '1' ? $request->owner_quota_size : null,
                'owner_quota_jacuzzi' => $request->has_quota == '1' ? (bool) $request->owner_quota_jacuzzi : null,
                'owner_quota_kitchen' => $request->has_quota == '1' ? (bool) $request->owner_quota_kitchen : null,
                'owner_quota_parking' => $request->has_quota == '1' ? (bool) $request->owner_quota_parking : null,
                'owner_quota_breakfast' => $request->has_quota == '1' ? (bool) $request->owner_quota_breakfast : null,
                'owner_quota_sofa_mais' => $request->has_quota == '1' ? (bool) $request->owner_quota_sofa_mais : null,
                'owner_quota_seasonality' => $request->has_quota == '1' ? $request->owner_quota_seasonality : null,
                'owner_quota_type' => $request->has_quota == '1' ? $request->owner_quota_type : null,
                'owner_quota_observations' => $request->has_quota == '1' ? $request->owner_quota_observations : null,
                'owner_quota_number' => $request->has_quota == '1' ? $request->owner_quota_number : null,
                'owner_quota_block' => $request->has_quota == '1' ? $request->owner_quota_block : null,
                'owner_apartment_number' => $request->has_quota == '1' ? $request->owner_apartment_number : null,
                'hospitality_authorization_term_path' => $hospitalityAuthorizationTerm && isset($hospitalityAuthorizationTerm['path']) ? $hospitalityAuthorizationTerm['path'] : null,
                // Campos do gestor
                'gestor_hotel_operational' => (string) $request->has_quota === '2' ? $this->parseBooleanValue($request->gestor_hotel_operational) : null,
                'gestor_quota_status' => (string) $request->has_quota === '2' ? $request->gestor_quota_status : null,
                'gestor_quota_payment_deadline' => (string) $request->has_quota === '2' ? $request->gestor_quota_payment_deadline : null,
                'gestor_authorization_document_path' => $gestorAuthorizationDocument ? $gestorAuthorizationDocument['path'] : null,
                'gestor_delegate_cpf' => (string) $request->has_quota === '3'
                    ? $cpfService->formatCPF($cpfService->cleanCPF((string) $request->gestor_delegate_cpf))
                    : null,
                'gestor_linked_owner_user_id' => (string) $request->has_quota === '2'
                    ? (int) $request->input('gestor_linked_owner_user_id')
                    : null,
                'gestor_quota_number' => in_array((string) $request->has_quota, ['2', '3'], true) ? $request->gestor_quota_number : null,
                'gestor_quota_block' => in_array((string) $request->has_quota, ['2', '3'], true) ? $request->gestor_quota_block : null,
                'gestor_apartment_number' => in_array((string) $request->has_quota, ['2', '3'], true) ? $request->gestor_apartment_number : null,
                'gestor_hotel_id' => (string) $request->has_quota === '2' ? $request->gestor_hotel_id : null,
                'gestor_quota_people' => (string) $request->has_quota === '2' && isset($quotaDetails['gestor_rooms_data']) ? (string) $quotaDetails['gestor_rooms_data']['total_people'] : null,
                'gestor_quota_double_bed' => (string) $request->has_quota === '2' && isset($quotaDetails['gestor_rooms_data']) ? (string) $quotaDetails['gestor_rooms_data']['total_double_bed'] : null,
                'gestor_quota_single_bed' => (string) $request->has_quota === '2' && isset($quotaDetails['gestor_rooms_data']) ? (string) $quotaDetails['gestor_rooms_data']['total_single_bed'] : null,
                'gestor_quota_sofa_bed' => (string) $request->has_quota === '2' && isset($quotaDetails['gestor_rooms_data']) ? (string) $quotaDetails['gestor_rooms_data']['total_sofa_bed'] : null,
                'gestor_quota_rooms' => (string) $request->has_quota === '2' ? $request->gestor_quota_rooms : null,
                'gestor_quota_size' => (string) $request->has_quota === '2' ? $request->gestor_quota_size : null,
                'gestor_quota_jacuzzi' => (string) $request->has_quota === '2' ? (bool) $request->gestor_quota_jacuzzi : null,
                'gestor_quota_kitchen' => (string) $request->has_quota === '2' ? (bool) $request->gestor_quota_kitchen : null,
                'gestor_quota_parking' => (string) $request->has_quota === '2' ? (bool) $request->gestor_quota_parking : null,
                'gestor_quota_breakfast' => (string) $request->has_quota === '2' ? (bool) $request->gestor_quota_breakfast : null,
                'gestor_quota_sofa_mais' => (string) $request->has_quota === '2' ? (bool) $request->gestor_quota_sofa_mais : null,
                'gestor_quota_seasonality' => (string) $request->has_quota === '2' ? $request->gestor_quota_seasonality : null,
                'gestor_quota_type' => (string) $request->has_quota === '2' ? $request->gestor_quota_type : null,
                'gestor_quota_observations' => (string) $request->has_quota === '2' ? $request->gestor_quota_observations : null,
                'gestor_hospitality_authorization_term_path' => $gestorHospitalityAuthorizationTerm && isset($gestorHospitalityAuthorizationTerm['path']) ? $gestorHospitalityAuthorizationTerm['path'] : null,
                'gestor_allowed_uses' => in_array((string) $request->has_quota, ['2', '3'], true) ? $request->gestor_allowed_uses : null,
            ]);

            // Create KYC validation record
            KYCValidation::create([
                'user_id' => $user->id,
                'document_type' => $request->document_type,
                'document_photo_path' => $documentPhoto['path'],
                'validation_status' => 'pending',
            ]);

            // Criar cota na tabela quotas se o usuário possui cota
            $quota = null;
            if ($request->has_quota == '1' || in_array((string) $request->has_quota, ['2', '3'], true)) {
                $hotelId = $request->has_quota == '1' ? $request->owner_hotel_id : $request->gestor_hotel_id;
                $hotel = Hotel::find($hotelId);
                
                if ($hotel) {
                    // Calcular número de hóspedes baseado nos quartos
                    $quotaRooms = $request->has_quota == '1' ? $request->owner_quota_rooms : $request->gestor_quota_rooms;
                    $numberOfGuests = 0;
                    
                    // Calcular hóspedes baseado na configuração dos quartos
                    if ($request->has_quota == '1') {
                        for ($i = 1; $i <= $quotaRooms; $i++) {
                            $people = (int) ($request->input("owner_room_{$i}_people") ?? 2);
                            $numberOfGuests += $people;
                        }
                    } else {
                        for ($i = 1; $i <= $quotaRooms; $i++) {
                            $people = (int) ($request->input("gestor_room_{$i}_people") ?? 2);
                            $numberOfGuests += $people;
                        }
                    }
                    
                    // Se não conseguiu calcular, usar valor padrão
                    if ($numberOfGuests == 0) {
                        $numberOfGuests = $quotaRooms * 2; // 2 pessoas por quarto como padrão
                    }
                    
                    // Calcular semanas
                    $weeksCount = $request->has_quota == '1' 
                        ? (int) $request->owner_quota_weeks_count 
                        : (int) $request->gestor_quota_weeks_count;
                    
                    // Definir datas padrão (pode ser ajustado depois)
                    $startDate = now()->addMonth()->startOfMonth();
                    $endDate = $startDate->copy()->addDays($weeksCount * 7 - 1);
                    
                    // Tentar pegar a primeira semana autorizada para definir as datas
                    if ($request->has_quota == '1' && !empty($ownerWeeksData)) {
                        foreach ($ownerWeeksData as $weekData) {
                            if ($weekData['authorize'] === 'yes' && !empty($weekData['year']) && !empty($weekData['month'])) {
                                $year = (int) $weekData['year'];
                                $month = (int) $weekData['month'];
                                $startDay = !empty($weekData['start_day']) ? (int) $weekData['start_day'] : 1;
                                $endDay = !empty($weekData['end_day']) ? (int) $weekData['end_day'] : 28;
                                
                                $startDate = Carbon::create($year, $month, $startDay);
                                $endDate = Carbon::create($year, $month, $endDay);
                                break;
                            }
                        }
                    } elseif (in_array((string) $request->has_quota, ['2', '3'], true) && !empty($gestorWeeksData)) {
                        foreach ($gestorWeeksData as $weekData) {
                            if ($weekData['authorize'] === 'yes' && !empty($weekData['year']) && !empty($weekData['month'])) {
                                $year = (int) $weekData['year'];
                                $month = (int) $weekData['month'];
                                $startDay = !empty($weekData['start_day']) ? (int) $weekData['start_day'] : 1;
                                $endDay = !empty($weekData['end_day']) ? (int) $weekData['end_day'] : 28;
                                
                                $startDate = Carbon::create($year, $month, $startDay);
                                $endDate = Carbon::create($year, $month, $endDay);
                                break;
                            }
                        }
                    }
                    
                    // Mapear sazonalidade
                    $seasonalityMap = [
                        'baixa' => 'low',
                        'media' => 'medium',
                        'alta' => 'high',
                        'pico' => 'peak'
                    ];
                    $seasonality = $request->has_quota == '1' 
                        ? ($seasonalityMap[$request->owner_quota_seasonality] ?? 'medium')
                        : ($seasonalityMap[$request->gestor_quota_seasonality] ?? 'medium');
                    
                    // Determinar allowed_uses
                    $allowedUses = $request->has_quota == '1' 
                        ? ($request->allowed_uses ?? ['rent', 'exchange', 'sell'])
                        : ($request->gestor_allowed_uses ?? ['rent', 'exchange']);
                    
                    // Determinar payment_status
                    $paymentStatus = $request->has_quota == '1'
                        ? ($request->quota_status ?? 'unpaid')
                        : ($request->gestor_quota_status ?? 'unpaid');
                    
                    // Usar o contrato da cota como contract_photo_path
                    $contractPath = null;
                    if ($quotaContract && isset($quotaContract['path'])) {
                        $contractPath = $quotaContract['path'];
                    } elseif (!empty($quotaContracts) && isset($quotaContracts[0])) {
                        $contractPath = $quotaContracts[0];
                    } elseif (in_array((string) $request->has_quota, ['2', '3'], true) && $gestorAuthorizationDocument && isset($gestorAuthorizationDocument['path'])) {
                        // Para gestor, usar documento de autorização se não tiver contrato
                        $contractPath = $gestorAuthorizationDocument['path'];
                    }
                    
                    // Se ainda não tiver contrato, usar um valor padrão (mas isso não deveria acontecer)
                    if (!$contractPath) {
                        $contractPath = 'default/contract.jpg';
                    }
                    
                    // Determinar se a cota é fracionada
                    // Sempre processar os detalhes de fracionamento para termos visibilidade completa do que veio no request
                    $fractionDetails = $this->processFractionDetails($request);
                    $fractionTypeFromDetails = $fractionDetails['fraction_type'] ?? null;
                    $fractionWeeksFromDetails = $fractionDetails['fraction_weeks'] ?? [];

                    Log::info('=== Checking Fraction Data (normalized) ===', [
                        'fraction_type' => $fractionTypeFromDetails,
                        'has_fraction_weeks' => (!empty($fractionWeeksFromDetails) ? 'true' : 'false'),
                    ]);

                    // Regra:
                    // - Se existir ao menos um período em fraction_weeks, considera fracionada
                    // - Caso contrário, se fraction_type estiver definido e for diferente de 7, também considera fracionada
                    $isFractioned = false;
                    if (!empty($fractionWeeksFromDetails)) {
                        $isFractioned = true;
                        Log::info('=== Creating Fractioned Quota (via processed fraction_weeks) ===');
                    } elseif (!empty($fractionTypeFromDetails) && $fractionTypeFromDetails !== '7' && $fractionTypeFromDetails !== 7) {
                        $isFractioned = true;
                        Log::info('=== Creating Fractioned Quota (via processed fraction_type) ===');
                    } else {
                        Log::info('=== Creating Non-Fractioned Quota ===');
                        Log::info('fraction_type (normalized): ' . var_export($fractionTypeFromDetails, true));
                        Log::info('is_fractioned will be: false (boolean)');
                    }
                    
                    // Criar a cota
                    $quotaData = [
                        'user_id' => $user->id,
                        'hotel_name' => $hotel->name,
                        'location' => ($hotel->city ?? '') . ', ' . ($hotel->state ?? ''),
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'number_of_guests' => $numberOfGuests,
                        'rental_price' => null, // Pode ser definido depois
                        'is_exchange' => false,
                        'observations' => $request->has_quota == '1' 
                            ? ($request->owner_quota_observations ?? '')
                            : ($request->gestor_quota_observations ?? ''),
                        'contract_photo_path' => $contractPath,
                        'status' => 'available',
                        'is_fractioned' => (bool) $isFractioned, // Garantir que seja boolean
                        'fraction_details' => $fractionDetails,
                        'weeks' => $weeksCount,
                        'number_of_rooms' => $quotaRooms,
                        'seasonality' => $seasonality,
                        'payment_status' => $paymentStatus,
                        'is_owner' => $request->has_quota == '1' || $request->has_quota == '3',
                        'is_published' => false,
                        'quota_status' => 'active',
                        'allowed_uses' => $allowedUses,
                    ];
                    
                    Log::info('=== Quota Data Before Create ===');
                    Log::info('is_fractioned value: ' . var_export($quotaData['is_fractioned'], true));
                    Log::info('is_fractioned type: ' . gettype($quotaData['is_fractioned']));
                    
                    $quota = Quota::create($quotaData);
                    
                    Log::info('=== Quota Created ===');
                    Log::info('Quota ID: ' . $quota->id);
                    Log::info('is_fractioned after create: ' . var_export($quota->is_fractioned, true));
                    Log::info('is_fractioned type after create: ' . gettype($quota->is_fractioned));
                }
            }

            Auth::login($user);

            // Enviar email de boas-vindas APÓS a resposta ser enviada ao usuário (não bloqueia o cadastro)
            $userForEmail = $user;
            $passwordForEmail = $request->password;
            $quotaInfoForEmail = isset($quota) ? [
                'is_gestor' => (string) $request->has_quota === '2',
                'is_fractioned' => (bool) ($quota->is_fractioned ?? false),
                'hotel_name' => $quota->hotel_name ?? null,
                'profile_type' => $request->profile_type ?? null,
                'allowed_uses' => $quota->allowed_uses ?? [],
            ] : null;

            app()->terminating(function () use ($userForEmail, $passwordForEmail, $quotaInfoForEmail) {
                try {
                    $emailService = new \App\Services\EmailService();
                    $emailService->sendWelcomeEmail($userForEmail, $passwordForEmail, $quotaInfoForEmail);
                    $emailService->sendAccountApprovedEmail($userForEmail);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar emails pós-cadastro: ' . $e->getMessage());
                }
            });

            // Redirecionar para página de sucesso (resposta enviada imediatamente; email roda depois)
            return redirect()->route('registration.success')
                ->with('registration_data', [
                    'has_quota' => $request->has_quota,
                    'profile_type' => $request->profile_type,
                ]);

        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar conta: ' . $e->getMessage() . '. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Show registration success page.
     */
    public function showRegistrationSuccess()
    {
        // Verificar se o usuário está autenticado (cadastro bem-sucedido)
        if (!Auth::check()) {
            return redirect()->route('register')
                ->with('error', 'Acesso negado. Por favor, realize o cadastro primeiro.');
        }

        return view('auth.registration-success');
    }

    /**
     * Show the login form.
     * Regenera a sessão para garantir token CSRF válido e evitar 419 ao enviar o formulário.
     */
    public function showLogin(Request $request)
    {
        $request->session()->regenerateToken();
        return view('auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            // Administradores podem acessar mesmo sem perfil (painel admin). Demais usuários precisam de perfil.
            $isAdminOrModerator = $user instanceof User && ($user->is_admin || $user->role === 'admin' || $user->role === 'moderator');
            if (!$user->profile && !$isAdminOrModerator) {
                Auth::logout();
                return redirect()->back()
                    ->withErrors(['email' => 'Usuário não encontrado ou cadastro incompleto. Por favor, entre em contato com o suporte.'])
                    ->withInput();
            }
            
            // Administrador e moderador vão direto para o painel administrativo
            if ($isAdminOrModerator) {
                return redirect()->intended(route('admin.dashboard'));
            }
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->back()
            ->withErrors(['email' => 'Credenciais inválidas. Verifique seu e-mail e senha.'])
            ->withInput();
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Process fraction details from request
     */
    private function processFractionDetails(Request $request)
    {
        // Se vier o JSON consolidado do front, usar ele com prioridade
        if ($request->filled('fraction_details_json')) {
            $json = $request->input('fraction_details_json');
            try {
                $decoded = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    Log::info('=== Processing Fraction Details (from JSON) ===');
                    Log::info('Fraction Details JSON: ' . $json);
                    return [
                        'fraction_type' => $decoded['fraction_type'] ?? null,
                        'fraction_weeks' => $decoded['fraction_weeks'] ?? [],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to decode fraction_details_json: ' . $e->getMessage());
            }
        }

        $fractionType = $request->fraction_type;
        $fractionWeeks = $request->input('fraction_weeks', []);
        
        // Log para debug (remover em produção se necessário)
        Log::info('=== Processing Fraction Details ===');
        Log::info('Fraction Type: ' . $fractionType);
        Log::info('Request All: ' . json_encode($request->all(), JSON_PRETTY_PRINT));
        Log::info('Fraction Weeks Raw from input(): ' . json_encode($fractionWeeks));
        
        // Se fraction_weeks está vazio ou não é um array válido, tentar buscar de outra forma
        if (empty($fractionWeeks) || !is_array($fractionWeeks)) {
            $fractionWeeks = [];
            
            // Verificar se há campos fraction_weeks no request (Laravel processa arrays aninhados automaticamente)
            $allInputs = $request->all();
            
            // Procurar por fraction_weeks no formato array
            if (isset($allInputs['fraction_weeks']) && is_array($allInputs['fraction_weeks'])) {
                $fractionWeeks = $allInputs['fraction_weeks'];
            } else {
                // Tentar reconstruir a partir de campos individuais
                foreach ($allInputs as $key => $value) {
                    // Procurar por padrão fraction_weeks[week][periods][period][field]
                    if (preg_match('/^fraction_weeks\[(\d+)\]\[periods\]\[(\d+)\]\[(.+)\]$/', $key, $matches)) {
                        $weekNumber = $matches[1];
                        $periodNumber = $matches[2];
                        $field = $matches[3];
                        
                        if (!isset($fractionWeeks[$weekNumber])) {
                            $fractionWeeks[$weekNumber] = ['periods' => []];
                        }
                        if (!isset($fractionWeeks[$weekNumber]['periods'][$periodNumber])) {
                            $fractionWeeks[$weekNumber]['periods'][$periodNumber] = [];
                        }
                        $fractionWeeks[$weekNumber]['periods'][$periodNumber][$field] = $value;
                    }
                }
            }
        }
        
        // Garantir que a estrutura está correta
        foreach ($fractionWeeks as $weekNumber => $weekData) {
            if (!isset($weekData['periods']) || !is_array($weekData['periods'])) {
                if (isset($weekData[0]) && is_array($weekData[0])) {
                    // Se periods está diretamente no array
                    $fractionWeeks[$weekNumber] = ['periods' => $weekData];
                } else {
                    $fractionWeeks[$weekNumber] = ['periods' => []];
                }
            }
        }
        
        Log::info('Fraction Weeks Processed: ' . json_encode($fractionWeeks));
        
        return [
            'fraction_type' => $fractionType,
            'fraction_weeks' => $fractionWeeks
        ];
    }

    /**
     * Parse boolean value from request
     */
    private function parseBooleanValue($value)
    {
        if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on') {
            return true;
        }
        if ($value === false || $value === 0 || $value === '0' || $value === 'false' || $value === 'off') {
            return false;
        }
        // Se não conseguir determinar, retornar false por padrão
        return false;
    }

    /**
     * Valida vínculo gestor/titular e localiza o documento de autorização.
     *
     * @return array{ok: bool, message?: string, owner_name?: string, owner_user_id?: int, path?: string, download_name?: string}
     */
    private function resolveDelegatedGestorAuthorization(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'owner_cpf' => 'required|string',
            'gestor_cpf' => 'required|string',
        ]);

        $unauthorizedMessage = 'Desculpe, mas você não é um gestor autorizado para gerir uma cota de terceiro no Cota Brasilis.';

        if ($validator->fails()) {
            return ['ok' => false, 'message' => $unauthorizedMessage];
        }

        $cpfService = new CPFValidationService();
        $ownerClean = $cpfService->cleanCPF((string) $request->input('owner_cpf'));
        $gestorClean = $cpfService->cleanCPF((string) $request->input('gestor_cpf'));

        if (strlen($ownerClean) !== 11 || strlen($gestorClean) !== 11
            || ! $cpfService->isValidCPFFormat($ownerClean)
            || ! $cpfService->isValidCPFFormat($gestorClean)) {
            return ['ok' => false, 'message' => $unauthorizedMessage];
        }

        $ownerProfile = $this->findOwnerDelegateProfileByCpf($ownerClean, $cpfService);

        if (! $ownerProfile) {
            return ['ok' => false, 'message' => $unauthorizedMessage];
        }

        if (! $this->cpfMatchesStored($ownerProfile->gestor_delegate_cpf ?? null, $gestorClean, $cpfService)) {
            return ['ok' => false, 'message' => $unauthorizedMessage];
        }

        $docPath = trim((string) ($ownerProfile->gestor_authorization_document_path ?? ''));
        if ($docPath === '') {
            return [
                'ok' => false,
                'message' => 'O titular ainda não enviou o documento de autorização no cadastro. Peça que ele anexe o arquivo na opção de autorizar gestor e salve o cadastro.',
            ];
        }

        $extension = pathinfo($docPath, PATHINFO_EXTENSION) ?: 'pdf';
        $downloadName = 'autorizacao-gestao-cota-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $ownerProfile->full_name)) . '.' . $extension;

        return [
            'ok' => true,
            'owner_name' => $ownerProfile->full_name,
            'owner_user_id' => (int) $ownerProfile->user_id,
            'path' => $docPath,
            'download_name' => $downloadName,
        ];
    }

    /**
     * Localiza titular cadastrado em "Possuo Cota e Autorizarei Outra pessoa geri-la" (has_quota = 3).
     */
    private function findOwnerDelegateProfileByCpf(string $ownerCpfClean, CPFValidationService $cpfService): ?UserProfile
    {
        $ownerFormatted = $cpfService->formatCPF($ownerCpfClean);

        return UserProfile::query()
            ->where('has_quota', 3)
            ->where(function ($query) use ($ownerFormatted, $ownerCpfClean) {
                $query->where('cpf', $ownerFormatted)
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?", [$ownerCpfClean]);
            })
            ->first();
    }

    /**
     * Compara CPF armazenado (formatado ou não) com dígitos informados.
     */
    private function cpfMatchesStored(?string $storedCpf, string $compareClean, CPFValidationService $cpfService): bool
    {
        if ($storedCpf === null || trim($storedCpf) === '') {
            return false;
        }

        return $cpfService->cleanCPF($storedCpf) === $compareClean;
    }
}
