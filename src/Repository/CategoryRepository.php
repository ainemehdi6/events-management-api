<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[] Returns an array of active categories
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'e') // Add needed joins
            ->leftJoin('c.events', 'e')
            ->groupBy('c.id')
            ->having('COUNT(e.id) > 0')
            ->getQuery()->getResult();
    }

    public function findOneById(Uuid $id): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}