<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('twilio_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('twilio_phone_number')->nullable();
            $table->string('forward_to_phone')->nullable();
            $table->string('sip_endpoint')->nullable();
            $table->enum('call_action', ['dial_phone', 'dial_sip'])->default('dial_phone');
            $table->boolean('sms_forwarding_enabled')->default(true);
            $table->text('custom_greeting')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twilio_settings');
    }
};
