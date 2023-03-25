<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Supports\Transformers;

use Simtabi\Laranail\Nails\Blade\Contracts\BladeTransformerInterface;

class NumericTransformer implements BladeTransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function canTransform($value): bool
    {
        return is_int($value) || is_float($value);
    }

    /**
     * @param float|int $value
     *
     * @return float|int
     */
    public function transform($value)
    {
        return $value;
    }
}
