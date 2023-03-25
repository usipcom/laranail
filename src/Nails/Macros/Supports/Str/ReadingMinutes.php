<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

use Illuminate\Support\Str;

class ReadingMinutes
{
    public function __invoke()
    {
        return function ($subject, $wordsPerMinute = 200) {
            return intval(ceil(Str::wordsCount(strip_tags($subject)) / $wordsPerMinute));
        };
    }
}
