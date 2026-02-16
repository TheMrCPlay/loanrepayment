<?php

namespace App\Domain\Entity;

use App\Domain\Enum\LoanState;

class Loan
{
    // {
    //"id": "51ed9314-955c-4014-8be2-b0e2b13588a5",
    //"customerId": "c539792e-7773-4a39-9cf6-f273b2581438",
    //"reference": "LN12345678",
    //"state": "ACTIVE",
    //"amount_issued": "100.00",
    //"amount_to_pay": "120.00"
    //},

    public function __construct(
        private readonly string $id,
        private readonly string $customerId,
        private readonly string $reference,
        private readonly string $amountIssued,
        private readonly string $amountToPay,
        private LoanState $state,
        private string $paidAmount = "0.00",
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getState(): LoanState
    {
        return $this->state;
    }

    public function isPaidAlready(): bool
    {
        return $this->state === LoanState::PAID;
    }

    public function getAmountIssued(): string
    {
        return $this->amountIssued;
    }

    public function getAmountToPay(): string
    {
        return $this->amountToPay;
    }

    public function getPaidAmount(): string
    {
        return $this->paidAmount;
    }

    public function getRemainingAmount(): string
    {
        return bcsub($this->amountToPay, $this->paidAmount, 2);
    }

    public function applyPayment(string $amount): void
    {
        $remaining = $this->getRemainingAmount();

        if (bccomp($amount, $remaining, 2) === 1) {
            throw new \DomainException("Payment amount exceeds remaining amount to pay.");
        }

        $this->paidAmount = bcadd($this->paidAmount, $amount, 2);

        if (bccomp($this->paidAmount, $this->amountToPay, 2) === 0) {
            $this->state = LoanState::PAID;
        }
    }
}
