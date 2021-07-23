<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        $this->em = $manager;
        parent::__construct($registry, Post::class);
    }

    /**
     * Get all posts sorted by last modified date
     *
     * @return Post[]
     */
    public function getAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.updated_at', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Check if published categories exist
     *
     * @return bool
     */
    public function countAvailableCategories(): bool
    {
        return $this->em->getRepository(Category::class)->countAvailableCategories();
    }

    /**
     * @return Post[]
     */
    public function getAllPublished(): array
    {
        return $this->createQueryBuilder('p')
//            ->select('p.id', 'p.content', 'p.title', 'p.img', 'pc.title as titleCategory')
            ->andWhere('p.is_published = :pp_val')
            ->setParameter('pp_val', true)
//            ->leftJoin('p.categories', 'pc')
            ->orderBy('p.updated_at', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}