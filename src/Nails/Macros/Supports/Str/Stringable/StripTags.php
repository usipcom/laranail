<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str\Stringable;

use Illuminate\Support\Str;

class StripTags
{
    public function __invoke()
    {
        return function ($allowed_tags = null) {
            return new static(Str::stripTags($this->value, $allowed_tags));
        };
    }
}
