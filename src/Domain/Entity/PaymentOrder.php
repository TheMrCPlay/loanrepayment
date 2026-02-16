<?php

namespace App\Domain\Entity;

use App\Domain\Enum\PaymentOrderState;

final class PaymentOrder
{
    public function __construct(
        private readonly string $id,
        private readonly string $paymentId,
        private readonly string $loanId,
        private readonly string $amount,
        private PaymentOrderState $state,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getLoanId(): string
    {
        return $this->loanId;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getState(): PaymentOrderState
    {
        return $this->state;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public static function createRefund(
        string $id,
        string $paymentId,
        string $loanId,
        string $amount
    ): self {
        return new self(
            id: $id,
            paymentId: $paymentId,
            loanId: $loanId,
            amount: $amount,
            state: PaymentOrderState::PENDING,
            createdAt: new \DateTimeImmutable()
        );
    }

    public function markProcessed(): void
    {
        $this->state = PaymentOrderState::PROCESSED;
    }

    public function markFailed(): void
    {
        $this->state = PaymentOrderState::FAILED;
    }
}
