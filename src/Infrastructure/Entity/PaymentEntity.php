<?php

namespace App\Infrastructure\Entity;

use App\Infrastructure\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payment')]
#[ORM\UniqueConstraint(name: 'uniq_payment_external_ref', columns: ['external_reference'])]
class PaymentEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'external_reference', type: 'string', length: 64)]
    private string $externalReference;

    #[ORM\Column(name: 'loan_reference', type: 'string', length: 16)]
    private string $loanReference;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 2)]
    private string $amount;

    #[ORM\Column(name: 'assigned_amount', type: 'decimal', precision: 18, scale: 2)]
    private string $assignedAmount = "0.00";

    #[ORM\Column(type: 'string', length: 32)]
    private string $state;

    #[ORM\Column(name: 'payment_date', type: 'datetime_immutable')]
    private \DateTimeImmutable $paymentDate;

    public function setId(string $id): PaymentEntity
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setExternalReference(string $externalReference): PaymentEntity
    {
        $this->externalReference = $externalReference;
        return $this;
    }

    public function getExternalReference(): string
    {
        return $this->externalReference;
    }

    public function setLoanReference(string $loanReference): PaymentEntity
    {
        $this->loanReference = $loanReference;
        return $this;
    }

    public function getLoanReference(): string
    {
        return $this->loanReference;
    }

    public function setAmount(string $amount): PaymentEntity
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAssignedAmount(string $assignedAmount): PaymentEntity
    {
        $this->assignedAmount = $assignedAmount;
        return $this;
    }

    public function getAssignedAmount(): string
    {
        return $this->assignedAmount;
    }

    public function setState(string $state): PaymentEntity
    {
        $this->state = $state;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setPaymentDate(\DateTimeImmutable $paymentDate): PaymentEntity
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getPaymentDate(): \DateTimeImmutable
    {
        return $this->paymentDate;
    }
}
