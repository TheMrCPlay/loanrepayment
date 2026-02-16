<?php

namespace App\Domain\Event;

final class PaymentReceived
{
    public function __construct(
        private readonly string $paymentId,
        private readonly string $customerId,
        private readonly string $loanId
    ) {
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getLoanId(): string
    {
        return $this->loanId;
    }
}
