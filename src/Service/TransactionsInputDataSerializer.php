<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

class TransactionsInputDataSerializer implements TransactionsInputDataSerializerInterface
{
    /**
     * Encoding type of the transactions file
     */
    private const ENCODING_TYPE = 'json';
    
    public function __construct(
        private readonly SerializerInterface              $serializer,
        private readonly Filesystem                       $filesystem,
    ){}

    /**
     * Processing a given transactions file name, parsing it and returning the collection of transaction entities
     *
     * @param string $filePath
     * @return ?array
     * @todo implement and use TransactionCollection instead of array of Objects
     *
     */
    final public function processTransactionsFile(string $filePath): ?array
    {
        $fileContent = $this->readTransactionsFromFile($filePath);

        if ( $fileContent === null ) {
            return null;
        }

        $transactionsJsonArr = $this->convertTransactionsIntoArray($fileContent);

        if ( empty($transactionsJsonArr) ) {
            return null;
        }

        $transactions = [];

        foreach ($transactionsJsonArr as $transactionJson) {
            if ( json_validate($transactionJson) === false ) {
                continue;
            }

            $transaction = $this->deserializeTransaction($transactionJson);

            if ($transaction === null) {
                continue;
            }

            $transactions[] = $transaction;
        }

        return $transactions;
    }

    /**
     * @todo use fopen+generator to not kill the server in case we may have real big files
     * @param string $filePath
     * @return string|null
     */
    private function readTransactionsFromFile(string $filePath): ?string
    {
        if ( $this->filesystem->exists($filePath) === false ) {
            return null;
        }

        try {
            $fileContent = $this->filesystem->readFile($filePath);

            if ( empty($fileContent) ) {
                return null;
            }

            return $fileContent;
        } catch (IOException) {
            // @todo log an error
            return null;
        }
    }

    private function convertTransactionsIntoArray(string $transactionsContent): ?array
    {
        return preg_split("/\r\n|\n|\r/", $transactionsContent);
    }

    private function deserializeTransaction(string $transactionJson): ?Transaction
    {
        if ( json_validate($transactionJson) === false ) {
            return null;
        }

        return $this->serializer->deserialize(
            $transactionJson,
            Transaction::class,
            self::ENCODING_TYPE,
            ['disable_type_enforcement' => true]
        );
    }
}