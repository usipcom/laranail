<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

class Matches
{
    public function __invoke()
    {
        return function ($regex, $subject) {
            return (preg_match($regex, $subject) > 0);
        };
    }
}
