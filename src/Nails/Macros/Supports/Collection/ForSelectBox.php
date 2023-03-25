<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Collection;

use Illuminate\Support\Collection;
use Illuminate\Support\HigherOrderCollectionProxy;

/**
 * Sorts the items by its lowercase value keys them by the provided key.
 * @param string $key
 * @param string $value
 * @param bool $addempty
 * @property-read HigherOrderCollectionProxy $each *
 * @mixin Collection
 *
 * @return mixed
 *

 */

class ForSelectBox
{
    public function __invoke(): \Closure
    {
        return function (string $key, string $value, bool $addempty = true) {
            $sorted = $this->sortBy(fn ($item) => strtolower($item[$value]))->keyBy($key)->transform(fn ($item) => $item[$value]);

            return ($addempty) ? [''] + $sorted->toArray() : $sorted->toArray();
        };
    }
}
