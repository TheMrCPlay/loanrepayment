<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Loan;
use App\Domain\Exception\LoanNotFoundException;
use App\Infrastructure\Entity\LoanEntity;
use App\Infrastructure\Mapper\LoanMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class LoanRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly LoanMapper $loanMapper
    ) {
        parent::__construct($this->registry, LoanEntity::class);
    }

    public function findByReferenceId(string $referenceId): ?LoanEntity
    {
        $entity = $this->createQueryBuilder('l')
            ->where('l.reference = :referenceId')
            ->setParameter('referenceId', $referenceId)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();

        if (!$entity) {
            throw new LoanNotFoundException($referenceId);
        }

        return $entity;
    }

    public function getDomainEntityByReferenceId(string $referenceId): Loan
    {
        $entity = $this->findByReferenceId($referenceId);

        return $this->loanMapper->toDomain($entity);
    }

    public function save(Loan $loan): void
    {
        $em = $this->getEntityManager();

        $entity = $em->find(LoanEntity::class, $loan->getId());

        if (!$entity) {
            $loanEntity = $this->loanMapper->newEntityFromDomain($loan);
            $em->persist($loanEntity);
            return;
        }

        $this->loanMapper->toEntity($loan, $entity);

    }
}
