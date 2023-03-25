<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Request;

use Illuminate\Http\Request;

class IsEquals
{
    public function __invoke()
    {
        return function (string $key, mixed $value): bool
        {
            /** @type Request $this */
            return $this->get($key) === $value;
        };
    }
}