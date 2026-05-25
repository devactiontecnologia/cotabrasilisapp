<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp',
        'is_active',
        'is_blocked',
        'blocked_until',
        'role',
        'is_admin',
        'ingress_date',
        'profile_approval_status',
        'profile_approved_at',
        'profile_rejected_at',
        'show_approval_success_modal',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_until' => 'datetime',
            'is_admin' => 'boolean',
            'ingress_date' => 'datetime',
            'profile_approved_at' => 'datetime',
            'profile_rejected_at' => 'datetime',
            'show_approval_success_modal' => 'boolean',
        ];
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's quotas.
     */
    public function quotas()
    {
        return $this->hasMany(Quota::class);
    }

    /**
     * Get the user's rental transactions.
     */
    public function rentalTransactions()
    {
        return $this->hasMany(QuotaTransaction::class, 'renter_id');
    }

    /**
     * Get the user's owned transactions.
     */
    public function ownedTransactions()
    {
        return $this->hasMany(QuotaTransaction::class, 'owner_id');
    }

    /**
     * Get the user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the admin logs created by this user.
     */
    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }

    /**
     * Get the KYC validations for this user.
     */
    public function kycValidations()
    {
        return $this->hasMany(KYCValidation::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->is_admin || $this->role === 'admin';
    }

    /**
     * Check if user is moderator.
     */
    public function isModerator()
    {
        return $this->role === 'moderator';
    }

    /**
     * Check if user has admin privileges.
     */
    public function hasAdminPrivileges()
    {
        return $this->isAdmin() || $this->isModerator();
    }

    /**
     * Check if user has completed KYC.
     */
    public function hasCompletedKYC()
    {
        return $this->profile && $this->profile->kyc_completed;
    }

    /**
     * Check if user's KYC is approved.
     */
    public function isKYCApproved()
    {
        return $this->profile && $this->profile->kyc_status === 'approved';
    }

    /**
     * Check if user's KYC is pending.
     */
    public function isKYCPending()
    {
        return $this->profile && in_array($this->profile->kyc_status, ['pending', 'under_review']);
    }

    /**
     * Get the favorite lists for the user.
     */
    public function favoriteLists()
    {
        return $this->hasMany(FavoriteList::class);
    }

    /**
     * Get the wishlist searches for the user.
     */
    public function wishlistSearches()
    {
        return $this->hasMany(WishlistSearch::class);
    }

    /**
     * Cotas marcadas como desejadas (estrela na busca).
     */
    public function wishlistQuotas()
    {
        return $this->belongsToMany(Quota::class, 'user_wishlist_quotas')->withTimestamps();
    }

    /**
     * Role constants.
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_USER = 'user';

    /** Profile approval status */
    public const PROFILE_APPROVAL_PENDING = 'pending';
    public const PROFILE_APPROVAL_APPROVED = 'approved';
    public const PROFILE_APPROVAL_REJECTED = 'rejected';

    /**
     * Check if user's profile has been approved by admin.
     */
    public function isProfileApproved(): bool
    {
        if ($this->hasAdminPrivileges()) {
            return true;
        }
        // null = usuários antigos sem a coluna preenchida, tratados como aprovados
        if ($this->profile_approval_status === null) {
            return true;
        }
        return $this->profile_approval_status === self::PROFILE_APPROVAL_APPROVED;
    }

    /**
     * Check if user's profile is pending approval.
     */
    public function isProfilePending(): bool
    {
        return $this->profile_approval_status === self::PROFILE_APPROVAL_PENDING;
    }

    /**
     * Check if user's profile was rejected.
     */
    public function isProfileRejected(): bool
    {
        return $this->profile_approval_status === self::PROFILE_APPROVAL_REJECTED;
    }

    /**
     * Whether to show the "account approved" congratulations modal once.
     */
    public function needsApprovalSuccessModal(): bool
    {
        return (bool) $this->show_approval_success_modal;
    }
}
