<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class CapitalizeWords
{
    public function __invoke()
    {
        return function ()
        {
            return new static(Str::capitalizeWords($this->value));
        };
    }
}