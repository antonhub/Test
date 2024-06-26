<?php

namespace App\Tests\Unit\Service;

use App\Entity\Transaction;
use App\Service\BinLookupServiceInterface;
use App\Service\CommissionService;
use App\Service\CountryServiceInterface;
use App\Service\TransactionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommissionServiceTest extends KernelTestCase
{
    /**
     * @dataProvider allCommissionDataProvider
     */
    public function testCommissionCalculation(?string $alpha2, ?bool $isEu, ?float $transactionAmount, ?float $expectedCommissionAmount): void
    {
        self::bootKernel([
            'environment' => 'test',
            'debug'       => false,
        ]);

        $container = static::getContainer();

        $binLookupServiceMock = $this->createMock(BinLookupServiceInterface::class);
        $countryServiceMock = $this->createMock(CountryServiceInterface::class);
        $transactionServiceMock = $this->createMock(TransactionServiceInterface::class);

        // set mocked services into service container
        $container->set(BinLookupServiceInterface::class, $binLookupServiceMock);
        $container->set(CountryServiceInterface::class, $countryServiceMock);
        $container->set(TransactionServiceInterface::class, $transactionServiceMock);

        $binLookupServiceMock->expects(self::once())
            ->method('getCountryAlpha2CodeByBin')
            ->willReturn($alpha2)
        ;

        if (! empty($alpha2)) {
            $countryServiceMock->expects(self::once())
                ->method('checkEuByAlpha2')
                ->willReturn($isEu)
            ;
        } else {
            $countryServiceMock->expects(self::never())
                ->method('checkEuByAlpha2')
            ;
        }

        $transactionServiceMock->expects(self::once())
            ->method('getTransactionAmountInEuro')
            ->willReturn($transactionAmount)
        ;

        $commissionService = $container->get(CommissionService::class);

        $transaction = $this->getTransaction();

        // run the commission culculation
        $commissionAmount = $commissionService->calculateCommissionAmountInEur($transaction);

        // assert the result
        $this->assertEquals($expectedCommissionAmount, $commissionAmount);
    }

    public function allCommissionDataProvider(): array
    {
//        $nonEuRate = CommissionService::N0N_EU_COMMISSION_RATE;
//        $euRate = CommissionService::EU_COMMISSION_RATE;

        return [
            ['NL', true, 11.1, $this->roundCommission(11.1, true)],
            ['UA', false, 22.22, $this->roundCommission(22.22)],
            ['ru', false, 33.333, $this->roundCommission(33.333)],
            [null, false, 44.4444, $this->roundCommission(44.4444)]
        ];
    }

    private function roundCommission(float $amount, bool $isEu = false): float
    {
        $rate = $isEu === true ? CommissionService::EU_COMMISSION_RATE : CommissionService::N0N_EU_COMMISSION_RATE;

        return round( $rate * $amount, 2, PHP_ROUND_HALF_DOWN );
    }

    private function getTransaction(): Transaction
    {
        $transaction = new Transaction;
        $transaction->setBin('1234567');
        $transaction->setAmount(23.4);
        $transaction->setCurrency('EUR');

        return $transaction;
    }
}