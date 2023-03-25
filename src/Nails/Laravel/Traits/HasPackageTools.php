<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Traits;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;
use Simtabi\Laranail\Core\Exceptions\LaranailException;

/**
 * @mixin ServiceProvider
 */
trait HasPackageTools
{

    protected string $namespacedPackageName = 'cms';

    protected string|null $packageNamespace = null;

    public function setPackageNamespace(string $packageNamespace, string|null $configKey = 'laranail.module_namespaces'): self
    {
        $this->packageNamespace = ltrim(rtrim($packageNamespace, '/'), '/');

        if (!empty($configKey)) {
            $this->app['config']->set([$configKey .'.'. File::basename($this->getPackageRootPath()) => $packageNamespace]);
        }

        return $this;
    }

    protected function getPackageRootPath(string|null $path = null, string $moduleBaseDirectory = 'platform', string $moduleOrPluginDirectory = 'plugins', string $moduleServiceProviderNamespacePath = '/src/Providers'): string
    {
        $reflection = new ReflectionClass($this);
        $modulePath = str_replace($moduleServiceProviderNamespacePath, '', File::dirname($reflection->getFilename()));

        if (! Str::contains($modulePath, base_path($moduleBaseDirectory . '/' . $moduleOrPluginDirectory))) {
            $modulePath = base_path($moduleBaseDirectory . '/' . $this->getDashedPackageNamespace());
        }

        return $modulePath . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function loadAndPublishPackageConfigurations(array|string $fileNames): self
    {
        if (! is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        foreach ($fileNames as $fileName) {
            $this->mergeConfigFrom($this->getPackageConfigFilePath($fileName), $this->getDotedPackageNamespace() . '.' . $fileName);

            if ($this->app->runningInConsole()) {
                $this->publishes([
                    $this->getPackageConfigFilePath($fileName) => config_path($this->getDashedPackageNamespace() . '/' . $fileName . '.php'),
                ], $this->getPackageNamespacedName('config'));
            }
        }

        return $this;
    }

    protected function getPackageConfigFilePath(string $file): string
    {
        return $this->getPackageRootPath('config/' . $file . '.php');
    }

    protected function getDashedPackageNamespace(): string
    {
        return str_replace('.', '/', $this->packageNamespace);
    }

    protected function getDotedPackageNamespace(): string
    {
        return str_replace('/', '.', $this->packageNamespace);
    }

    public function loadPackageRoutes(array|string $fileNames = ['web']): self
    {
        if (! is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        foreach ($fileNames as $fileName) {
            $this->loadRoutesFrom($this->getPackageRoutes($fileName));
        }

        return $this;
    }

    protected function getPackageRoutes(string $file): string
    {
        return $this->getPackageRootPath('routes/' . $file . '.php');
    }

    public function loadAndPublishPackageViews(): self
    {
        $this->loadViewsFrom($this->getPackageViewsPath(), $this->getDashedPackageNamespace());

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getPackageViewsPath() => resource_path('views/vendor/' . $this->getDashedPackageNamespace())],
                $this->getPackageNamespacedName('views')
            );
        }

        return $this;
    }

    protected function getPackageViewsPath(string $path = '/resources/views/'): string
    {
        return $this->getPackageRootPath($path);
    }

