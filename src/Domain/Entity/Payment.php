<?php

namespace App\Domain\Entity;

use App\Domain\Enum\PaymentState;

final class Payment
{
    public function __construct(
        private readonly string $id,
        private readonly string $externalReference,
        private readonly string $loanReference,
        private readonly \DateTimeImmutable $paymentDate,
        private readonly string $amount,
        private PaymentState $state,
        private string $assignedAmount = '0.00',
    ) {
    }
    
    public static function createReceived(
        string $id,
        string $externalReference,
        string $loanReference,
        \DateTimeImmutable $paymentDate,
        string $amount
    ) {
        return new self(
            $id,
            $externalReference,
            $loanReference,
            $paymentDate,
            $amount,
            PaymentState::RECEIVED,
            '0.00'
        );
    }

    public function markAssigned(string $assignedAmount): void {
        $this->state = PaymentState::ASSIGNED;
        $this->assignedAmount = $assignedAmount;
    }

    public function markPartiallyAssigned(string $assignedAmount): void
    {
        $this->state = PaymentState::PARTIALLY_ASSIGNED;
        $this->assignedAmount = $assignedAmount;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExternalReference(): string
    {
        return $this->externalReference;
    }

    public function getLoanReference(): string
    {
        return $this->loanReference;
    }

    public function getPaymentDate(): \DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getState(): PaymentState
    {
        return $this->state;
    }

    public function getAssignedAmount(): string
    {
        return $this->assignedAmount;
    }
}
