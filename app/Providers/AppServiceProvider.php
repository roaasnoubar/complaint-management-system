<?php

namespace App\Providers;

use App\Models\Complain;
use App\Models\ChatMessage; 
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
        // 1. مراقب الشكاوى
        Complain::observe(ComplainObserver::class);

        // 2. مراقب الرسائل (نستخدم ChatMessage فقط)
        if (class_exists(ChatMessage::class)) {
            ChatMessage::observe(ChatMessageObserver::class);
        }
        
        // ملاحظة: قمت بحذف السطر الذي يحتوي على CantMessage لأنه يسبب الخطأ
    }
}