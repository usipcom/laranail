<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

/**
 * Get a single item from the collection by index.
 *
 * @param mixed $index
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return mixed
 */
class At
{
    public function __invoke()
    {

        /*
         * Get a single item from the collection by index.
         *
         * @param mixed $index
         *
         * @mixin \Illuminate\Support\Collection
         *
         * @return mixed
         */
        return function ($index) {
            return $this->slice($index, 1)->first();
        };
    }
}
