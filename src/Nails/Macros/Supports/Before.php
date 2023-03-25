<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

/**
 * Get the previous item from the collection.
 *
 * @param mixed $currentItem
 * @param mixed $fallback
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return mixed
 */
class Before
{
    public function __invoke()
    {
        return function ($currentItem, $fallback = null) {
            return $this->reverse()->after($currentItem, $fallback);
        };
    }
}
