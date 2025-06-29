<?php

use App\Http\Controllers\TwilioSettingsController;
use App\Http\Controllers\TwilioWebhookController;
use Illuminate\Support\Facades\Route;

// Protected Twilio routes
Route::middleware('auth')->group(function () {
    Route::get('twilio/settings', [TwilioSettingsController::class, 'index'])->name('twilio.settings');
    Route::post('twilio/settings', [TwilioSettingsController::class, 'update'])->name('twilio.settings.update');
    Route::get('twilio/logs', [TwilioSettingsController::class, 'logs'])->name('twilio.logs');
    Route::get('api/twilio/logs', [TwilioSettingsController::class, 'logsData'])->name('twilio.logs.data');
});

// Public webhook endpoints
Route::post('webhooks/twilio/voice', [TwilioWebhookController::class, 'voice'])->name('twilio.webhook.voice');
Route::post('webhooks/twilio/sms', [TwilioWebhookController::class, 'sms'])->name('twilio.webhook.sms');
