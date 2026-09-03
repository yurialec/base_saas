<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        Route::pattern('tenant', '[a-z0-9]+(?:-[a-z0-9]+)*');

        $this->routes(function () {
            Route::domain(static::tenantDomain())
                ->prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Get the domain pattern used by tenant routes.
     *
     * @return string
     */
    public static function tenantDomain(): string
    {
        return '{tenant}.'.static::applicationDomain();
    }

    /**
     * Get the host configured as the application's root domain.
     *
     * @return string
     */
    public static function applicationDomain(): string
    {
        $applicationHost = parse_url(config('app.url'), PHP_URL_HOST);

        if (! $applicationHost) {
            throw new \RuntimeException('A variável APP_URL deve conter uma URL válida.');
        }

        return $applicationHost;
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
    }
}
