<?php

namespace App\Tests\Unit\Service;

use App\Service\CountryService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CountryServiceTest extends KernelTestCase
{
    /**
     * @dataProvider countryAlpha2Provider
     */
    public function testCheckEuCountry(string $alpha2, bool $expected): void
    {
        self::bootKernel([
            'environment' => 'test',
            'debug'       => false,
        ]);

        $container = static::getContainer();

        // get CountryService from the service container
        $countryService = $container->get(CountryService::class);

        // run the "EU country" check
        $isEu = $countryService->checkEuByAlpha2($alpha2);

        // assert the result
        $this->assertEquals($expected, $isEu);
    }

    public function testCheckEuCountryWithInvalidArgument(): void
    {
        self::bootKernel([
            'environment' => 'test',
            'debug'       => false,
        ]);

        $container = static::getContainer();

        // get CountryService from the service container
        $countryService = $container->get(CountryService::class);


        $this->expectException(\TypeError::class);

        // run the "EU country" check with invalid argument type
        $countryService->checkEuByAlpha2([123]);
    }

    public function countryAlpha2Provider(): array
    {
        return [
            ['NL', true],
            ['nl', true],
            ['Nl', true],
            ['nL', true],
            ['UA', false],
            ['uA', false],
            ['RU', false],
            ['ru', false],
            ['', false],
            ['not_valid', false],
        ];
    }
}