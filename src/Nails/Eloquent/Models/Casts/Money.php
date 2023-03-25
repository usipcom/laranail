<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Money implements CastsAttributes
{

    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return float
     */
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return $value;
        }
        
        return round($value * 0.01, 2);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return floatval($value) * 100;
    }

}