    public function loadAndPublishPackageTranslations(string $path = '/resources/lang/'): self
    {
        $this->loadJsonTranslationsFrom($this->getPackageTranslationsPath($path));
        $this->loadTranslationsFrom($this->getPackageTranslationsPath($path), $this->getDashedPackageNamespace());

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getPackageTranslationsPath($path) => lang_path('vendor/' . $this->getDashedPackageNamespace())],
                $this->getPackageNamespacedName('lang')
            );
        }

        return $this;
    }

    protected function getPackageTranslationsPath(string $path = '/resources/lang/'): string
    {
        return $this->getPackageRootPath($path);
    }

    public function publishPackageAssets(string|null $path = null, string $assetsPath = 'vendor/core/'): self
    {
        if ($this->app->runningInConsole()) {
            if (empty($path)) {
                $path = $assetsPath . $this->getDashedPackageNamespace();
            }

            $this->publishes([$this->getPackageAssetsPath() => public_path($path)], $this->getPackageNamespacedName('public'));
        }

        return $this;
    }

    protected function getPackageAssetsPath(string $path = 'public'): string
    {
        return $this->getPackageRootPath($path);
    }

    public function loadPackageMigrations(): self
    {
        $this->loadMigrationsFrom($this->getPackageMigrationsPath());

        return $this;
    }

    protected function getPackageMigrationsPath(string $path = '/database/migrations/'): string
    {
        return $this->getPackageRootPath($path);
    }

    protected function getPackageFactoriesPath(string $path = '/database/factories/'): string
    {
        return $this->getPackageRootPath($path);
    }

    protected function loadPackageFactories(string|array $environments): static
    {
        if (! $this->app->environment($environments) && $this->app->runningInConsole()) {
            $this->app->make(Factory::class)->load($this->getPackageFactoriesPath());
        }

        return $this;
    }

    protected function getPackageSeedersPath(string $path = '/database/seeders/'): string
    {
        return $this->getPackageRootPath($path);
    }

    public function loadPackageHelpers(string $path = '/helpers'): static
    {

        $autoload = function (string $directory): void
        {
            foreach (File::glob($directory . '/*.php') as $helper) {
                File::requireOnce($helper);
            }
        };

        $autoload($this->getPackageRootPath($path));

        return $this;
    }

    protected function loadPackageConfigFromFile($request, string $folderName = null, $path = null): array
    {
        $loadFileData = function ($files, string $namespace, string $folderName = null, $path = null)
        {
            $loadFilePath = function ($fileName, $namespace, $folderName, $path = null)
            {
                $folderName = trim($folderName);
                $folderName = !empty($folderName) ? $folderName . '/' : '';
                $fileName   = trim($fileName);
                $path       = !empty($path)       ? $path             : 'platform';

                return base_path($path) . '/' . $namespace . '/' . $folderName . $fileName;
            };

            $folderName = !empty($folderName) ? $folderName                   : 'config';
            $files      = !is_array($files)   ? explode(',', $files) : $files;
            $data       = [];

            foreach ( $files as $file)
            {
                $file     = trim($file);
                $filePath = $loadFilePath($file, $namespace, $folderName, $path) . '.php';
                if (file_exists($filePath) && is_readable($filePath)) {
                    $data[$file] = require $filePath;
                }
            }
            return  $data;
        };
        return $loadFileData($request,  $this->getDashedPackageNamespace(), $folderName, $path);
    }

    protected function mergePackageConfigs(array $data, string $key): void
    {
        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, array_merge(
                $data, $config->get($key, [])
            ));
        }
    }

    /**
     * Register a view composer event.
     *
     * @param array|string $views
     * @param Closure|string $callback
     * @return void
     */
    protected function loadComposerViews(array|string $views, Closure|string $callback): void
    {
        View::composer($views, $callback);
    }

    /**
     * Namespaced group name used to group/classify config options
     *
     * @param string $descriptor
     * @param string $separator
     *
     * @return string
     * @throws LaranailException
     */
    private function getPackageNamespacedName(string $descriptor, string $separator = ':') : string
    {

        if (property_exists($this, 'namespacedPackageName') && (!empty($this->namespacedPackageName))) {
            $namespacedPackageName = $this->namespacedPackageName;
        } else {
            $namespacedPackageName = env('NAMESPACED_PACKAGE_NAME', 'cms');
        }

        if (empty($namespacedPackageName)) {
            throw new LaranailException('You must set a valid $this->namespacedPackageName property value or NAMESPACED_PACKAGE_NAME .env value');
        }

        return Str::slug(Str::lower($namespacedPackageName)) . $separator . Str::slug(Str::lower($descriptor));
    }

}
