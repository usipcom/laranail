<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Collection;

use Illuminate\Support\Collection;

/**
 * Filter items that end with the given string.
 *
 * @param string $key
 * @param string $value
 * @param bool $casesensitive
 *
 * @mixin Collection
 *
 * @return mixed
 */

class WhereEndsWith
{
    public function __invoke(): \Closure
    {
        return function (string $key, string $value, bool $casesensitive = true) {
            return $this->filter(function ($item) use ($key, $value, $casesensitive) {
                $comparer = ($casesensitive) ? 'strncmp' : 'strncasecmp';

                return $comparer(strrev(data_get($item, $key)), strrev($value), strlen($value)) === 0;
            });
        };
    }
}
