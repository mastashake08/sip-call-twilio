<?php

namespace App\Console\Commands;

use App\Models\TwilioSettings;
use Illuminate\Console\Command;

class ListTwilioSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:list-settings {--user= : Show settings for specific user ID} {--detailed : Show detailed view}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List Twilio settings for all users or a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $detailed = $this->option('detailed');

        try {
            if ($userId) {
                $twilioSettings = TwilioSettings::with('user')->where('user_id', $userId)->first();
                
                if (!$twilioSettings) {
                    $this->error("No Twilio settings found for user ID: {$userId}");
                    return 1;
                }

                $this->info("Twilio Settings for User ID {$userId}:");
                $this->displayDetailedSettings($twilioSettings);
                
            } else {
                $settings = TwilioSettings::with('user')->get();
                $count = $settings->count();
                
                if ($count === 0) {
                    $this->info('No Twilio settings found.');
                    return 0;
                }

                $this->info("All Twilio Settings ({$count} records):");
                
                if ($detailed) {
                    foreach ($settings as $setting) {
                        $this->newLine();
                        $this->displayDetailedSettings($setting);
                        $this->newLine();
                    }
                } else {
                    $this->table(
                        ['ID', 'User ID', 'User Email', 'Twilio Phone', 'Forward To', 'SIP Endpoint', 'Call Action', 'SMS Forwarding'],
                        $settings->map(function ($setting) {
                            return [
                                $setting->id,
                                $setting->user_id,
                                $setting->user->email ?? 'N/A',
                                $setting->twilio_phone_number ?? 'N/A',
                                $setting->forward_to_phone ?? 'N/A',
                                $setting->sip_endpoint ?? 'N/A',
                                $setting->call_action ?? 'N/A',
                                $setting->sms_forwarding_enabled ? 'Yes' : 'No',
                            ];
                        })->toArray()
                    );
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('An error occurred while listing Twilio settings: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display detailed settings for a single record
     */
    private function displayDetailedSettings(TwilioSettings $settings)
    {
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $settings->id],
                ['User ID', $settings->user_id],
                ['User Email', $settings->user->email ?? 'N/A'],
                ['Twilio Phone Number', $settings->twilio_phone_number ?? 'N/A'],
                ['Forward To Phone', $settings->forward_to_phone ?? 'N/A'],
                ['SIP Endpoint', $settings->sip_endpoint ?? 'N/A'],
                ['SIP Username', $settings->sip_username ?? 'N/A'],
                ['SIP Password', $settings->sip_password ? '***SET***' : 'N/A'],
                ['Call Action', $settings->call_action ?? 'N/A'],
                ['SMS Forwarding Enabled', $settings->sms_forwarding_enabled ? 'Yes' : 'No'],
                ['Custom Greeting', $settings->custom_greeting ?? 'N/A'],
                ['Created At', $settings->created_at->format('Y-m-d H:i:s')],
                ['Updated At', $settings->updated_at->format('Y-m-d H:i:s')],
            ]
        );
    }
}
