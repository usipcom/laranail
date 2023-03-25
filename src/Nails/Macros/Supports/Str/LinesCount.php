<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

class LinesCount
{
    public function __invoke()
    {
        return function ($subject) {
            $lines = preg_split('/\n|\r/', $subject);

            return count($lines);
        };
    }
}
