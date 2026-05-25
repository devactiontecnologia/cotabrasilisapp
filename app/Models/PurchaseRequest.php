<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hotel_id',
        'weeks',
        'month',
        'period_type',
        'city',
        'company',
        'price_range_min',
        'price_range_max',
        'observations',
        'status',
        'delegated_to_admin',
        'max_price',
        'purchase_fee_percentage',
    ];

    protected function casts(): array
    {
        return [
            'price_range_min' => 'decimal:2',
            'price_range_max' => 'decimal:2',
            'max_price' => 'decimal:2',
            'purchase_fee_percentage' => 'decimal:2',
            'delegated_to_admin' => 'boolean',
        ];
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hotel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Check if request is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Calculate purchase fee
     */
    public function calculateFee(float $purchasePrice): float
    {
        return $purchasePrice * ($this->purchase_fee_percentage / 100);
    }

    /**
     * Delegate to admin
     */
    public function delegateToAdmin(float $maxPrice): bool
    {
        $this->update([
            'delegated_to_admin' => true,
            'max_price' => $maxPrice,
            'status' => 'active',
        ]);

        return true;
    }

    /**
     * Calculate admin commission if delegated
     */
    public function calculateAdminCommission(float $actualPrice): float
    {
        if (!$this->delegated_to_admin || !$this->max_price) {
            return 0;
        }

        $difference = $this->max_price - $actualPrice;
        $commission = max($difference * 0.20, $difference * 0.20); // 20% da diferença ou 20% do valor máximo
        
        return $commission;
    }
}
