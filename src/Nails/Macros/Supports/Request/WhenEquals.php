<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Request;

use Closure;
use Illuminate\Http\Request;

class WhenEquals
{
    public function __invoke(): Closure
    {
        return function (string $key, mixed $value, callable $callback, ?callable $otherwise = null): Request
        {
            /** @type Request $this */
            $actualValue = $this->get($key);

            if ($actualValue == $value) {
                $callback($actualValue);
            } elseif ($otherwise) {
                $otherwise($actualValue);
            }

            return $this;
        };
    }
}
