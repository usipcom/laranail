<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

class StripTags
{
    public function __invoke()
    {
        return function ($subject, $allowed_tags = null) {
            return strip_tags($subject, $allowed_tags);
        };
    }
}
