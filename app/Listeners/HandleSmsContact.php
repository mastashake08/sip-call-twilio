<?php

namespace App\Listeners;

use App\Events\SmsContactRequested;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleSmsContact implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SmsContactRequested $event): void
    {
        try {
            $contact = $event->contact;
            $user = $event->user;
            $message = $event->message;
            $twilioSettings = $user->twilioSettings;

            if (!$twilioSettings) {
                Log::warning("SMS requested but no Twilio settings for user {$user->id}");
                return;
            }

            $twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            // Send SMS
            $sms = $twilio->messages->create(
                $contact->phone_number, // to
                [
                    'from' => $twilioSettings->twilio_phone_number,
                    'body' => $message,
                ]
            );

            Log::info("SMS sent", [
                'message_sid' => $sms->sid,
                'contact_id' => $contact->id,
                'user_id' => $user->id,
                'to' => $contact->phone_number,
                'from' => $twilioSettings->twilio_phone_number,
                'message_length' => strlen($message),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'error' => $e->getMessage(),
                'contact_id' => $contact->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
