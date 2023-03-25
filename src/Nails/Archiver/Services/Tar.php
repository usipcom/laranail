<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Archiver\Services;

use PharData;
use Simtabi\Laranail\Nails\Archiver\Abstracts\Extractor;

class Tar extends Extractor
{
    /**
     * {@inheritdoc}
     */
    public function extract(string $pathToArchive, string $pathToDirectory): void
    {
        $archive = new PharData($pathToArchive);
        $archive->extractTo($pathToDirectory);
    }
}
