<?php

namespace App\Repository;


use App\Entity\Post;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface PostRepositoryInterface
{
    /**
     * Get all posts sorted by last modified date
     *
     * @return Post[]
     */
    public function getAll(): array;

    /**
     * Check if published categories exist
     *
     * @return bool
     */
    public function countAvailableCategories(): bool;

    /**
     * @param $categoryId
     * @return Post[]
     */
    public function getPostsCategory($categoryId): array;


}