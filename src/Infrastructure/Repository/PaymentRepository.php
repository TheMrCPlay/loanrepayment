<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Payment;
use App\Infrastructure\Entity\PaymentEntity;
use App\Infrastructure\Mapper\PaymentMapper;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly PaymentMapper $paymentMapper
    ) {
        parent::__construct($this->registry, PaymentEntity::class);
    }

    public function existsByExternalReference(string $reference): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('1')
            ->where('p.externalReference = :reference')
            ->setParameter('reference', $reference)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Payment $payment): void
    {
        $entity = $this->getEntityManager()->find(PaymentEntity::class, $payment->getId());

        if (!$entity) {
            $entity = $this->paymentMapper->newEntityFromDomain($payment);
            $this->getEntityManager()->persist($entity);
            return;
        }

        $this->paymentMapper->toEntity($payment, $entity);
    }

    public function nextId(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function findByDate(\DateTimeImmutable $date): array
    {
        $qb = $this->createQueryBuilder('p');
        $start = $date->setTime(0, 0);
        $end = $date->setTime(23, 59, 59);

        return $qb->where(
            $qb->expr()->between('p.paymentDate', ':from', ':to')
        )
            ->setParameter('from', $start)
            ->setParameter('to', $end)
            ->getQuery()
            ->getResult();
    }
}
