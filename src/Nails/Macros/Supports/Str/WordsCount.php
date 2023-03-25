<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

class WordsCount
{
    public function __invoke()
    {
        return function ($subject) {
            return strval(str_word_count(strip_tags($subject)));
        };
    }
}
