<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Macros\Supports\Str;

use Closure;
use Illuminate\Support\Str;

class Bind
{
    public function __invoke(): Closure
    {
        return function (string $subject, array $bindings) {
            foreach ($bindings as $binding => $value) {
                $subject = Str::replace(":$binding", $value, $subject);
            }

            return $subject;
        };
    }
}
