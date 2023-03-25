<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class Matches
{
    public function __invoke()
    {
        return function ($regex) {
            $result = (new static(Str::matches($regex, $this->value)));

            return (bool)$result->value;
        };
    }
}
