<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Datetime implements CastsAttributes
{

    /**
     * Default timezone
     * @var string
     */
    private string $timezone = 'UTC';

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @param string|null $timezone
     * @return Carbon|mixed
     */
    public function get($model, string $key, $value, array $attributes, ?string $timezone = null)
    {

        if (empty($timezone)) {
            $timezone = config('app.timezone', $this->timezone);
        }

        if (is_string($value)) {
            return Carbon::parse($value)->setTimezone($timezone);
        }

        return $value->copy()->setTimezone($timezone);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @param string|null $timezone
     * @return array|Carbon|string
     */
    public function set($model, string $key, $value, array $attributes, ?string $timezone = null)
    {
        if (empty($timezone)) {
            $timezone = config('app.timezone', $this->timezone);
        }

        if (is_string($value)) {
            return Carbon::parse($value)->setTimezone($timezone);
        }

        return $value->copy()->setTimezone($timezone);
    }

}