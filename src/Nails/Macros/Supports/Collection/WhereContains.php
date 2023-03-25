<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Collection;

use Illuminate\Support\Collection;

/**
 * Filter items that contain the given string.
 *
 * @param string $key
 * @param string $value
 * @param bool $casesensitive
 *
 * @mixin Collection
 *
 * @return mixed
 */

class WhereContains
{
    public function __invoke(): \Closure
    {
        return function (string $key, string $value, bool $casesensitive = true) {
            return $this->filter(function ($item) use ($key, $value, $casesensitive) {
                $haystack = data_get($item, $key);
                $haystack = ($casesensitive) ? $haystack : strtolower($haystack);
                $needle   = ($casesensitive) ? $value : strtolower($value);

                return str_contains($haystack, $needle);
            });
        };
    }
}
