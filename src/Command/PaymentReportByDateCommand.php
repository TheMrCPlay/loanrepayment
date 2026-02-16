<?php

namespace App\Command;

use App\Infrastructure\Entity\PaymentEntity;
use App\Infrastructure\Repository\PaymentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'report',
    description: 'Show payments by date',
)]
class PaymentReportByDateCommand extends Command
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED,
                'Date in format YYYY-MM-DD'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getOption('date');

        try {
            $dateTime = new \DateTimeImmutable($date);
        } catch (\Exception $exception) {
            $output->writeln('<error>Invalid date format. Use YYYY-MM-DD.</error>');
            return 3;
        }

        $payments = $this->paymentRepository->findByDate($dateTime);

        if (!$payments) {
            $output->writeln('No payments found.');
            return Command::SUCCESS;
        }

        /** @var PaymentEntity $payment */
        foreach ($payments as $payment) {
            $output->writeln(sprintf(
                '%s | %s | %s | %s',
                $payment->getPaymentDate()->format('Y-m-d'),
                $payment->getExternalReference(),
                $payment->getAmount(),
                $payment->getState()
            ));
        }

        return Command::SUCCESS;
    }
}
