<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            App\Http\Middleware\CheckEmployeeStatus::class,

        ]);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            App\Http\Middleware\CheckEmployeeStatus::class,
            \App\Http\Middleware\RemoveContentLength::class,
        ]);

        $middleware->group('web-auth', [
            \App\Http\Middleware\LoginCheck::class,
            \App\Http\Middleware\UserCheck::class
        ]);

        $middleware->group('userCheck', [
            \App\Http\Middleware\UserCheck::class
        ]);

        $middleware->group('isActive', [
            \App\Http\Middleware\CheckEmployeeStatus::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            // 'stripe/*',
        ]);

        $middleware->alias([
            'subscription' => \App\Http\Middleware\CheckSubscriptionAccess::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenance::class,
        ]);
    })

    ->withSchedule(function ($schedule) {
        // Check for devices that need syncing every minute
        $schedule->command('devices:check-sync')->everyMinute();

        // Optional: Add logging
        $schedule->command('devices:check-sync')
            ->everyMinute()
            ->onSuccess(function () {
                Log::info('Device sync check completed successfully');
            })
            ->onFailure(function () {
                Log::error('Device sync check failed');
            });

        $schedule->command('subscriptions:check-expirations')->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
