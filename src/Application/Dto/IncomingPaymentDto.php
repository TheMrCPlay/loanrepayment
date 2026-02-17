<?php

namespace App\Application\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class IncomingPaymentDto
{
    public function __construct(
        #[SerializedName('payerName')]
        private readonly string $firstname,
        #[SerializedName('payerSurname')]
        private readonly string $lastname,
        #[SerializedName('paymentDate')]
        private readonly string $paymentDate,
        #[SerializedName('amount')]
        private readonly string $amount,
        #[SerializedName('description')]
        private readonly string $description,
        #[SerializedName('paymentReference')]
        private readonly string $refId,
    ) {
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getPaymentDate(): string
    {
        return $this->paymentDate;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getRefId(): string
    {
        return $this->refId;
    }

    public function extractLoanRef(): ?string
    {
        return preg_match('/(?<ref>LN\d{8})/', $this->getDescription(), $m)
            ? $m['ref']
            : null;
    }
}
