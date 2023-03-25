<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasQuietSaving
{
    /**
     * Save the model quietly without dispatching any events.
     * 
     * @param  array  $options
     * @return Model
     */
    public function saveQuietly(array $options = []): Model
    {
        return static::withoutEvents(function () use ($options) {
            return $this->save($options);
        });
    }
}