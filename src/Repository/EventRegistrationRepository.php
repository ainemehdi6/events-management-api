<?php

namespace App\Repository;

use App\Entity\EventRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<EventRegistration>
 */
class EventRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }

    /**
     * @return EventRegistration[] Returns registrations for an event
     */
    public function findByEvent(Uuid $eventId): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'u', 'e')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.event', 'e')
            ->andWhere('e.id = :eventId')
            ->setParameter('eventId', $eventId, 'uuid')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return EventRegistration[] Returns user's registrations
     */
    public function findByUser(Uuid $userId): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'u', 'e')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.event', 'e')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId, 'uuid')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByEventAndUser(Uuid $eventId, Uuid $userId): ?EventRegistration
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'u', 'e')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.event', 'e')
            ->andWhere('e.id = :eventId')
            ->andWhere('u.id = :userId')
            ->setParameter('eventId', $eventId, 'uuid')
            ->setParameter('userId', $userId, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}