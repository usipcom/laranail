<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Nails\Laravel\Http\Middleware\EmailObfuscatorMiddleware;

class LaravelMiddlewareServiceProvider extends ServiceProvider
{

    public function register()
    {
    }

    public function boot()
    {

        // register middlewares
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('email-obfuscator', EmailObfuscatorMiddleware::class);

    }

}