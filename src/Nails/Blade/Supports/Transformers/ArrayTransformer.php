<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Supports\Transformers;

use Simtabi\Laranail\Nails\Blade\Contracts\BladeTransformerInterface;

class ArrayTransformer implements BladeTransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function canTransform($value): bool
    {
        return is_array($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function transform($value): string
    {
        return json_encode($value);
    }
}
