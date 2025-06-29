<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'from_number',
        'to_number',
        'content',
        'call_sid',
        'message_sid',
        'status',
        'twilio_data',
    ];

    protected $casts = [
        'twilio_data' => 'array',
    ];

    /**
     * Get the user that owns the webhook log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
