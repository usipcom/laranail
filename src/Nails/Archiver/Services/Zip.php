<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Archiver\Services;

use Simtabi\Laranail\Nails\Archiver\Abstracts\Extractor;
use ZipArchive;

class Zip extends Extractor
{
    /**
     * {@inheritdoc}
     */
    public function extract(string $pathToArchive, string $pathToDirectory): void
    {
        $archive = new ZipArchive;
        $archive->open($pathToArchive);
        $archive->extractTo($pathToDirectory);
        $archive->close();
    }
}
