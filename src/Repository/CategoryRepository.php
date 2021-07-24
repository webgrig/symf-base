<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class CategoryRepository extends ServiceEntityRepository implements CategoryRepositoryInterface
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        $this->em = $manager;
        parent::__construct($registry, Category::class);
    }

    /**
     * Get all categories sorted by last modified date
     *
     * @return Category[]
     */
    public function getAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.updated_at', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Check if published categories exist
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkAvailableCategories(): bool
    {
        return boolval(
            $this->createQueryBuilder('c')
                ->select('count(c.id)')
                ->andWhere('c.is_published = :val')
                ->setParameter('val', true)
                ->getQuery()
                ->getSingleScalarResult()
        );
    }

}
