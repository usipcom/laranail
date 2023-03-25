<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use Illuminate\Support\Collection;

/**
 * Transform a collection into an an array with pairs.
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return \Illuminate\Support\Collection
 */
class ToPairs
{
    public function __invoke()
    {
        return function (): Collection {
            return $this->keys()->map(function ($key) {
                return [$key, $this->items[$key]];
            });
        };
    }
}
