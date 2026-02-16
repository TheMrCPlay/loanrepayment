<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Payment;
use App\Domain\Enum\PaymentState;
use App\Infrastructure\Entity\PaymentEntity;

final class PaymentMapper
{
    public function toDomain(PaymentEntity $paymentEntity): Payment
    {
        return new Payment(
            $paymentEntity->getId(),
            $paymentEntity->getExternalReference(),
            $paymentEntity->getLoanReference(),
            $paymentEntity->getPaymentDate(),
            $paymentEntity->getAmount(),
            PaymentState::from($paymentEntity->getState()),
            $paymentEntity->getAssignedAmount()
        );
    }

    public function toEntity(Payment $payment, PaymentEntity $paymentEntity): void
    {
        $paymentEntity
            ->setId($payment->getId())
            ->setExternalReference($payment->getExternalReference())
            ->setLoanReference($payment->getLoanReference())
            ->setPaymentDate($payment->getPaymentDate())
            ->setAmount($payment->getAmount())
            ->setState($payment->getState()->value)
            ->setAssignedAmount($payment->getAssignedAmount())
        ;
    }

    public function newEntityFromDomain(Payment $payment): PaymentEntity
    {
        $paymentEntity = new PaymentEntity();
        $paymentEntity->setId($payment->getId());
        $this->toEntity($payment, $paymentEntity);
        return $paymentEntity;
    }
}
