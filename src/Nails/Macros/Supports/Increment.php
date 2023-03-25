<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports;

use Illuminate\Support\Arr;

class Increment
{
    public function __invoke()
    {
        return function ($key, $amount) {
            if (Arr::has($this->items, $key)) {
                $amount += Arr::get($this->items, $key);
            }

            Arr::set($this->items, $key, $amount);

            return $this;
        };
    }
}
