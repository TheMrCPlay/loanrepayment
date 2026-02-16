<?php

namespace App\Domain\Assignment;

use App\Domain\Entity\PaymentOrder;

class AssignmentOutcome
{
    public function __construct(
        private readonly array $events,
        private readonly ?PaymentOrder $paymentOrder = null
    ) {
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function hasRefundOrder(): bool
    {
        return !is_null($this->paymentOrder);
    }

    public function getRefundOrder(): ?PaymentOrder
    {
        return $this->paymentOrder;
    }
}
