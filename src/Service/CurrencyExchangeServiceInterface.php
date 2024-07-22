<?php

declare(strict_types=1);

namespace App\Service;

interface CurrencyExchangeServiceInterface
{
    public function isServiceAvailable(): bool;

    public function getEuroInverseExchangeRateByAlphaCode(string $currencyAlphaCode): ?float;
}
