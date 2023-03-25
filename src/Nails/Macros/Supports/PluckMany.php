<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use ArrayAccess;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Get a Collection with only the specified keys.
 *
 * @param  array  $keys
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return Collection
 * @todo revisit later for updates
 */
class PluckMany
{
    public function __invoke()
    {
        return function ($keys): Collection {
            return $this->map(function ($item) use ($keys) {
                if ($item instanceof Collection) {
                    return $item->only($keys);
                }

                if (is_array($item)) {
                    return Arr::only($item, $keys);
                }

                if ($item instanceof ArrayAccess) {
                    return collect($keys)->mapWithKeys(function ($key) use ($item) {
                        return [$key => $item[$key]];
                    })->toArray();
                }

                return (object) Arr::only(get_object_vars($item), $keys);
            });
        };
    }
}
