<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CommissionService
{
    private const EU_COMMISSION_RATE = 0.01;
    private const N0N_EU_COMMISSION_RATE = 0.02;

    public function __construct(
        private CountryService      $countryService,
        private BinLookupService    $binLookupService,
        private TransactionsService $transactionsService,
    ) {}

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    final public function calculateCommissionAmountInEur(Transaction $transaction): ?float
    {
        // BIN of a given transaction
        $bin = $transaction->getBin();

        // get a commission rate depending on the BIN country
        $commissionRate = $this->getCommissionRateByBin($bin);

        // get transaction amount in EUR or converted into EUR if it's in different currency
        $transactionAmountInEur = $this->transactionsService->getTransactionAmountInEuro($transaction);

        // calculate the commission amount in EUR
        $commissionAmount =  $transactionAmountInEur * $commissionRate;

        // rounded commission amount
        return round($commissionAmount, 2, PHP_ROUND_HALF_DOWN);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    private function getCommissionRateByBin(string $bin): float
    {
        if ( empty($bin) ) {
            return self::N0N_EU_COMMISSION_RATE;
        }

        $countryAlpha2Code = $this->binLookupService->getCountryAlpha2CodeByBin($bin);

        if ( empty($countryAlpha2Code) ) {
            return self::N0N_EU_COMMISSION_RATE;
        }

        $isEuTransaction = $this->countryService->checkEuByAlpha2($countryAlpha2Code);

        return $isEuTransaction ? self::EU_COMMISSION_RATE : self::N0N_EU_COMMISSION_RATE;
    }
}