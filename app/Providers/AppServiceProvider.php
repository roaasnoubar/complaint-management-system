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
    // 1. مراقبي النماذج (تأكدي أن الأسماء صحيحة)
    if (class_exists(\App\Models\Complain::class)) {
        \App\Models\Complain::observe(\App\Observers\ComplainObserver::class);
    }

    // 2. المحرض التلقائي (Trigger)
    // قمنا بإزالة شرط request()->is('api/*') مؤقتاً لضمان العمل 
    try {
        \Illuminate\Support\Facades\Artisan::call('escalate:complaints');
    } catch (\Exception $e) {
        // لا نفعل شيئاً في حال الخطأ
    }
}
}
