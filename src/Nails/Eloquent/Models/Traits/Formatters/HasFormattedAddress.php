<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits\Formatters;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasFormattedAddress
{
    /*
    |--------------------------------------------------------------------------
    | Address formatters
    |--------------------------------------------------------------------------
    |
    | Formats address fields
    |
    */

    public function addressName() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $value;
            },
            set: function($value) {
                return [
                    'name' => trim($value),
                ];
            },
        );
    }

    public function addressLineI() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $value;
            },
            set: function($value) {
                return [
                    'address' => trim($value),
                ];
            },
        );
    }

    public function street() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $value;
            },
            set: function($value) {
                return [
                    'street' => trim($value),
                ];
            },
        );
    }

    public function city() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $value;
            },
            set: function($value) {
                return [
                    'city' => ucwords(trim($value)),
                ];
            },
        );
    }

    public function state() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $value;
            },
            set: function($value) {
                return [
                    'state' => ucwords(trim($value)),
                ];
            },
        );
    }

    public function zipCode() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $value;
            },
            set: function($value) {
                return [
                    'zip_code' => strtoupper(strtolower(trim($value))),
                ];
            },
        );
    }

    public function countryName() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                $value = empty($value) ? $attribute['country'] : $value;
                if (!empty($value)) {
                    return pheg()->countries()->setCountryCode($value)->getCountryName();
                }
                return null;
            },
            set: function($value) {
                return null;
            },
        );
    }

    public function formattedAddress(bool $format = true)
    {
        return pheg()->helpers()->formatAddress($this, $format);
    }
}