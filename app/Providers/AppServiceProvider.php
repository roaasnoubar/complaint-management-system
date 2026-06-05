<?php

namespace App\Providers;

use App\Models\Complain;
use App\Models\ChatMessage; 
use App\Observers\ComplainObserver;
use App\Observers\ChatMessageObserver;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register NotificationService as a singleton
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
{
    if (class_exists(\App\Models\Complain::class)) {
        \App\Models\Complain::observe(\App\Observers\ComplainObserver::class);
    }

   
    try {
        \Illuminate\Support\Facades\Artisan::call('escalate:complaints');
    } catch (\Exception $e) {
        
    }
}
}
