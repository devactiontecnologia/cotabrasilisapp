<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KYCValidation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kyc_validations';

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'document_photo_path',
        'validation_status',
        'rejection_reason',
        'ocr_data',
        'validation_metadata',
        'validated_at',
        'validated_by',
    ];

    protected function casts(): array
    {
        return [
            'ocr_data' => 'array',
            'validation_metadata' => 'array',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the validation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who validated this document.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Check if validation is pending.
     */
    public function isPending()
    {
        return $this->validation_status === 'pending';
    }

    /**
     * Check if validation is processing.
     */
    public function isProcessing()
    {
        return $this->validation_status === 'processing';
    }

    /**
     * Check if validation is approved.
     */
    public function isApproved()
    {
        return $this->validation_status === 'approved';
    }

    /**
     * Check if validation is rejected.
     */
    public function isRejected()
    {
        return $this->validation_status === 'rejected';
    }

    /**
     * Document type constants.
     */
    public const DOCUMENT_RG = 'rg';
    public const DOCUMENT_CNH = 'cnh';
    public const DOCUMENT_PASSPORT = 'passport';

    /**
     * Validation status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
}
