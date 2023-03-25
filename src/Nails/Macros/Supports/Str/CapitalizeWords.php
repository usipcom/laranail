<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

/**
 * Capitalize every word in a sentence.
 *
 * @param string $words
 *
 * @return string
 */
class CapitalizeWords
{
    public function __invoke(): \Closure
    {
        return function (string $words) {
            $words = collect(explode(" ", $words));

            return $words->transform(fn ($word) => ucfirst($word))->implode(" ");
        };
    }
}
