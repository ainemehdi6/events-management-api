<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[] Returns an array of upcoming events
     */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c', 'o', 'r') // Add needed joins
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.registrations', 'r')
            ->andWhere('e.date > :now')
            ->andWhere('e.status = :status')
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'published')
            ->orderBy('e.date', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * @return Event[] Returns an array of all events with related entities
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c', 'o', 'r') // Eager load related entities
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.registrations', 'r')
            ->orderBy('e.date', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @return Event[] Returns an array of events by category
     */
    public function findByCategory(Uuid $categoryId): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c', 'o', 'r')
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.registrations', 'r')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId, 'uuid')
            ->orderBy('e.date', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * @return Event[] Returns an array of events organized by a user
     */
    public function findByOrganizer(Uuid $organizerId): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c', 'o', 'r')
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.registrations', 'r')
            ->andWhere('o.id = :organizerId')
            ->setParameter('organizerId', $organizerId, 'uuid')
            ->orderBy('e.date', 'DESC')
            ->getQuery()->getResult();
    }

    public function findOneById(Uuid $id): ?Event
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c', 'o', 'r')
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.registrations', 'r')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}