<?php

namespace App\Listeners;

use App\Events\CallContactRequested;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Twilio\TwiML\VoiceResponse;
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
            
            $response = new VoiceResponse();
            $dial = $response->dial('', ['timeout' => 30]);
            $dial->number($twilioSettings->forward_to_phone);
            
            $call = $twilio->calls->create(
                $contact->phone_number, // to
                $twilioSettings->twilio_phone_number, 
                [
                    'twiml' => $response,
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
