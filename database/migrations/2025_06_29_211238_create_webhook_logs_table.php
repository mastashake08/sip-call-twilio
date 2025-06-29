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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['voice', 'sms']); 
            $table->string('from_number');
            $table->string('to_number');
            $table->text('content')->nullable(); // SMS content or call details
            $table->string('call_sid')->nullable(); // Twilio Call SID
            $table->string('message_sid')->nullable(); // Twilio Message SID
            $table->enum('status', ['received', 'processed', 'forwarded', 'failed'])->default('received');
            $table->json('twilio_data'); // Store full Twilio webhook data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
