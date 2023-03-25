<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

/**
 * Get the array of values of a given key.
 *
 * @param  string|array  $value
 * @param  string|null  $key
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return array
 */
class PluckToArray
{
    public function __invoke()
    {
        return function ($value, $key = null): array {
            return $this->pluck($value, $key)->toArray();
        };
    }
}
