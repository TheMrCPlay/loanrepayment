<?php

namespace App\Application\Dto;

final class IncomingPaymentDto
{
//    public function __construct(
//        private readonly string $firstname,
//        private readonly string $lastname,
//        private readonly string $paymentDate,
//        private readonly string $amount,
//        private readonly string $description,
//        private readonly string $refId,
//    ) {
//    }

    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $paymentDate,
        public string $amount,
        public string $description,
        public string $refId,
    ) {}

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
