<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CommissionService;
use App\Service\TransactionsService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:commissions',
    description: 'Commissions Service',
    hidden: false,
    aliases: ['commissions:calculate']
)]
class CommissionsCommand extends Command
{

    // @todo use translation
    private const COMMAND_TITLE = 'Processing transactions...';
    private const INPUT_ARGUMENT_MESSAGE = 'Transactions file name: ';
    private const COMMAND_PROGRESS_NOTE = 'Reading file "%s"...';
    private const NOTHING_TO_PROCESS_WARNING_MESSAGE = 'Nothing to process!';

    public function __construct(
        private CommissionService   $commissionService,
        private TransactionsService $transactionsService,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'filePath',
            InputArgument::REQUIRED,
            self::INPUT_ARGUMENT_MESSAGE
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title(self::COMMAND_TITLE);

        $filePath = $input->getArgument('filePath');

        $io->note(
            sprintf(
                self::COMMAND_PROGRESS_NOTE,
                $filePath
            )
        );

        // array of Transaction entities (property objects)
        $transactions = $this->transactionsService->processTransactionsFile($filePath);

        if ( empty($transactions) ) {
            $io->warning(self::NOTHING_TO_PROCESS_WARNING_MESSAGE);

            return self::INVALID;
        }

        $io->progressStart(count($transactions));
        $io->newLine();

        $results = [];
        $errors = [];

        foreach ($transactions as $transaction) {
            $io->progressAdvance();

            try {
                //calculating commission for each transaction
                $results[] = $this->commissionService->calculateCommissionAmountInEur($transaction);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        // print the calculated commission for each transaction
        if ( ! empty($results) ) {
            // TODO experiment with $io->block styling
            $io->listing($results);
        }

        // print any possible exception message
        if ( ! empty($errors) ) {
            $io->error($errors);
            $io->listing($results);
        }

        $io->newLine();
        $io->success('');

        return Command::SUCCESS;
    }
}
