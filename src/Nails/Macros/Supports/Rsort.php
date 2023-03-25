<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use Illuminate\Support\Collection;

class Rsort
{
    public function __invoke()
    {
        return function () {
            rsort($this->items);

            return new Collection($this->items);
        };
    }
}