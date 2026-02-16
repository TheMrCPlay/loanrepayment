<?php

namespace App\Infrastructure\Entity;

use App\Infrastructure\Repository\PaymentOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentOrderRepository::class)]
#[ORM\Table(name: 'payment_order')]
class PaymentOrderEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'payment_id', type: 'uuid')]
    private ?string $paymentId = null;

    #[ORM\Column(name: 'loan_id', type: 'uuid')]
    private ?string $loanId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 32)]
    private ?string $state = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): PaymentOrderEntity
    {
        $this->id = $id;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): PaymentOrderEntity
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getLoanId(): ?string
    {
        return $this->loanId;
    }

    public function setLoanId(string $loanId): PaymentOrderEntity
    {
        $this->loanId = $loanId;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): PaymentOrderEntity
    {
        $this->amount = $amount;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): PaymentOrderEntity
    {
        $this->state = $state;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): PaymentOrderEntity
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
