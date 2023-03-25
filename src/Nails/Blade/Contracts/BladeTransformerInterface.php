<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Contracts;

interface BladeTransformerInterface
{
    public function canTransform($value): bool;

    public function transform($value);
}