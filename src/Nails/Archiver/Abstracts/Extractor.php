<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Archiver\Abstracts;

abstract class Extractor
{
    /**
     * @param  string  $pathToArchive
     * @param  string  $pathToDirectory
     * @return void
     */
    abstract public function extract(string $pathToArchive, string $pathToDirectory): void;
}
