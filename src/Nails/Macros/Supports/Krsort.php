<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use Illuminate\Support\Collection;

class Krsort
{
    public function __invoke()
    {
        return function () {
            krsort($this->items);

            return new Collection($this->items);
        };
    }
}
