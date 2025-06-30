<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwilioSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'twilio_phone_number',
        'forward_to_phone',
        'sip_endpoint',
        'sip_username',
        'sip_password',
        'call_action',
        'sms_forwarding_enabled',
        'custom_greeting',
    ];

    protected $casts = [
        'sms_forwarding_enabled' => 'boolean',
        'sip_password' => 'encrypted',
    ];

    /**
     * Get the user that owns the Twilio settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
