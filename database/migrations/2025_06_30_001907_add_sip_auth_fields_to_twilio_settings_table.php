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
        Schema::table('twilio_settings', function (Blueprint $table) {
            $table->string('sip_username')->nullable()->after('sip_endpoint');
            $table->string('sip_password')->nullable()->after('sip_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('twilio_settings', function (Blueprint $table) {
            $table->dropColumn(['sip_username', 'sip_password']);
        });
    }
};
