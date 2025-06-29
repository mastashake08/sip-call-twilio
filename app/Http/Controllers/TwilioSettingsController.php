<?php

namespace App\Http\Controllers;

use App\Models\TwilioSettings;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwilioSettingsController extends Controller
{
    /**
     * Display the Twilio settings page.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $settings = $user->twilioSettings ?? new TwilioSettings();
        
        return Inertia::render('Twilio/Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the Twilio settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'twilio_phone_number' => 'nullable|string|max:20',
            'forward_to_phone' => 'nullable|string|max:20',
            'sip_endpoint' => 'nullable|string|max:255',
            'call_action' => 'required|in:dial_phone,dial_sip',
            'sms_forwarding_enabled' => 'boolean',
            'custom_greeting' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        $user->twilioSettings()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('success', 'Twilio settings updated successfully.');
    }

    /**
     * Display webhook logs.
     */
    public function logs(): Response
    {
        $user = Auth::user();
        $logs = $user->webhookLogs()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Twilio/Logs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Get webhook logs data for API calls.
     */
    public function logsData(Request $request)
    {
        $user = Auth::user();
        $query = $user->webhookLogs()
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('from_number', 'like', "%{$search}%")
                  ->orWhere('to_number', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20);

        return response()->json($logs);
    }
}
