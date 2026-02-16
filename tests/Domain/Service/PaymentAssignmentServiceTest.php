<?php

namespace App\Tests\Domain\Service;

use App\Domain\Assignment\AssignmentOutcome;
use App\Domain\Entity\Loan;
use App\Domain\Entity\Payment;
use App\Domain\Entity\PaymentOrder;
use App\Domain\Enum\LoanState;
use App\Domain\Enum\PaymentOrderState;
use App\Domain\Enum\PaymentState;
use App\Domain\Event\LoanFullyPaid;
use App\Domain\Event\PaymentReceived;
use App\Domain\Event\RefundCreated;
use App\Domain\Service\PaymentAssignmentService;
use PHPUnit\Framework\TestCase;

final class PaymentAssignmentServiceTest extends TestCase
{
    private PaymentAssignmentService $svc;

    protected function setUp(): void
    {
        $this->svc = new PaymentAssignmentService();
    }

    public function testAssign_paymentLessThanRemaining_marksAssigned_andDoesNotCreateRefund_andDoesNotCloseLoan(): void
    {
        $loan = $this->makeLoan(
            amountToPay: '120.00',
            paidAmount: '0.00',
            state: LoanState::ACTIVE
        );

        $payment = $this->makePayment(amount: '100.00');

        $outcome = $this->svc->assign($payment, $loan);

        self::assertInstanceOf(AssignmentOutcome::class, $outcome);

        // Loan updated correctly
        self::assertSame('100.00', $loan->getPaidAmount());
        self::assertSame(LoanState::ACTIVE, $loan->getState());

        // Payment status
        self::assertSame(PaymentState::ASSIGNED, $payment->getState());

        // No refund
        self::assertNull($outcome->getRefundOrder());

        // Events: PaymentReceived only (LoanFullyPaid not expected)
        $events = $outcome->getEvents();
        self::assertTrue($this->containsEvent($events, PaymentReceived::class));
        self::assertFalse($this->containsEvent($events, LoanFullyPaid::class));
        self::assertFalse($this->containsEvent($events, RefundCreated::class));
    }

    public function testAssign_paymentEqualsRemaining_marksAssigned_andClosesLoan_andNoRefund_andEmitsLoanFullyPaidOnce(): void
    {
        $loan = $this->makeLoan(
            amountToPay: '120.00',
            paidAmount: '0.00',
            state: LoanState::ACTIVE
        );

        $payment = $this->makePayment(amount: '120.00');

        $outcome = $this->svc->assign($payment, $loan);

        // Loan becomes paid exactly
        self::assertSame('120.00', $loan->getPaidAmount());
        self::assertSame(LoanState::PAID, $loan->getState());

        // Payment status assigned
        self::assertSame(PaymentState::ASSIGNED, $payment->getState());

        // No refund
        self::assertNull($outcome->getRefundOrder());

        // Events: PaymentReceived + LoanFullyPaid
        $events = $outcome->getEvents();
        self::assertTrue($this->containsEvent($events, PaymentReceived::class));
        self::assertTrue($this->containsEvent($events, LoanFullyPaid::class));
        self::assertFalse($this->containsEvent($events, RefundCreated::class));
    }

    public function testAssign_overpayment_appliesOnlyRemaining_createsRefundOrder_marksPaymentPartiallyAssigned_andEmitsRefundCreated(): void
    {
        $loan = $this->makeLoan(
            amountToPay: '120.00',
            paidAmount: '0.00',
            state: LoanState::ACTIVE
        );

        $payment = $this->makePayment(amount: '150.00');

        $outcome = $this->svc->assign($payment, $loan);

        // Critical: loan must not exceed amountToPay
        self::assertSame(
            '120.00',
            $loan->getPaidAmount(),
            'Loan paidAmount must equal amountToPay after overpayment.'
        );
        self::assertSame(LoanState::PAID, $loan->getState());

        // Payment must be PARTIALLY_ASSIGNED
        self::assertSame(PaymentState::PARTIALLY_ASSIGNED, $payment->getState());

        // Refund order exists and amount is correct: 150 - 120 = 30
        $refund = $outcome->getRefundOrder();
        self::assertInstanceOf(PaymentOrder::class, $refund);
        self::assertSame('30.00', $refund->getAmount());
        self::assertSame(PaymentOrderState::PENDING, $refund->getState());
        self::assertSame($payment->getId(), $refund->getPaymentId());
        self::assertSame($loan->getId(), $refund->getLoanId());

        // Events: PaymentReceived + LoanFullyPaid + RefundCreated
        $events = $outcome->getEvents();
        self::assertTrue($this->containsEvent($events, PaymentReceived::class));
        self::assertTrue($this->containsEvent($events, LoanFullyPaid::class));
        self::assertTrue($this->containsEvent($events, RefundCreated::class));
    }

    public function testAssign_whenLoanAlreadyPaid_mustNotEmitLoanFullyPaidAgain(): void
    {
        // loan already PAID
        $loan = $this->makeLoan(
            amountToPay: '120.00',
            paidAmount: '120.00',
            state: LoanState::PAID
        );

        $payment = $this->makePayment(amount: '10.00');

        $outcome = $this->svc->assign($payment, $loan);

        // Key expectation: no repeated "LoanFullyPaid"
        $events = $outcome->getEvents();
        self::assertFalse(
            $this->containsEvent($events, LoanFullyPaid::class),
            'LoanFullyPaid must not be emitted if loan was already PAID before assign().'
        );

        // Optional (but recommended business behavior):
        // create full refund for 10.00, payment should not be ASSIGNED.
        // If you haven’t implemented this scenario yet — this test will guide you.
        self::assertInstanceOf(PaymentOrder::class, $outcome->getRefundOrder());
        self::assertSame('10.00', $outcome->getRefundOrder()->getAmount());
    }

    // -----------------------
    // Helpers
    // -----------------------

    /** @param list<object> $events */
    private function containsEvent(array $events, string $fqcn): bool
    {
        foreach ($events as $e) {
            if ($e instanceof $fqcn) {
                return true;
            }
        }
        return false;
    }

    private function makeLoan(string $amountToPay, string $paidAmount, LoanState $state): Loan
    {
        return new Loan(
            id: '51ed9314-955c-4014-8be2-b0e2b13588a5',
            customerId: 'c539792e-7773-4a39-9cf6-f273b2581438',
            reference: 'LN12345678',
            amountIssued: '100.00',
            amountToPay: $amountToPay,
            state: $state,
            paidAmount: $paidAmount
        );
    }

    private function makePayment(string $amount): Payment
    {
        return Payment::createReceived(
            id: uniqid('pay_', true),
            externalReference: 'ext-1',
            loanReference: 'LN12345678',
            paymentDate: new \DateTimeImmutable('2026-02-16'),
            amount: $amount
        );
    }
}
