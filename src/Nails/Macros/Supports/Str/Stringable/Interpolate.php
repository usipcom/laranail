<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class Interpolate
{
    public function __invoke()
    {
        return function ($values) {
            return new static(Str::interpolate($this->value, $values));
        };
    }
}
