<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Supports\Transformers;

use JsonSerializable;
use Simtabi\Laranail\Nails\Blade\Contracts\BladeTransformerInterface;
use Simtabi\Laranail\Nails\Blade\Exceptions\UntransformableException;
use StdClass;

class ObjectTransformer implements BladeTransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function canTransform($value): bool
    {
        return is_object($value);
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws UntransformableException
     */
    public function transform($value): string
    {
        if (method_exists($value, 'toJson')) {
            return $value->toJson();
        }

        if ($value instanceof JsonSerializable || $value instanceof StdClass) {
            return json_encode($value);
        }

        if (! method_exists($value, '__toString')) {
            throw UntransformableException::cannotTransformObject($value);
        }

        return "'{$value}'";
    }
}
