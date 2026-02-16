<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\PaymentOrder;
use App\Domain\Enum\PaymentOrderState;
use App\Infrastructure\Entity\PaymentOrderEntity;

final class PaymentOrderMapper
{
    public function toDomain(PaymentOrderEntity $entity): PaymentOrder
    {
        return new PaymentOrder(
            id: $entity->getId(),
            paymentId: $entity->getPaymentId(),
            loanId: $entity->getLoanId(),
            amount: $entity->getAmount(),
            state: PaymentOrderState::from($entity->getState()),
            createdAt: $entity->getCreatedAt(),
        );
    }

    public function applyToEntity(PaymentOrder $domain, PaymentOrderEntity $entity): void
    {
        $entity->setPaymentId($domain->getPaymentId());
        $entity->setLoanId($domain->getLoanId());
        $entity->setAmount($domain->getAmount());
        $entity->setState($domain->getState()->value);
        $entity->setCreatedAt($domain->getCreatedAt());
    }

    public function newEntityFromDomain(PaymentOrder $domain): PaymentOrderEntity
    {
        $entity = new PaymentOrderEntity();
        $entity->setId($domain->getId());

        $this->applyToEntity($domain, $entity);

        return $entity;
    }
}
