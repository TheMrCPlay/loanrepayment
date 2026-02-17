<?php

namespace App\Command;

use App\Application\Dto\IncomingPaymentDto;
use App\Application\Notification\Message\FailedPaymentsReport;
use App\Application\Service\PaymentIngestionService;
use App\Domain\Exception\DuplicatePaymentException;
use App\Domain\Exception\InvalidDateException;
use App\Domain\Exception\NegativeAmountException;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'import',
    description: 'Import a CSV payment batch',
)]
class ImportBatchPaymentFromCsvCommand extends Command
{
    public function __construct(
        private readonly PaymentIngestionService $paymentIngestionService,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly SerializerInterface $serializer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the CSV file to import'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $highestExitCode = 0;
        $failures = [];

        $csvFile = Reader::from($input->getOption('file'));
        $csvFile->setHeaderOffset(0);

        foreach ($csvFile->getRecords() as $record) {
            try {
                $dto = $this->serializer->denormalize($record, IncomingPaymentDto::class);
                $this->paymentIngestionService->process($dto);
            } catch (\DomainException $exception) {
                $highestExitCode = max($highestExitCode, $this->domainExceptionToExitCode($exception));
                $failures[] = [
                    'record' => $record,
                    'error' => $exception->getMessage(),
                ];

                $this->logger->error(sprintf('%s severity: %d', $record->getRefId(), $highestExitCode));

                continue;
            } catch (\Exception $exception) {
                $highestExitCode = max($highestExitCode, 99);
                $failures[] = [
                    'record' => $record,
                    'error' => $exception->getMessage(),
                ];

                $this->logger->error(sprintf('%s severity: %d', $record->getRefId(), $highestExitCode));

                continue;
            }

            $this->logger->info($record->getRefId() . ' OK');
        }

        if ($failures) {
            // Notify Support Team about the failures
            $this->messageBus->dispatch(new FailedPaymentsReport($failures));
        }

        $this->logger->info('EXIT CODE: ' . $highestExitCode);

        return $highestExitCode;
    }

    private function domainExceptionToExitCode(\DomainException $exception): int
    {
        return match (true) {
            $exception instanceof DuplicatePaymentException => 1,
            $exception instanceof NegativeAmountException => 2,
            $exception instanceof InvalidDateException => 3,
            default => 99,
        };
    }
}
