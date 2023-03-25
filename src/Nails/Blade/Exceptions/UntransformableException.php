<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Exceptions;

use Exception;

class UntransformableException extends Exception
{
    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function noTransformerFound($value)
    {
        return new static("There is no transformer to transform {$value} to JavaScript.");
    }

    /**
     * @param mixed $object
     *
     * @return static
     */
    public static function cannotTransformObject($object)
    {
        $objectString = print_r($object, true);

        return new static("Cannot transform object {$objectString} to JavaScript.");
    }
}