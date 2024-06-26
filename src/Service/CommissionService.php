<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class CommissionService
{
    // TODO depending on requirements consider to move it to app config or DB
    public final const EU_COMMISSION_RATE = 0.01;
    public final const N0N_EU_COMMISSION_RATE = 0.02;

    public function __construct(
        private readonly CountryServiceInterface     $countryService,
        private readonly BinLookupServiceInterface   $binLookupService,
        private readonly TransactionServiceInterface $transactionsService,
    ) {}

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
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
     * @throws ServerExceptionInterface
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