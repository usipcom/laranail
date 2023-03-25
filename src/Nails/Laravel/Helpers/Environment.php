<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Helpers;

use Illuminate\Support\Arr;

final class Environment
{

    public function isLocal(): bool
    {
        return ((bool) app()->isLocal() || (bool) app()->environment(['local', 'staging']) || (bool) $this->isDevelopment());
    }

    public function isDevelopment(): bool
    {
        return (bool) app()->environment(['development']);
    }

    public function isStaging(): bool
    {
        return (bool) app()->environment(['staging']);
    }

    public function isTesting(): bool
    {
        return (bool) app()->environment(['testing']);
    }

    public function isBeta(): bool
    {
        return (bool) app()->environment(['beta']);
    }

    public function isAlpha(): bool
    {
        return (bool) app()->environment(['alpha']);
    }

    public function isRelease(): bool
    {
        return (bool) app()->environment(['release']);
    }

    public function isProduction(): bool
    {
        return app()->isProduction();
    }

    public function isNonProduction(): bool
    {
        if ($this->isLocal() || $this->isStaging() || $this->isTesting() || $this->isBeta() || $this->isAlpha() || $this->isDevelopment()) {
            return true;
        }

        return false;
    }

    public function isEnvironment(string|array $environment): bool
    {
        return (bool) app()->environment(Arr::wrap($environment));
    }

    public function isRunningUnitTests(): bool
    {
        return (bool) app()->runningUnitTests();
    }

}