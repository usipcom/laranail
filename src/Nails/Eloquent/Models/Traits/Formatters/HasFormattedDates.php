<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits\Formatters;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

trait HasFormattedDates
{

    /**
     * @var string
     */
    private $usDateFormat     = 'm/d/Y';

    /**
     * @var string
     */
    private $usTimeFormat     = 'h:i:s a';

    /**
     * @var string
     */
    private $usDateTimeFormat = 'm/d/Y h:i:s a';

    /*
    |--------------------------------------------------------------------------
    | Date formatters
    |--------------------------------------------------------------------------
    |
    | Formats created at, and updated at, and birthday
    |
    */

    public function formattedCreatedAt(bool $format = null)
    {
        return Carbon::parse(($attribute['created_at']  ?? $this->created_at))->format($format ?? $this->usDateTimeFormat);
    }

    public function formattedUpdatedAt(bool $format = null): string
    {
        return Carbon::parse(($attribute['updated_at']  ?? $this->updated_at))->format($format ?? $this->usDateTimeFormat);
    }

    public function dob() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return pheg()->time()->convertToSqlFormat($value, false, $this->usDateFormat);
            },
            set: function($value) {
                return [
                    'dob' => pheg()->time()->convertToSqlFormat($value, true, $this->usDateFormat),
                ];
            },
        );
    }

    public function formattedDob(string $format = 'F j, Y'): ?string
    {
        return !empty($this->dob) ? pheg()->time()->convertToSqlFormat($this->dob, false, $format) : null;
    }
}
