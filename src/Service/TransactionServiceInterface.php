<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;

interface TransactionServiceInterface
{
    public function processTransactionsFile(string $filePath): ?array;
    public function getTransactionAmountInEuro(Transaction $transaction): ?float;
}