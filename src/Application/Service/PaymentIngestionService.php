<?php

namespace App\Application\Service;

use App\Application\Dto\IncomingPaymentDto;
use App\Application\Notification\NotificationDispatcher;
use App\Domain\Assignment\AssignmentOutcome;
use App\Domain\Entity\Payment;
use App\Domain\Exception\DuplicatePaymentException;
use App\Domain\Exception\InvalidDateException;
use App\Domain\Exception\NegativeAmountException;
use App\Domain\Service\PaymentAssignmentService;
use App\Infrastructure\Repository\LoanRepository;
use App\Infrastructure\Repository\PaymentOrderRepository;
use App\Infrastructure\Repository\PaymentRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class PaymentIngestionService
{

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly PaymentAssignmentService $paymentAssignmentService,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentOrderRepository $paymentOrderRepository,
        private readonly NotificationDispatcher $notificationDispatcher
    ) {
    }

    public function process(IncomingPaymentDto $incomingPaymentDto)
    {
        if (bccomp($incomingPaymentDto->getAmount(), '0.00') <= 0) {
            throw new NegativeAmountException();
        }

        $paymentDate = $this->parseDateOrThrow($incomingPaymentDto->getPaymentDate());

        if ($this->paymentRepository->existsByExternalReference($incomingPaymentDto->getRefId())) {
            throw new DuplicatePaymentException();
        }

        $loanReference = $incomingPaymentDto->extractLoanRef();

        $payment = Payment::createReceived(
            id: $this->paymentRepository->nextId(),
            externalReference: $incomingPaymentDto->getRefId(),
            loanReference: $loanReference,
            paymentDate: $paymentDate,
            amount: $incomingPaymentDto->getAmount(),
        );

        $this->entityManager->beginTransaction();

        try {
            $this->paymentRepository->save($payment);

            $loan = $this->loanRepository->getDomainEntityByReferenceId($loanReference);

            $result = $this->paymentAssignmentService->assign($payment, $loan);

            $this->loanRepository->save($loan);
            $this->paymentRepository->save($payment);

            if ($result->hasRefundOrder()) {
                $this->paymentOrderRepository->save($result->getRefundOrder());
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (UniqueConstraintViolationException) {
            $this->entityManager->rollback();
            throw new DuplicatePaymentException();
        } catch(\Throwable $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }

        // Send notification to external system about payment processing result (success, failure, refund, etc.)
        $this->notificationDispatcher->dispatch($result->getEvents());
    }

    private function parseDateOrThrow(string $date): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            throw new InvalidDateException();
        }
    }
}
