<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\TadaRequestPlan;
use App\Observers\EmployeeObserver;
use App\Observers\TadaRequestPlanObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\ChannelManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
        $loader->alias('Alert', \RealRashid\SweetAlert\Facades\Alert::class);

        $loader->alias('loginCheck', \App\Http\Middleware\LoginCheck::class);
        $loader->alias('userCheck', \App\Http\Middleware\UserCheck::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Force HTTPS only if the request came via proxy as HTTPS
        if (request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // API Rate Limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->emp_id ?: $request->ip());
        });

        // Global Rate Limiter
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(6);
        });

        // Observers
        TadaRequestPlan::observe(TadaRequestPlanObserver::class);
        Employee::observe(EmployeeObserver::class);

        // Superadmin routes
        Route::middleware('web')
            ->group(base_path('routes/superadmin.php'));

        // Custom Notification Channel
        $this->app->make(ChannelManager::class)->extend('fcm', function ($app) {
            return new FcmChannel($app->make(Messaging::class));
        });
    }
}
