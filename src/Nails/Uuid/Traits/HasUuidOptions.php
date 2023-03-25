<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Uuid\Traits;

use Simtabi\Pheg\Core\Exceptions\InvalidUuidVersionException;
use Illuminate\Support\Facades\App;
use Exception;

trait HasUuidOptions
{

    /**
     * Default Uuid field name
     *
     * @var string
     */
    // protected $uuidColumnName  = 'id';

    /**
     * Default Uuid version to be used
     * Available 1,3,4 or 5
     *
     * @var string
     */
    // protected $uuidVersion     = 4;

    /**
     * Default Uuid string
     * Needed when $uuidVersion is "3 or 5"
     *
     * @var string
     */
    // protected $uuidString      = '';

    /**
     * Default development/testing environments
     *
     * @var string[]
     */
    // protected $devEnvironments = ['local', 'testing'];

    /**
     * Enable testing Uuid type
     *
     * @var bool
     */
    // protected $enableUuidTesting   = false;

    /**
     * Enable testing Uuid type
     *
     * @var bool
     */
    // protected $useTimeOrderedUuid  = false;

    /**
     * Enforce UUID usage
     *
     * @var bool
     */
    // protected $enforceUuid  = false;


    /**
     * Disable auto incrementing option when using Uuid
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Change auto incrementing key type to allow for Uuid
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Get the column name for the "uuid".
     *
     * @return string
     */
    public function getUuidColumnName()
    {
        return property_exists($this, 'uuidColumnName') ? $this->uuidColumnName : 'id';
    }

    /**
     * Get "uuid" version or default to 4.
     *
     * @return int
     */
    public function getUuidVersion()
    {
        return property_exists($this, 'uuidVersion') ? $this->uuidVersion : 4;
    }

    /**
     * Get string to generate uuid version 3 and 5.
     *
     * @return string
     */
    public function getUuidString()
    {
        return property_exists($this, 'uuidString') ? $this->uuidString : '';
    }

    /**
     * @return string[]
     */
    public function getDevEnvironments()
    {
        return property_exists($this, 'devEnvironments') ? $this->devEnvironments : ['local', 'testing'];
    }

    /**
     * @return bool
     */
    public function isEnableUuidTesting()
    {
        return property_exists($this, 'enableUuidTesting') ? $this->enableUuidTesting : false;
    }

    /**
     * Checks to see if "Time Ordered" UUIDs have been specified
     *
     * @return bool
     */
    public function isUseTimeOrderedUuid(): bool
    {
        return property_exists($this, 'useTimeOrderedUuid') ? $this->useTimeOrderedUuid : false;
    }

    /**
     * Checks to see if we have to use UUID
     *
     * @return bool
     */
    public function isEnforceUuid(): bool
    {
        return property_exists($this, 'enforceUuid') ? $this->enforceUuid : true;
    }

    /**
     * Set the uuid value.
     *
     * @param  string  $value
     * @return static
     */
    public function setUuid($value)
    {
        if (! empty($this->getUuidColumnName())) {
            $this->{$this->getUuidColumnName()} = $value;
        }

        return $this;
    }

    /**
     * Get the uuid value.
     *
     * @return string|null
     * @throws Exception
     */
    public function getUuid()
    {
        if (! empty($this->getUuidColumnName())) {
            return (string) $this->{$this->getUuidColumnName()};
        }

        throw new Exception("UUID value for [{$this->getUuidColumnName()}] is missing.");
    }

    /**
     * Gets generated Uuid
     *
     * @return string
     * @throws InvalidUuidVersionException
     */
    public function getGeneratedUuid($model = null){
        if ($this->isEnableUuidTesting() && App::environment($this->getDevEnvironments()) && (!empty($model))) {
            return pheg()->uuid()->generateReadableForTesting($model);
        } else {
            return ($this->isUseTimeOrderedUuid()) ? pheg()->uuid()->generateOrdered() : pheg()->uuid()->generate();
        }
    }


    /**
     *  Scoping method to search for a record via the UUID
     *
     * @param $query
     * @param $uuid
     *
     * @return mixed
     */
    public function scopeByUuid($query, $uuid)
    {
        return $query->where($this->getUuidFieldName(), $uuid);
    }

    /**
     * Static call to search for a record via the UUID
     *
     * @param $uuid
     *
     * @return mixed
     */
    public static function findByUuid($uuid)
    {
        return static::byUuid($uuid)->first();
    }

}
