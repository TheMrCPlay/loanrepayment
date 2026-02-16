<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Loan;
use App\Domain\Enum\LoanState;
use App\Infrastructure\Entity\LoanEntity;

final class LoanMapper
{
    public function toDomain(LoanEntity $loanEntity): Loan
    {
        return new Loan(
            $loanEntity->getId(),
            $loanEntity->getCustomerId(),
            $loanEntity->getReference(),
            $loanEntity->getAmountIssued(),
            $loanEntity->getAmountToPay(),
            LoanState::from($loanEntity->getState()),
            $loanEntity->getPaidAmount()
        );
    }

    public function toEntity(Loan $loan, LoanEntity $loanEntity): void
    {
        $loanEntity->setCustomerId($loan->getCustomerId())
            ->setReference($loan->getReference())
            ->setState($loan->getState()->value)
            ->setAmountIssued($loan->getAmountIssued())
            ->setAmountToPay($loan->getAmountToPay())
            ->setPaidAmount($loan->getPaidAmount());
    }

    public function newEntityFromDomain(Loan $loan): LoanEntity
    {
        $entity = new LoanEntity();
        $entity->setId($loan->getId());

        $this->toEntity($loan, $entity);

        return $entity;
    }
}
