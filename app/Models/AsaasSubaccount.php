<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsaasSubaccount extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'asaas_account_id',
        'wallet_id',
        'api_key',
        'status',
        'last_error',
        'created_from_transaction_id',
        'cached_balance',
        'balance_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'cached_balance' => 'decimal:2',
            'balance_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdFromTransaction(): BelongsTo
    {
        return $this->belongsTo(QuotaTransaction::class, 'created_from_transaction_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AsaasWalletTransfer::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->wallet_id && $this->api_key;
    }
}
