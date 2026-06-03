<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: realpath(__DIR__.'/../')) 
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. تفعيل الـ CORS للسماح بالاتصال من أي مصدر
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // 2. دمج الميدل وير الخاص بك
        $middleware->append(\App\Http\Middleware\CheckEscalation::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالدخول، يرجى تسجيل الدخول أولاً.'
                ], 401);
            }
        });
    })
    ->withCommands([
        \App\Console\Commands\EscalateComplaints::class,
    ])
    ->create();