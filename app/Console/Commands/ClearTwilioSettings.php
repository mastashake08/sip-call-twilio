<?php

namespace App\Console\Commands;

use App\Models\TwilioSettings;
use Illuminate\Console\Command;

class ClearTwilioSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:clear-settings {--user= : Clear settings for specific user ID} {--all : Clear all Twilio settings} {--force : Skip confirmation prompt} {--list : List current settings before clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Twilio settings for a specific user or all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $all = $this->option('all');
        $force = $this->option('force');
        $list = $this->option('list');

        if (!$userId && !$all) {
            $this->error('You must specify either --user=ID or --all flag');
            return 1;
        }

        if ($userId && $all) {
            $this->error('You cannot use both --user and --all flags together');
            return 1;
        }

        try {
            if ($all) {
                $settings = TwilioSettings::with('user')->get();
                $count = $settings->count();
                
                if ($count === 0) {
                    $this->info('No Twilio settings found to clear.');
                    return 0;
                }

                if ($list) {
                    $this->info("Current Twilio Settings ({$count} records):");
                    $this->table(
                        ['ID', 'User ID', 'User Email', 'Twilio Phone', 'Forward To', 'SIP Endpoint', 'Call Action'],
                        $settings->map(function ($setting) {
                            return [
                                $setting->id,
                                $setting->user_id,
                                $setting->user->email ?? 'N/A',
                                $setting->twilio_phone_number ?? 'N/A',
                                $setting->forward_to_phone ?? 'N/A',
                                $setting->sip_endpoint ?? 'N/A',
                                $setting->call_action ?? 'N/A',
                            ];
                        })->toArray()
                    );
                    $this->newLine();
                }

                if (!$force && !$this->confirm("This will delete all {$count} Twilio settings records. Are you sure?")) {
                    $this->info('Operation cancelled.');
                    return 0;
                }

                TwilioSettings::truncate();
                $this->info("Successfully cleared all {$count} Twilio settings records.");
                
            } else {
                $twilioSettings = TwilioSettings::with('user')->where('user_id', $userId)->first();
                
                if (!$twilioSettings) {
                    $this->error("No Twilio settings found for user ID: {$userId}");
                    return 1;
                }

                if ($list) {
                    $this->info("Current Twilio Settings for User ID {$userId}:");
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['ID', $twilioSettings->id],
                            ['User Email', $twilioSettings->user->email ?? 'N/A'],
                            ['Twilio Phone', $twilioSettings->twilio_phone_number ?? 'N/A'],
                            ['Forward To', $twilioSettings->forward_to_phone ?? 'N/A'],
                            ['SIP Endpoint', $twilioSettings->sip_endpoint ?? 'N/A'],
                            ['SIP Username', $twilioSettings->sip_username ?? 'N/A'],
                            ['Call Action', $twilioSettings->call_action ?? 'N/A'],
                            ['SMS Forwarding', $twilioSettings->sms_forwarding_enabled ? 'Enabled' : 'Disabled'],
                            ['Custom Greeting', $twilioSettings->custom_greeting ?? 'N/A'],
                            ['Created At', $twilioSettings->created_at],
                            ['Updated At', $twilioSettings->updated_at],
                        ]
                    );
                    $this->newLine();
                }

                if (!$force && !$this->confirm("This will delete Twilio settings for user ID {$userId}. Are you sure?")) {
                    $this->info('Operation cancelled.');
                    return 0;
                }

                $twilioSettings->delete();
                $this->info("Successfully cleared Twilio settings for user ID: {$userId}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('An error occurred while clearing Twilio settings: ' . $e->getMessage());
            return 1;
        }
    }
}
