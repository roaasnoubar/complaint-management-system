<?php

namespace App\Providers;

use App\Models\Complain;
use App\Models\ChatMessage;
use App\Models\CantMessage;
use App\Observers\ComplainObserver;
use App\Observers\ChatMessageObserver;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register NotificationService as a singleton
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        // Register Eloquent observers
        Complain::observe(ComplainObserver::class);
        CantMessage::observe(ChatMessageObserver::class);
        ChatMessage::observe(ChatMessageObserver::class);
    }
}
