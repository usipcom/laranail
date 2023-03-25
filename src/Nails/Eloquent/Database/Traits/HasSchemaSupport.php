<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Database\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait HasSchemaSupport
{

    protected function dropColumnsFromTable(string $table, array|string $columns): void
    {
        $columns = is_string($columns) ? [$columns] : $columns;

        foreach ($columns as $column)
        {
            if (Schema::hasColumn($table, $column))
            {
                Schema::table($table, function (Blueprint $table) use ($column)
                {
                    $table->dropColumn($column);
                });
            }
        }
    }

    protected function dropTableIfExists(string|array $tables): void
    {
        $tables = is_string($tables) ? [$tables] : $tables;

        foreach ($tables as $table)
        {
            Schema::dropIfExists($table);
        }
    }

}