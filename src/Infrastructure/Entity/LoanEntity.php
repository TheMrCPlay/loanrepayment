<?php

namespace App\Infrastructure\Entity;

use App\Infrastructure\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
#[ORM\Table(name: 'loan')]
#[ORM\UniqueConstraint(name: 'uniq_loan_reference', columns: ['reference'])]
class LoanEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'customer_id', type: 'uuid')]
    private string $customerId;

    #[ORM\Column(type: 'string', length: 16)]
    private string $reference;

    #[ORM\Column(type: 'string', length: 16)]
    private string $state;

    #[ORM\Column(name: 'amount_issued', type: 'decimal', precision: 18, scale: 2)]
    private string $amountIssued;

    #[ORM\Column(name: 'amount_to_pay', type: 'decimal', precision: 18, scale: 2)]
    private string $amountToPay;

    #[ORM\Column(name: 'paid_amount', type: 'decimal', precision: 18, scale: 2)]
    private string $paidAmount = "0.00";

    public function setId(string $id): LoanEntity
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setCustomerId(string $customerId): LoanEntity
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setReference(string $reference): LoanEntity
    {
        $this->reference = $reference;
        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setState(string $state): LoanEntity
    {
        $this->state = $state;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setAmountIssued(string $amountIssued): LoanEntity
    {
        $this->amountIssued = $amountIssued;
        return $this;
    }

    public function getAmountIssued(): string
    {
        return $this->amountIssued;
    }

    public function setAmountToPay(string $amountToPay): LoanEntity
    {
        $this->amountToPay = $amountToPay;
        return $this;
    }

    public function getAmountToPay(): string
    {
        return $this->amountToPay;
    }

    public function setPaidAmount(string $paidAmount): LoanEntity
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    public function getPaidAmount(): string
    {
        return $this->paidAmount;
    }
}
