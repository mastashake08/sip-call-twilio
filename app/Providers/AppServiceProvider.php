<?php

namespace App\Providers;

use App\Events\CallContactRequested;
use App\Events\SmsContactRequested;
use App\Listeners\HandleCallContact;
use App\Listeners\HandleSmsContact;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(CallContactRequested::class, HandleCallContact::class);
        Event::listen(SmsContactRequested::class, HandleSmsContact::class);
    }
}
