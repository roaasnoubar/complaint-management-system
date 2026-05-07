<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // تسجيل الميدل وير
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // أضيفي هذا القسم هنا لتسجيل أمر التصعيد يدوياً
    ->withCommands([
        \App\Console\Commands\EscalateComplaints::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\CheckEscalation::class);
    })
    ->create();