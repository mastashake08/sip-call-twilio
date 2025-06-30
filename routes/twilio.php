<?php

use App\Http\Controllers\TwilioSettingsController;
use Illuminate\Support\Facades\Route;

// Protected Twilio routes
Route::middleware('auth')->group(function () {
    Route::get('twilio/settings', [TwilioSettingsController::class, 'index'])->name('twilio.settings');
    Route::post('twilio/settings', [TwilioSettingsController::class, 'update'])->name('twilio.settings.update');
    Route::get('twilio/logs', [TwilioSettingsController::class, 'logs'])->name('twilio.logs');
    Route::get('api/twilio/logs', [TwilioSettingsController::class, 'logsData'])->name('twilio.logs.data');
});
