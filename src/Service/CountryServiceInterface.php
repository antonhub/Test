<?php

declare(strict_types=1);

namespace App\Service;

interface CountryServiceInterface
{
    public function checkEuByAlpha2(string $alpha2): bool;
}