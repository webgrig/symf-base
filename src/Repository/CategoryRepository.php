<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
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
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        parent::__construct($registry, Category::class);
    }

    /**
     * @return array
     */
    public function getAllCategories(): array
    {
        return parent::findAll();
    }

    /**
     * @param int $categoryId
     * @return Category
     */
    public function getOneCategory(int $categoryId): object
    {
        return parent::find($categoryId);
    }

    /**
     * @param Category $category
     * @return object
     */
    public function setCreateCategory(Category $category): object
    {
        $this->manager->persist($category);
        $this->manager->flush();
        return $this;
    }


    /**
     * @param Category $category
     * @return object
     */
    public function setSaveCategory(Category $category): object
    {
        $this->manager->flush();
        return $this;
    }

    /**
     * @param Category $category
     * @return object
     */
    public function setUpdateCategory(Category $category): object
    {
        $this->manager->flush();
        return $category;
    }

    /**
     * @param Category $category
     * @return mixed
     */
    public function setDeleteCategory(Category $category)
    {
        $this->manager->remove($category);
        $this->manager->flush();
    }

    /**
     * @param PostRepository $postRepository
     * @param int $categoryId
     * @return bool
     */

    public function getHavePostsCategory(PostRepository $postRepository, int $categoryId): bool
    {
        return $postRepository->findOneBy(['category' => $categoryId]) ? true : false;
    }

}
