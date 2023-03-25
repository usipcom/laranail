<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class WordsCount
{
    public function __invoke()
    {
        return function ()
        {
            return new static(Str::wordCount($this->value));
        };
    }
}
