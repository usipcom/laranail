<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits;

use Simtabi\Laranail\Nails\Eloquent\Models\Exceptions\ImmutableDataException;

trait HasImmutabilility
{
    /**
     * Hook into the boot method to catch updating and deleting events.
     *
     * @return void
     * @throws ImmutableDataException
     */
    public static function bootHasImmutabilility()
    {
        static::updating(function ($model) {
            if ($model->isImmutable()) {
                throw new ImmutableDataException($model);
            }
        });

        static::deleting(function ($model) {
            if ($model->isImmutable()) {
                throw new ImmutableDataException($model);
            }
        });
    }

    /**
     * Determine if this model is in an immutable state (default to always immutable)
     *
     * @return boolean
     */
    public function isImmutable()
    {
        return true;
    }
}
