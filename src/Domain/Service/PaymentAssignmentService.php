<?php

namespace App\Domain\Service;

use App\Domain\Assignment\AssignmentOutcome;
use App\Domain\Entity\Loan;
use App\Domain\Entity\Payment;
use App\Domain\Entity\PaymentOrder;
use App\Domain\Enum\LoanState;
use App\Domain\Enum\PaymentOrderState;
use App\Domain\Event\LoanFullyPaid;
use App\Domain\Event\PaymentReceived;
use App\Domain\Event\RefundCreated;
use Ramsey\Uuid\Uuid;

class PaymentAssignmentService
{
    public function assign(Payment $payment, Loan $loan): AssignmentOutcome
    {
        $remaining = $loan->getRemainingAmount();
        $isPaidAlready = $loan->isPaidAlready();

        $events = [
            new PaymentReceived(
                $payment->getId(),
                $loan->getCustomerId(),
                $loan->getId()
            )
        ];

        //When payment amount is less than matched load amount to pay
        //   - Mark payment as assigned
        //
        if (bccomp($payment->getAmount(), $remaining, 2) === -1) {
            $loan->applyPayment($payment->getAmount());
            $payment->markAssigned($payment->getAmount());

            return new AssignmentOutcome($events);
        }

        // When payment amount equals to matched loan amount to pay
        //   - Mark loan as paid
        //   - Mark payment as assigned
        //
        if (bccomp($payment->getAmount(), $remaining, 2) === 0) {
            $loan->applyPayment($payment->getAmount());
            $payment->markAssigned($payment->getAmount());

            if (!$isPaidAlready && $loan->getState() === LoanState::PAID) {
                $events[] = new LoanFullyPaid(
                    $loan->getId(),
                    $loan->getCustomerId()
                );
            }

            return new AssignmentOutcome($events);
        }

        //When payment amount is greater than matched loan amount to pay
        //   - Mark loan as paid
        //   - Mark payment as partially assigned
        //   - Create refund payment as separate entity called "Payment Order" with all necessary information
        //
        $loan->applyPayment($remaining);
        $payment->markPartiallyAssigned($remaining);

        $refundAmount = bcsub($payment->getAmount(), $remaining, 2);

        if (!$isPaidAlready && $loan->getState() === LoanState::PAID) {
            $events[] = new LoanFullyPaid(
                $loan->getId(),
                $loan->getCustomerId()
            );
        }

        $events[] = new RefundCreated();

        return new AssignmentOutcome(
            $events,
            new PaymentOrder(
                id: Uuid::uuid7(),
                paymentId: $payment->getId(),
                loanId: $loan->getId(),
                amount: $refundAmount,
                state: PaymentOrderState::PENDING,
                createdAt: new \DateTimeImmutable()
            )
        );
    }
}
