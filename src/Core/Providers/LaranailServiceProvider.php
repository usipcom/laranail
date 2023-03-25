<?php declare(strict_types=1);

namespace Simtabi\Laranail\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Core\Commands\SetAppNamespace;
use Simtabi\Laranail\Core\Commands\CleanupApplication;
use Simtabi\Laranail\Nails\Archiver\Providers\ArchiverServiceProvider;
use Simtabi\Laranail\Nails\Blade\Providers\BladeServiceProvider;
use Simtabi\Laranail\Nails\Laravel\Providers\LaravelMiddlewareServiceProvider;
use Simtabi\Laranail\Nails\Laravel\Traits\HasPackageTools;
use Simtabi\Laranail\Nails\Macros\Providers\MacrosServiceProvider;

class LaranailServiceProvider extends ServiceProvider
{
    use HasPackageTools;

    private string $packageName = 'laranail';
    public  const  PACKAGE_PATH = __DIR__.'/../../../';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->loadTranslationsFrom(self::PACKAGE_PATH . "resources/lang/", $this->packageName);
        $this->loadMigrationsFrom(self::PACKAGE_PATH . 'database/migrations');
        $this->loadViewsFrom(self::PACKAGE_PATH . "resources/views", $this->packageName);
        $this->mergeConfigFrom(self::PACKAGE_PATH . "config/config.php", $this->packageName);

    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->registerConsoles();

        // register other service providers
        $this->app->register(BladeServiceProvider::class);
        $this->app->register(MacrosServiceProvider::class);
        $this->app->register(ArchiverServiceProvider::class);
        $this->app->register(LaravelMiddlewareServiceProvider::class);
    }

    private function registerConsoles(): static
    {
        if ($this->app->runningInConsole())
        {
            $this->commands([
                CleanupApplication::class,
                SetAppNamespace::class,
            ]);

            $this->publishes([
                self::PACKAGE_PATH . "config/config.php"               => config_path("{$this->packageName}.php"),
            ], "{$this->packageName}:config");

            $this->publishes([
                self::PACKAGE_PATH . "public"                          => public_path("vendor/{$this->packageName}"),
            ], "{$this->packageName}:assets");

            $this->publishes([
                self::PACKAGE_PATH . "resources/views"                 => resource_path("views/vendor/{$this->packageName}"),
            ], "{$this->packageName}:views");

            $this->publishes([
                self::PACKAGE_PATH . "resources/lang"                  => $this->app->langPath("vendor/{$this->packageName}"),
            ], "{$this->packageName}:translations");
        }

        return $this;
    }

    protected function getPackageName(): string
    {
        return $this->packageName;
    }

}