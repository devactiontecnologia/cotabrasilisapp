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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'profile.complete' => \App\Http\Middleware\CheckProfileComplete::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'valid.registration' => \App\Http\Middleware\CheckValidRegistration::class,
        ]);
        
        // Excluir webhook do Asaas do CSRF
        $middleware->validateCsrfTokens(except: [
            'webhooks/asaas',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
