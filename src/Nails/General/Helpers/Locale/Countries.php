<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\General\Helpers\Locale;

use PragmaRX\Countries\Package\Countries as PragmaRXCountries;
use PragmaRX\Countries\Package\Services\Config as PragmaRXConfig;
use PragmaRX\Countries\Package\Data\Repository as PragmaRXRepository;
use PragmaRX\Countries\Package\Services\Cache\Service as PragmaRXCache;
use PragmaRX\Countries\Package\Services\Helper as PragmaRXHelper;
use PragmaRX\Countries\Package\Services\Hydrator as PragmaRXHydrator;

class Countries
{

    private PragmaRXCountries $countries;

    public function __construct(
        $config = null,
        Cache $cache = null,
        Helper $helper = null,
        Hydrator $hydrator = null,
        Repository $repository = null
    )
    {
        $config          = (!empty($config) && is_array($config)) ? $config : [
            'hydrate' => [
                'elements' => [
                    'currencies' => true,
                    'timezones'  => true,
                    'flag'       => true,
                ],
            ],
        ];
        $this->countries = new PragmaRXCountries(new PragmaRXConfig($config), $cache, $helper, $hydrator, $repository);
    }

    public static function invoke(): self
    {
        return new self();
    }

    public function getAllCountries($iso3 = true, bool $compactName = true)
    {
        return app(PragmaRXCountries::class)
            ->all()
            ->map(function ($country) use ($iso3, $compactName) {
                $commonName  = $country->name->common;
                $languages   = $country->languages ?? collect();
                $language    = $languages->keys()->first() ?? null;
                $nativeNames = $country->name->native ?? null;

                if (filled($language) && filled($nativeNames) && filled($nativeNames[$language]) ?? null) {
                    $native = $nativeNames[$language]['common'] ?? null;
                }

                if (blank($native ?? null) && filled($nativeNames)) {
                    $native = $nativeNames->first()['common'] ?? null;
                }

                if (!$compactName) {
                    $native = $native ?? $commonName;

                    if ($native !== $commonName && filled($native)) {
                        $native = "$native ($commonName)";
                    }

                    $name = $native;
                }else{
                    $name = $commonName;
                }

                return [($iso3 ? $country->cca3 : $country->cca2) => $name];
            })
            ->values()
            ->toArray();
    }
    
}
