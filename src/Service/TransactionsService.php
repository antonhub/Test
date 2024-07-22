<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;

class TransactionsService implements TransactionServiceInterface
{
    public function __construct(
        private readonly CurrencyExchangeServiceInterface $currencyExchangeService,
    ){}

    final public function getTransactionAmountInEuro(Transaction $transaction): ?float
    {
        if (
            empty($transaction->getAmount())
        ) {
            return 0.;
        }

        // not valid currency
        if (
            empty($transaction->getCurrency())
            || strlen($transaction->getCurrency()) !== 3
        ) {
            return null;
        }

        // transaction currency is already EUR
        if ( strtolower( $transaction->getCurrency() ) === 'eur' ) {
            return $transaction->getAmount();
        }

        // calculate the currency exchange amount otherwise

        $currencyToEuroExchangeRate = $this->currencyExchangeService
            ->getEuroInverseExchangeRateByAlphaCode( $transaction->getCurrency() );

        if ($currencyToEuroExchangeRate === null) {
            return null;
        }

        return $transaction->getAmount() * $currencyToEuroExchangeRate;
    }
}