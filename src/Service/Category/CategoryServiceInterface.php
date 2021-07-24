<?php

namespace App\Service\Category;

use App\Entity\Category;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface CategoryServiceInterface
{

    /**
     * @param Category $category
     * @return FormInterface
     */

    public function createForm(Category $category): object;

    /**
     * @return Category[]
     */
    public function getAll(): array;

    /**
     * @return bool
     */
    public function checkAvailableCategories(): bool;

    /**
     * @param int $id
     * @return Category
     */
    public function getOne(int $id): object;

    /**
     * @param Category $category
     * @return Category
     */
    public function saveImg(Category $category): object;

    /**
     * @param Category $category
     */
    public function deleteImg(Category $category): void;

    /**
     * @param Category $category
     * @return Category
     */
    public function save(Category $category): object;

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse;
}