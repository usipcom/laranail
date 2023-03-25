<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Observers;

use Illuminate\Database\Eloquent\Model;

abstract class BaseObserver
{

    /**
     * Triggered before a record is created.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function creating(Model $model): void;

    /**
     * Triggered after a record has been created.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function created(Model $model): void;

    /**
     * Triggered before a record is updated.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function updating(Model $model): void;

    /**
     * Triggered after a record has been updated.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function updated(Model $model): void;

    /**
     * Triggered before a record is deleted or soft-deleted.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function deleting(Model $model): void;

    /**
     * Triggered after a record has been deleted or soft-deleted.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function deleted(Model $model): void;

    /**
     * Triggered when retrieving a record.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function retrieved(Model $model): void;

    /**
     * Triggered before a record is saved (either created or updated).
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function saving(Model $model): void;

    /**
     * Triggered after a record has been updated (either created or updated).
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function saved(Model $model): void;

    /**
     * Triggered before a soft-deleted record is going restored.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function restoring(Model $model): void;

    /**
     * Triggered after a soft-deleted record has been restored.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function restored(Model $model): void;

    /**
     * Triggered when replicating a record.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function replicating(Model $model): void;

    /**
     * Triggered before a record is deleted forcefully.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function forceDeleting(Model $model): void;

    /**
     * Triggered after a record is deleted forcefully.
     *
     * @param Model $model
     *
     * @return void
     */
    abstract public function forceDeleted(Model $model): void;

}
