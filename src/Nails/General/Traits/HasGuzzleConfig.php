<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\General\Traits;

trait HasGuzzleConfig
{

    protected bool $persistConnection = true;
    protected int  $requestTimeout    = 60;
    protected int  $maxRetries        = 10;
    protected int  $cacheTtl          = 10;

    public function setPersistConnection(bool $persistConnection): static
    {
        $this->persistConnection = $persistConnection;

        return $this;
    }

    public function isPersistConnection(): bool
    {
        return (bool) env('GUZZLE_PERSIST_CONNECTION', true);
    }

    public function setRequestTimeout(int $requestTimeout): static
    {
        $this->requestTimeout = $requestTimeout;

        return $this;
    }

    public function getRequestTimeout(): int
    {
        return (int) env('GUZZLE_REQUEST_TIMEOUT', 60);
    }

    public function setMaxRetries(int $maxRetries): static
    {
        $this->maxRetries = $maxRetries;

        return $this;
    }

    public function getMaxRetries(): int
    {
        return (int) env('GUZZLE_MAX_RETRIES', 10);
    }

    public function setCacheTtl(int $cacheTtl): static
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }

    public function getCacheTtl(): int
    {
        return (int) env('GUZZLE_CACHE_TTL', 10);
    }

}
