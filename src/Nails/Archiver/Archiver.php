<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Archiver;

use Simtabi\Laranail\Nails\Archiver\Services\Tar;
use Simtabi\Laranail\Nails\Archiver\Services\TarGz;
use Simtabi\Laranail\Nails\Archiver\Services\Zip;

class Archiver
{

    public function __construct()
    {
    }

    public function tar(): Tar
    {
        return new Tar();
    }

    public function tarGz(): TarGz
    {
        return new TarGz();
    }

    public function zip(): Zip
    {
        return new Zip();
    }

}