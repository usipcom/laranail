<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;


use Simtabi\Laranail\Nails\Macros\Exceptions\CollectionItemNotFound;

/**
 * Get the first item. Throws CollectionItemNotFound if the item was not found.
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return mixed
 */
class FirstOrFail
{
    public function __invoke()
    {
        return function () {
            if (! is_null($item = $this->first())) {
                return $item;
            }

            throw new CollectionItemNotFound('No items found in collection.');
        };
    }
}
