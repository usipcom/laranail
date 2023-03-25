<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits\Formatters;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasUserFormatting
{

    /*
    |--------------------------------------------------------------------------
    | Name & email formatters
    |--------------------------------------------------------------------------
    |
    | Formats names, usernames and emails
    |
    */

    public function firstName() :Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => strtolower($value),
        );
    }

    public function lastName() :Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => strtolower($value),
        );
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->formattedFullName(false),
            set: fn ($value) => $value,
        );
    }

    public function formattedFullName(bool $substitute = true): bool|string|null
    {
        return pheg()->name()->make($this, $substitute);
    }

    public function nameOrEmail()
    {
        return trim($this->name) ?: $this->email;
    }

    public function name2Initials(): string
    {
        return strtoupper(pheg()->name()->makeInitials($this->nameOrEmail()));
    }

    public function formattedUsername($format = true): bool|string
    {
        if (!empty($this->username)) {
            return $format ? '@' . $this->username : $this->username;
        }
        return false;
    }

    public function email2username()
    {
        return pheg()->name()->usernameFromEmail($this->email);
    }

    public function username() :Attribute
    {
        return Attribute::make(
            get: function($value, $attribute) {
                return $attribute['username'] ?? $value;
            },
            set: fn ($value) => strtolower($value),
        );
    }

    public function email() :Attribute
    {
        return Attribute::make(
            get: function($value, $attribute) {
                $email = strtolower(trim($attribute['email'] ?? $value));
                return  !empty($email) ? $email : null;
            },
            set: fn ($value) => strtolower($value),
        );
    }

    public function phone() :Attribute
    {
        return Attribute::make(
            get: function($value, $attribute) {
                return  $attribute['phone'] ?? $value;
            },
            set: fn ($value) => strtolower($value),
        );
    }

    public function occupation(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return !empty($value) ? pheg()->supports()->getOccupationTypes($value) : null;
            },
            set: fn ($value) => strtolower($value),
        );
    }

}
