<?php

namespace App\Http\Controllers;

use App\Models\TwilioSettings;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
use Twilio\TwiML\MessagingResponse;

class TwilioWebhookController extends Controller
{
    /**
     * Handle incoming voice calls
     */
    public function voice(Request $request)
    {
        $response = new VoiceResponse();

        try {
            // Get the Twilio phone number from the request
            $toNumber = $request->input('To');
            $fromNumber = $request->input('From');
            $callSid = $request->input('CallSid');

            // Find user by their Twilio phone number
            $twilioSettings = TwilioSettings::where('twilio_phone_number', $toNumber)->first();
            
            if (!$twilioSettings) {
                $response->say('This number is not configured. Please contact support.');
                return response($response, 200, ['Content-Type' => 'text/xml']);
            }

            $user = $twilioSettings->user;

            // Log the incoming call
            WebhookLog::create([
                'user_id' => $user->id,
                'type' => 'voice',
                'from_number' => $fromNumber,
                'to_number' => $toNumber,
                'call_sid' => $callSid,
                'status' => 'received',
                'twilio_data' => $request->all(),
            ]);

            // Custom greeting if available
            if ($twilioSettings->custom_greeting) {
                $response->say($twilioSettings->custom_greeting);
            }

            // Handle call action based on settings
            if ($twilioSettings->call_action === 'dial_phone' && $twilioSettings->forward_to_phone) {
                $dial = $response->dial('', ['timeout' => 30]);
                $dial->number($twilioSettings->forward_to_phone);
            } elseif ($twilioSettings->call_action === 'dial_sip' && $twilioSettings->sip_endpoint) {
                $dial = $response->dial('', ['timeout' => 30]);
                
                // Build SIP URI with authentication if provided
                $sipUri = $twilioSettings->sip_endpoint;
                if ($twilioSettings->sip_username && $twilioSettings->sip_password) {
                    // Parse the SIP endpoint to inject credentials
                    $parsedUri = parse_url($sipUri);
                    if ($parsedUri && isset($parsedUri['scheme']) && $parsedUri['scheme'] === 'sip') {
                        $userInfo = $twilioSettings->sip_username . ':' . $twilioSettings->sip_password;
                        $sipUri = $parsedUri['scheme'] . '://' . $userInfo . '@' . $parsedUri['host'];
                        if (isset($parsedUri['port'])) {
                            $sipUri .= ':' . $parsedUri['port'];
                        }
                        if (isset($parsedUri['path'])) {
                            $sipUri .= $parsedUri['path'];
                        }
                    }
                }
                
                $dial->sip($sipUri);
            } else {
                $response->say('No forwarding configured. Please contact support.');
            }

            // Update log with success status
            WebhookLog::where('call_sid', $callSid)
                ->where('user_id', $user->id)
                ->update(['status' => 'processed']);

        } catch (\Exception $e) {
            // Log error
            if (isset($user)) {
                WebhookLog::create([
                    'user_id' => $user->id ?? null,
                    'type' => 'voice',
                    'from_number' => $fromNumber ?? null,
                    'to_number' => $toNumber ?? null,
                    'call_sid' => $callSid ?? null,
                    'status' => 'error',
                    'content' => $e->getMessage(),
                    'twilio_data' => $request->all(),
                ]);
            }

            $response->say('An error occurred. Please try again later.');
        }

        return response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Handle incoming SMS messages
     */
    public function sms(Request $request)
    {
        $response = new MessagingResponse();

        try {
            $toNumber = $request->input('To');
            $fromNumber = $request->input('From');
            $messageBody = $request->input('Body');
            $messageSid = $request->input('MessageSid');

            // Find user by their Twilio phone number
            $twilioSettings = TwilioSettings::where('twilio_phone_number', $toNumber)->first();
            
            if (!$twilioSettings) {
                return response($response, 200, ['Content-Type' => 'text/xml']);
            }

            $user = $twilioSettings->user;

            // Log the incoming SMS
            WebhookLog::create([
                'user_id' => $user->id,
                'type' => 'sms',
                'from_number' => $fromNumber,
                'to_number' => $toNumber,
                'content' => $messageBody,
                'message_sid' => $messageSid,
                'status' => 'received',
                'twilio_data' => $request->all(),
            ]);

            // Forward SMS if enabled and forwarding number is set
            if ($twilioSettings->sms_forwarding_enabled && $twilioSettings->forward_to_phone) {
                $this->forwardSms($user, $fromNumber, $messageBody, $twilioSettings->forward_to_phone);
            }

            // Update log with success status
            WebhookLog::where('message_sid', $messageSid)
                ->where('user_id', $user->id)
                ->update(['status' => 'processed']);

        } catch (\Exception $e) {
            // Log error
            if (isset($user)) {
                WebhookLog::create([
                    'user_id' => $user->id ?? null,
                    'type' => 'sms',
                    'from_number' => $fromNumber ?? null,
                    'to_number' => $toNumber ?? null,
                    'content' => $messageBody ?? null,
                    'message_sid' => $messageSid ?? null,
                    'status' => 'error',
                    'twilio_data' => $request->all(),
                ]);
            }
        }

        return response($response, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Forward SMS to user's phone number
     */
    private function forwardSms(User $user, string $fromNumber, string $messageBody, string $forwardToPhone)
    {
        try {
            $twilioSid = config('services.twilio.sid');
            $twilioToken = config('services.twilio.token');
            $twilioFromNumber = config('services.twilio.from');

            if (!$twilioSid || !$twilioToken || !$twilioFromNumber) {
                throw new \Exception('Twilio credentials not configured');
            }

            $twilio = new Client($twilioSid, $twilioToken);

            $forwardedMessage = "Forwarded from {$fromNumber}: {$messageBody}";

            $twilio->messages->create(
                $forwardToPhone,
                [
                    'from' => $twilioFromNumber,
                    'body' => $forwardedMessage,
                ]
            );

            // Log the forwarded message
            WebhookLog::create([
                'user_id' => $user->id,
                'type' => 'sms_forward',
                'from_number' => $twilioFromNumber,
                'to_number' => $forwardToPhone,
                'content' => $forwardedMessage,
                'status' => 'sent',
            ]);

        } catch (\Exception $e) {
            // Log forwarding error
            WebhookLog::create([
                'user_id' => $user->id,
                'type' => 'sms_forward',
                'from_number' => $twilioFromNumber ?? null,
                'to_number' => $forwardToPhone,
                'content' => "Failed to forward message: {$e->getMessage()}",
                'status' => 'error',
            ]);
        }
    }
}
