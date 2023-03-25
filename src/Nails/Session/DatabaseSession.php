<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Session;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class DatabaseSession extends Model
{

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should auto increment.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model has no timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * User model class to be used
     *
     * @var string
     */
    public $userModelClass;

    /**
     * Use parent constructor and set table according to config file
     */
    public function __construct($userModelClass, $sessionTable)
    {
        parent::__construct();
        $this->userModelClass = $userModelClass;
        $this->table          = $sessionTable;
    }

    /**
     * Get Unserialized Payload (base64 decoded too)
     *
     * @return array
     */
    public function getUnserializedPayloadAttribute() : array
    {
        return unserialize(base64_decode($this->payload));
    }

    /**
     * Manually set Payload (base64 encoded / serialized)
     *
     * @return void
     */
    public function setPayload(string $payload)
    {
        $this->payload = serialize(base64_encode($payload));

        $this->save();
    }

    /**
     * User Relationship
     *
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo($this->userModelClass);
    }

    /**
     * Last Activity Carbon instance
     *
     * @return Carbon
     */
    public function getLastActivityAtAttribute() : Carbon
    {
        return Carbon::createFromTimestamp($this->last_activity);
    }
}