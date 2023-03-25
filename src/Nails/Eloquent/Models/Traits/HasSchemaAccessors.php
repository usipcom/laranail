<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Checks if the underlying model has a given column
 *
 * https://laracasts.com/discuss/channels/eloquent/test-attributescolumns-existence
 */

trait HasSchemaAccessors
{

    public static $schemaInstance;
    public static $schemaColumnNames;
    public static $schemaTableName;

    /**
     * @return Model
     * Returns singleton of model
     */
    protected static function schemaInstance(): Model
    {
        if(empty(static::$schemaInstance)) {
            static::$schemaInstance = new static;
        }

        return static::$schemaInstance;
    }

    /**
     * @return string
     * Returns the table name for a given model
     */
    public static function getSchemaTableName(): string
    {
        if(empty(static::$schemaTableName)) {
            static::$schemaTableName = static::schemaInstance()->getTable();
        }

        return static::$schemaTableName;
    }

    /**
     * @return array
     * Fetches column names from the database schema
     */
    public static function getSchemaColumnNames(): array
    {
        if(empty(static::$schemaColumnNames)) {
            static::$schemaColumnNames = Schema::getColumnListing(static::getSchemaTableName());
        }

        return static::$schemaColumnNames;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function schemaHasColumn($name): bool
    {
        return in_array( $name, static::getSchemaColumnNames() );
    }

}