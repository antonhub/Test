<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Psr\Cache\InvalidArgumentException;

class CountryService
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly CacheService      $cacheService,
    ) {}

    /**
     * Checking whether a given alpha2 country code is part of EU or not
     *
     * @return bool
     */
    public function checkEuByAlpha2(string $alpha2): bool
    {
        $alpha2 = strtolower($alpha2);

        return in_array($alpha2, $this->getEuCountriesAlpha2CodesLowercase());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getEuCountriesAlpha2CodesLowercase(): array
    {
        $cacheKey = 'euCountriesAlpha2Codes';

        $cachedCountries = $this->cacheService->get($cacheKey);

        // return cached countries if available
        if ( $this->cacheService->get($cacheKey) !== null ) {
            return $cachedCountries;
        }

        $countries = $this->countryRepository
            ->findBy( ['isEu' => true] );

        $euAlpha2Codes = [];

        /**
         * Populating EU alpa2 codes array
         * @var $country Country
         */
        foreach ($countries as $country) {
            if ( $country->isEu() !== true ) {
                continue;
            }

            $euAlpha2Codes[] = strtolower( $country->getAlpha2() );
        }

        // set cache
        $this->cacheService->set($cacheKey, $euAlpha2Codes);

        return $euAlpha2Codes;
    }
}