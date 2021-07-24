<?php

namespace App\Repository;


use App\Entity\Category;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface CategoryRepositoryInterface
{
    /**
     * Get all categories sorted by last modified date
     *
     * @return Category[]
     */
    public function getAll(): array;

    /**
     * Check if published categories exist
     *
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkAvailableCategories(): bool;
}