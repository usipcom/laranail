<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

/**
 * Get the first item from the collection.
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return mixed
 */
class Head
{
    public function __invoke()
    {
        return function () {
            return $this->first();
        };
    }
}
