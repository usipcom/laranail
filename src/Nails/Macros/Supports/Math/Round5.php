<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Math;

use Closure;

class Round5
{
    public function __invoke(): Closure
    {
        return function (float|int $number): int
        {
            $flooredMin = floor($number / 10) * 10;
            $number    -= $flooredMin;

            if ($number < 2.5) {
                return $flooredMin;
            } else if ($number >= 2.5 && $number < 7.5) {
                return $flooredMin + 5;
            } else {
                return $flooredMin + 10;
            }
        };
    }
}
