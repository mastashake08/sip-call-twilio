<?php

namespace App\Listeners;

use App\Events\CallContactRequested;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleCallContact implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(CallContactRequested $event): void
    {
        try {
            $contact = $event->contact;
            $user = $event->user;
            $twilioSettings = $user->twilioSettings;

            if (!$twilioSettings) {
                Log::warning("Call requested but no Twilio settings for user {$user->id}");
                return;
            }

            $twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            // Create a call based on user's settings
            $webhookUrl = config('app.url') . '/webhooks/twilio/voice';
            
            $call = $twilio->calls->create(
                $contact->phone_number, // to
                $twilioSettings->twilio_phone_number, // from
                [
                    'url' => $webhookUrl,
                    'method' => 'POST',
                ]
            );

            Log::info("Call initiated", [
                'call_sid' => $call->sid,
                'contact_id' => $contact->id,
                'user_id' => $user->id,
                'to' => $contact->phone_number,
                'from' => $twilioSettings->twilio_phone_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to initiate call', [
                'error' => $e->getMessage(),
                'contact_id' => $contact->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
