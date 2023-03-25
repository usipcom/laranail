<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Math;

use Simtabi\Pheg\Toolbox\Distance\Entities\Calculator;
use Simtabi\Pheg\Toolbox\Distance\Entities\LatLong;
use Closure;

class DistanceBetween
{
    public function __invoke(): Closure
    {
        return function (float $fromLat, float $fromLng, float $toLat, float $toLng): float
        {
            $from     = new LatLong($fromLat, $fromLng);
            $to       = new LatLong($toLat, $toLng);
            $distance = new Calculator($from, $to);

            return $distance->get()->asMiles();
        };
    }
}
