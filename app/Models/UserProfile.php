<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_type',
        'full_name',
        'cpf',
        'phone',
        'cep',
        'street',
        'neighborhood',
        'city',
        'state',
        'house_number',
        'complement',
        'cnh_photo_path',
        'rg_photo_path',
        'user_photo_path',
        'quota_contract_photo_path',
        'quota_contracts',
        'quota_paid_off',
        'hotel_operational',
        'terms_accepted',
        'terms_accepted_at',
        'digital_signature',
        'auctions_used',
        'search_views_used',
        'last_search_view',
        'alert_cities',
        'has_quota',
        'quota_status',
        'quota_payment_deadline',
        'is_quota_owner',
        'is_authorized_user',
        'authorization_document_path',
        'gov_br_signature',
        'gov_br_signature_at',
        'kyc_completed',
        'kyc_completed_at',
        'kyc_status',
        'kyc_rejection_reason',
        'allowed_uses',
        // Campos do proprietário da cota
        'owner_hotel_id',
        'owner_quota_rooms',
        'owner_quota_people',
        'owner_quota_double_bed',
        'owner_quota_single_bed',
        'owner_quota_sofa_bed',
        'owner_quota_size',
        'owner_quota_jacuzzi',
        'owner_quota_kitchen',
        'owner_quota_parking',
        'owner_quota_breakfast',
        'owner_quota_sofa_mais',
        'owner_quota_seasonality',
        'owner_quota_observations',
        'owner_quota_number',
        'owner_quota_block',
        'owner_apartment_number',
        'owner_quota_type',
        'hospitality_authorization_term_path',
        // Campos do gestor
        'gestor_hotel_operational',
        'gestor_quota_status',
        'gestor_quota_payment_deadline',
        'gestor_authorization_document_path',
        'gestor_delegate_cpf',
        'gestor_linked_owner_user_id',
        'gestor_quota_number',
        'gestor_quota_block',
        'gestor_apartment_number',
        'gestor_hotel_id',
        'gestor_quota_people',
        'gestor_quota_double_bed',
        'gestor_quota_single_bed',
        'gestor_quota_sofa_bed',
        'gestor_quota_rooms',
        'gestor_quota_size',
        'gestor_quota_jacuzzi',
        'gestor_quota_kitchen',
        'gestor_quota_parking',
        'gestor_quota_breakfast',
        'gestor_quota_sofa_mais',
        'gestor_quota_seasonality',
        'gestor_quota_observations',
        'gestor_quota_type',
        'gestor_hospitality_authorization_term_path',
        'gestor_allowed_uses',
        'quota_details',
    ];

    protected function casts(): array
    {
        return [
            'quota_paid_off' => 'boolean',
            'hotel_operational' => 'boolean',
            'terms_accepted' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'digital_signature' => 'array',
            'alert_cities' => 'array',
            'has_quota' => 'integer',
            'quota_status' => 'string',
            'quota_payment_deadline' => 'date',
            'is_quota_owner' => 'boolean',
            'last_search_view' => 'datetime',
            'is_authorized_user' => 'boolean',
            'gov_br_signature' => 'array',
            'gov_br_signature_at' => 'datetime',
            'kyc_completed' => 'boolean',
            'kyc_completed_at' => 'datetime',
            'quota_contracts' => 'array',
            'allowed_uses' => 'array',
            'quota_details' => 'array',
            // Campos do proprietário da cota
            'owner_quota_jacuzzi' => 'boolean',
            'owner_quota_kitchen' => 'boolean',
            'owner_quota_parking' => 'boolean',
            'owner_quota_breakfast' => 'boolean',
            'owner_quota_sofa_mais' => 'boolean',
            // Campos do gestor
            'gestor_hotel_operational' => 'boolean',
            'gestor_quota_status' => 'string',
            'gestor_quota_payment_deadline' => 'date',
            'gestor_quota_jacuzzi' => 'boolean',
            'gestor_quota_kitchen' => 'boolean',
            'gestor_quota_parking' => 'boolean',
            'gestor_quota_breakfast' => 'boolean',
            'gestor_quota_sofa_mais' => 'boolean',
            'gestor_allowed_uses' => 'array',
        ];
    }

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Caminho relativo da foto no disco public (null se vazio, PDF ou inexistente).
     */
    public function userPhotoStoragePath(): ?string
    {
        $raw = $this->user_photo_path;
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim((string) $raw));
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) {
            $path = (string) Str::after($path, 'storage/');
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf'], true)) {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * URL pública para arquivos no disco public.
     */
    public static function publicStorageUrl(?string $raw): ?string
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim((string) $raw));
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) {
            $path = (string) Str::after($path, 'storage/');
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return route('storage.public', ['path' => $path]);
    }

    /**
     * URL da foto do cotista para &lt;img&gt; (null se não houver imagem válida).
     */
    public function userPhotoImageUrl(): ?string
    {
        $path = $this->userPhotoStoragePath();
        if ($path === null) {
            return null;
        }

        $version = $this->updated_at?->timestamp ?? 0;

        return route('storage.public', ['path' => $path]) . '?v=' . $version;
    }

    /**
     * URL para &lt;img&gt;: foto real ou placeholder (nunca retorna URL quebrada).
     */
    public function userPhotoDisplayUrl(): string
    {
        return $this->userPhotoImageUrl() ?? asset('images/placeholders/user-avatar.svg');
    }

    /**
     * Get the KYC validations for this profile.
     */
    public function kycValidations()
    {
        return $this->hasMany(KYCValidation::class, 'user_id', 'user_id');
    }

    /**
     * Check if all required KYC documents are uploaded.
     */
    public function hasRequiredDocuments()
    {
        return !empty($this->user_photo_path) && 
               (!empty($this->rg_photo_path) || !empty($this->cnh_photo_path)) &&
               !empty($this->quota_contract_photo_path);
    }

    /**
     * Check if user is authorized (not owner).
     */
    public function isAuthorizedUser()
    {
        return $this->is_authorized_user;
    }

    /**
     * Check if authorization document is uploaded for authorized users.
     */
    public function hasAuthorizationDocument()
    {
        return !$this->is_authorized_user || !empty($this->authorization_document_path);
    }

    /**
     * Check if Gov.br signature is completed.
     */
    public function hasGovBrSignature()
    {
        return !empty($this->gov_br_signature) && !empty($this->gov_br_signature_at);
    }

    /**
     * Check if KYC process is complete.
     */
    public function isKYCComplete()
    {
        return $this->hasRequiredDocuments() && 
               $this->hasAuthorizationDocument() && 
               $this->hasGovBrSignature() &&
               $this->kyc_status === 'approved';
    }

    /**
     * Get profile type constants.
     */
    public const PROFILE_CURIOSO = 'curioso';
    public const PROFILE_INTELIGENTE = 'inteligente';
    public const PROFILE_SABIO = 'sabio';

    /**
     * Get profile configuration.
     */
    public function getProfileConfig()
    {
        return match($this->profile_type) {
            self::PROFILE_CURIOSO => [
                'can_rent' => true,
                'can_publish' => false,
                'quota_days' => 7,
                'app_fee' => 150.00,
                'max_auctions' => 3,
                'can_fraction' => false,
                'max_cities_alerts' => 0,
                'max_search_results' => 3,
                'search_cooldown_hours' => 48,
                'mega_offer_days_before' => 3,
                'super_discount_days_before' => 14,
                'discount_links_days_before' => 7,
            ],
            self::PROFILE_INTELIGENTE => [
                'can_rent' => true,
                'can_publish' => true,
                'quota_days' => [3, 4],
                'app_fee' => [70.00, 100.00],
                'max_auctions_per_month' => 2,
                'can_fraction' => true,
                'fraction_blocks' => [3, 4],
                'max_cities_alerts' => 1,
                'max_search_results' => 5,
                'search_cooldown_hours' => 48,
                'mega_offer_days_before' => 5,
                'super_discount_days_before' => 14,
                'discount_links_days_before' => 14,
            ],
            self::PROFILE_SABIO => [
                'can_rent' => true,
                'can_publish' => true,
                'quota_days' => [2, 3, 4, 5, 7],
                'app_fee' => [50.00, 70.00, 100.00, 125.00, 150.00],
                'max_auctions_per_month' => 3,
                'can_fraction' => true,
                'fraction_blocks' => [
                    [2, 2, 3],
                    [2, 5],
                    [3, 4],
                ],
                'max_cities_alerts' => 3,
                'max_search_results' => 10,
                'search_cooldown_hours' => 0,
                'mega_offer_days_before' => 7,
                'super_discount_days_before' => 14,
                'discount_links_days_before' => 0,
            ],
            default => [],
        };
    }

    /**
     * Obter taxa de êxito dinâmica do banco de dados
     *
     * @param int $days Número de dias do fracionamento
     * @return float Valor da taxa de êxito
     */
    public function getSuccessFee(int $days): float
    {
        return \App\Models\SuccessFee::calculateFee($this->profile_type, $days);
    }

    /**
     * Obter todas as taxas de êxito ativas para este perfil
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuccessFees()
    {
        return \App\Models\SuccessFee::getActiveFeesForProfile($this->profile_type);
    }

    /**
     * Obter quota_days baseado nas taxas de êxito ativas
     * Retorna array de dias disponíveis para este perfil
     *
     * @return array Array de dias disponíveis
     */
    public function getAvailableQuotaDays(): array
    {
        $fees = $this->getSuccessFees();
        return $fees->pluck('days')->toArray();
    }

    /**
     * Verificar se um número de dias é válido para este perfil
     *
     * @param int $days Número de dias
     * @return bool
     */
    public function isValidQuotaDays(int $days): bool
    {
        return \App\Models\SuccessFee::feeExists($this->profile_type, $days);
    }

    /**
     * Obter configuração de perfil atualizada com taxas dinâmicas
     * Se não houver taxas no banco, retorna configuração padrão
     *
     * @return array
     */
    public function getProfileConfigWithDynamicFees(): array
    {
        $config = $this->getProfileConfig();
        
        // Tentar obter taxas dinâmicas do banco
        $dynamicFees = $this->getSuccessFees();
        
        if ($dynamicFees->count() > 0) {
            // Atualizar quota_days e app_fee com dados dinâmicos
            $config['quota_days'] = $dynamicFees->pluck('days')->toArray();
            $config['app_fee'] = $dynamicFees->map(function($fee) {
                return (float) $fee->fee_amount;
            })->toArray();
            
            // Se for apenas uma taxa, retornar como único valor
            if ($dynamicFees->count() === 1) {
                $config['quota_days'] = $dynamicFees->first()->days;
                $config['app_fee'] = (float) $dynamicFees->first()->fee_amount;
            }
        }
        
        return $config;
    }
}
