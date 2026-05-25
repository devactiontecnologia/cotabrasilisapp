<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'address',
        'phone',
        'email',
        'description',
        'images',
        'amenities',
        'website',
        'rating',
        'is_active',
        'is_functioning',
        'city',
        'state',
        'status_reason',
        'zip_code',
        'stars',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'images' => 'array',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
            'is_functioning' => 'boolean',
            'stars' => 'integer',
        ];
    }

    /**
     * Get the quotas for this hotel.
     */
    public function quotas()
    {
        return $this->hasMany(Quota::class, 'hotel_name', 'name');
    }

    /**
     * Check if hotel is functioning (can rent)
     */
    public function isFunctioning(): bool
    {
        return $this->is_functioning && $this->is_active;
    }

    /**
     * Check if hotel allows rentals
     */
    public function allowsRentals(): bool
    {
        return $this->isFunctioning();
    }

    /**
     * Check if hotel allows sales/exchanges
     */
    public function allowsSales(): bool
    {
        return $this->is_active; // Hotel pode estar inativo mas ainda permitir venda/troca
    }

    /**
     * Get hotel status label
     */
    public function getStatusLabel(): string
    {
        if (!$this->is_active) {
            return 'Inativo';
        }
        
        if (!$this->is_functioning) {
            return 'Não Funcionando';
        }
        
        return 'Funcionando';
    }

    /**
     * Scope for functioning hotels
     */
    public function scopeFunctioning($query)
    {
        return $query->where('is_functioning', true)->where('is_active', true);
    }

    /**
     * Scope for hotels that allow rentals
     */
    public function scopeAllowsRentals($query)
    {
        return $query->where('is_functioning', true)->where('is_active', true);
    }

    /**
     * Scope for hotels that allow sales
     */
    public function scopeAllowsSales($query)
    {
        return $query->where('is_active', true);
    }
}
