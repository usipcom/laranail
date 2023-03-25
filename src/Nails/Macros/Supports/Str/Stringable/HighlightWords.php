<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class HighlightWords
{
    public function __invoke()
    {
        return function ($values)
        {
            return new static(Str::highlightWords($this->value, $values));
        };
    }
}
