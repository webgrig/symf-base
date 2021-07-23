<?php

namespace App\Service\Category;

use App\Entity\Category;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

interface CategoryServiceInterface
{
    /**
     * @param Category $category
     * @return Form
     */
    public function createForm(Category $category): object;

    /**
     * @return Category[]
     */
    public function getAll();

    /**
     * @return bool
     */
    public function countAvailableEntities(): bool;

    /**
     * @param int $id
     * @return Category
     */
    public function getEntity(int $id): object;

    /**
     * @param Category $category
     * @return Category
     */
    public function saveImg(Category $category);

    /**
     * @param Category $category
     */
    public function deleteImg(Category $category): void;

    /**`
     * @param Category $category
     * @return CategoryServiceInterface|Category|string
     */
    public function save(Category $category);

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response;
}