<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;

interface BinLookupServiceInterface
{
    public function getCountryAlpha2CodeByBin(string $bin): ?string;
}