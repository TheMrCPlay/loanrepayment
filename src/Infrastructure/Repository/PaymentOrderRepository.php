<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\PaymentOrder;
use App\Infrastructure\Entity\PaymentOrderEntity;
use App\Infrastructure\Mapper\PaymentOrderMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentOrderEntity>
 */
class PaymentOrderRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaymentOrderMapper $mapper
    ) {
        parent::__construct($registry, PaymentOrderEntity::class);
    }

    public function save(PaymentOrder $order): void
    {
        $em = $this->getEntityManager();

        /** @var PaymentOrderEntity|null $entity */
        $entity = $em->find(PaymentOrderEntity::class, $order->getId());

        if (!$entity) {
            $entity = $this->mapper->newEntityFromDomain($order);
            $em->persist($entity);
            return;
        }

        $this->mapper->applyToEntity($order, $entity);
    }
}
