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
    protected $signature = 'twilio:clear-settings {--user= : Clear settings for specific user ID} {--all : Clear all Twilio settings} {--force : Skip confirmation prompt}';

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
                $count = TwilioSettings::count();
                
                if ($count === 0) {
                    $this->info('No Twilio settings found to clear.');
                    return 0;
                }

                if (!$force && !$this->confirm("This will delete all {$count} Twilio settings records. Are you sure?")) {
                    $this->info('Operation cancelled.');
                    return 0;
                }

                TwilioSettings::truncate();
                $this->info("Successfully cleared all {$count} Twilio settings records.");
                
            } else {
                $twilioSettings = TwilioSettings::where('user_id', $userId)->first();
                
                if (!$twilioSettings) {
                    $this->error("No Twilio settings found for user ID: {$userId}");
                    return 1;
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
