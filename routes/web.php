<?php

use App\Models\TwilioSettings;
use App\Models\WebhookLog;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\ContactController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    /** @var User $user */
    $user = Auth::user();
    
    $twilioSettings = $user->twilioSettings;
    
    $stats = [
        'total_calls' => $user->webhookLogs()->where('type', 'voice')->count(),
        'total_sms' => $user->webhookLogs()->whereIn('type', ['sms', 'sms_forward'])->count(),
        'total_errors' => $user->webhookLogs()->where('status', 'error')->count(),
        'recent_activity' => $user->webhookLogs()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'type', 'from_number', 'status', 'created_at']),
        'twilio_configured' => $twilioSettings && $twilioSettings->twilio_phone_number !== null,
    ];
    
    return Inertia::render('Dashboard', [
        'stats' => $stats
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Contact management routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('contacts', ContactController::class);
    Route::post('contacts/{contact}/toggle-favorite', [ContactController::class, 'toggleFavorite'])->name('contacts.toggle-favorite');
    Route::post('contacts/{contact}/call', [ContactController::class, 'call'])->name('contacts.call');
    Route::post('contacts/{contact}/sms', [ContactController::class, 'sms'])->name('contacts.sms');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/twilio.php';
