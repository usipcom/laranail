<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Uuid\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Exception;
use Simtabi\Laranail\Nails\Uuid\Exceptions\MissingUuidColumnException;

trait HasUuid
{
    use HasUuidOptions;

    /**
     * Boot trait on the model.
     *
     * @return void
     * @throws Exception
     */
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            if ($model->isEnforceUuid()) {
                (new static())->hasColumnUuid($model);

                $model->setUuid($model->getGeneratedUuid($model));
            }
        });

        static::saving(function ($model) {
            if ($model->isEnforceUuid()) {
                (new static())->hasColumnUuid($model);

                $originalUuid = $model->getOriginal($model->getUuidColumnName());

                if ($originalUuid !== $model->getUuid()) {
                    $model->setUuid($originalUuid);
                }
            }
        });

    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->getUuidColumnName();
    }

    /**
     * Scope query by UUID.
     *
     * @param  string $uuid
     * @param  bool $firstOrFail
     * @return Model|Builder
     *
     * @throws ModelNotFoundException
     */
    public function scopeFindByUuid($query, $uuid, $firstOrFail = true)
    {
        $this->validateUuid($uuid);

        $queryBuilder = $query->where($this->getUuidColumnName(), $uuid);

        return $firstOrFail ? $queryBuilder->firstOrFail() : $queryBuilder;
    }

    /**
     * Check if the table have a column uuid.
     *
     * @param Model
     * @return void
     *
     * @throws MissingUuidColumnException
     */
    private function hasColumnUuid($model)
    {
        if (!$model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $model->getUuidColumnName())) {
            throw new MissingUuidColumnException("You don't have a '{$model->getUuidColumnName()}' column on '{$model->getTable()}' table.");
        }
    }

    /**
     * Check if uuid value is valid.
     *
     * @param  string $uuid
     * @return void
     *
     * @throws ModelNotFoundException
     */
    private function validateUuid($uuid)
    {
        if (! RamseyUuid::isValid($uuid)) {
            throw (new ModelNotFoundException())->setModel(get_class($this));
        }
    }
}
