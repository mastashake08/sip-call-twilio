<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Public webhook endpoints
Route::post('webhooks/twilio/voice', [TwilioWebhookController::class, 'voice'])->name('twilio.webhook.voice');
Route::post('webhooks/twilio/sms', [TwilioWebhookController::class, 'sms'])->name('twilio.webhook.sms');
