<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class Initials
{
    public function __invoke()
    {
        return function ($number = 2) {
            return new static(Str::interpolate($this->value, $number));
        };
    }
}
