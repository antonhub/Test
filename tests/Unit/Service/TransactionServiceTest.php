<?php

namespace App\Tests\Unit\Service;

use App\Entity\Transaction;
use App\Service\CurrencyExchangeServiceInterface;
use App\Service\TransactionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionServiceTest extends KernelTestCase
{
    /**
     * @dataProvider transactionsDataProvider
     */
    public function testCommissionCalculation(Transaction $transaction, ?float $exchangeRate, ?float $expectedTransactionAmount): void
    {
        self::bootKernel([
            'environment' => 'test',
            'debug'       => false,
        ]);

        $container = static::getContainer();

        $currencyExchangeServiceMock = $this->createMock(CurrencyExchangeServiceInterface::class);

        // set mocked services into service container
        $container->set(CurrencyExchangeServiceInterface::class, $currencyExchangeServiceMock);

        if(
            empty($transaction->getAmount())
            || empty($transaction->getCurrency())
            || strlen($transaction->getCurrency()) !== 3
            || strtolower( $transaction->getCurrency() ) === 'eur'
        ) {
            $currencyExchangeServiceMock->expects(self::never())
                ->method('getEuroInverseExchangeRateByAlphaCode');
        } else {
            $currencyExchangeServiceMock->expects(self::once())
                ->method('getEuroInverseExchangeRateByAlphaCode')
                ->willReturn($exchangeRate)
            ;
        }

        $transactionService = $container->get(TransactionServiceInterface::class);

        // run the transaction conversion calculation
        $transactionAmount = $transactionService->getTransactionAmountInEuro($transaction);

        // assert the result
        $this->assertEquals($expectedTransactionAmount, $transactionAmount);
    }

    public function transactionsDataProvider(): array
    {
        return [
            // empty amount
            [
                $this->getTransaction(0., 'ee', 'bin23'),
                null,
                0.
            ],
            // invalid currency
            [
                $this->getTransaction(1., '', 'bin23'),
                null,
                null
            ],
            // invalid currency
            [
                $this->getTransaction(2., 'eu', 'bin23'),
                null,
                null
            ],
            // invalid currency
            [
                $this->getTransaction(3., 'eurr', 'bin23'),
                null,
                null
            ],
            // EUR currency should give the same output amount as a transaction amount
            [
                $this->getTransaction(1.23, 'eur', 'bin23'),
                null,
                1.23
            ],
            // custom currency unknown conversion rate
            [
                $this->getTransaction(2.23, 'uah', 'bin23'),
                null,
                null
            ],
            // custom currency and custom conversion rate
            [
                $this->getTransaction(3.23, 'uah', 'bin23'),
                0.05,
                0.05 * 3.23
            ],
        ];
    }

    private function getTransaction(?float $amount, string $currency, string $bin): Transaction
    {
        $transaction = new Transaction;
        $transaction->setBin($bin);
        $transaction->setAmount($amount);
        $transaction->setCurrency($currency);

        return $transaction;
    }
}