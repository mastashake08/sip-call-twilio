<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'email',
        'notes',
        'is_favorite',
        'tags',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the user that owns the contact.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get favorite contacts
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope to search contacts by name or phone
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Format phone number for display
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone_number);
        
        if (strlen($phone) === 11 && $phone[0] === '1') {
            // US number with country code
            return sprintf('+1 (%s) %s-%s', 
                substr($phone, 1, 3), 
                substr($phone, 4, 3), 
                substr($phone, 7, 4)
            );
        } elseif (strlen($phone) === 10) {
            // US number without country code
            return sprintf('(%s) %s-%s', 
                substr($phone, 0, 3), 
                substr($phone, 3, 3), 
                substr($phone, 6, 4)
            );
        }
        
        return $this->phone_number;
    }

    /**
     * Get initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        
        return substr($initials, 0, 2);
    }
}
