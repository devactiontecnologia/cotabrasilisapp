<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsaasWalletTransfer extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'asaas_subaccount_id',
        'amount',
        'destination_wallet_id',
        'asaas_transfer_id',
        'status',
        'error_message',
        'asaas_response',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'asaas_response' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subaccount(): BelongsTo
    {
        return $this->belongsTo(AsaasSubaccount::class, 'asaas_subaccount_id');
    }
}
