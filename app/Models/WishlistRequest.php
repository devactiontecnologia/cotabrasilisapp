<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class WishlistRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'city',
        'state',
        'desired_start_date',
        'desired_end_date',
        'number_of_people',
        'number_of_rooms',
        'max_price',
        'priority',
        'status',
        'fulfilled_at',
        'fulfilled_by_offer_id',
        'admin_notes',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'desired_start_date' => 'date',
            'desired_end_date' => 'date',
            'max_price' => 'decimal:2',
            'fulfilled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the wishlist request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the offer that fulfilled this request.
     */
    public function fulfilledByOffer()
    {
        return $this->belongsTo(RentalOffer::class, 'fulfilled_by_offer_id');
    }

    /**
     * Check if request is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at > now());
    }

    /**
     * Check if request is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at <= now();
    }

    /**
     * Mark request as fulfilled.
     */
    public function markAsFulfilled(RentalOffer $offer): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
            'fulfilled_by_offer_id' => $offer->id,
        ]);

        return true;
    }

    /**
     * Cancel request.
     */
    public function cancel(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
        ]);

        return true;
    }

    /**
     * Check if request matches an offer.
     */
    public function matchesOffer(RentalOffer $offer): bool
    {
        // Verificar cidade
        if ($this->city !== $offer->city) {
            return false;
        }

        // Verificar estado
        if ($this->state !== $offer->state) {
            return false;
        }

        // Verificar sobreposição de datas
        if ($this->desired_start_date > $offer->end_date || 
            $this->desired_end_date < $offer->start_date) {
            return false;
        }

        // Verificar número de pessoas
        if ($this->number_of_people > $offer->number_of_people) {
            return false;
        }

        // Verificar preço máximo
        if ($this->max_price && $offer->price > $this->max_price) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active requests.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired requests.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Scope for fulfilled requests.
     */
    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    /**
     * Scope for requests by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            default => 'Média'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Ativa',
            'fulfilled' => 'Atendida',
            'cancelled' => 'Cancelada',
            'expired' => 'Expirada',
            default => 'Desconhecido'
        };
    }
}
