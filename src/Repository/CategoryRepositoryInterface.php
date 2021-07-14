<?php


namespace App\Repository;


use App\Entity\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return Category[]
     */
    public function getAllCategories(): array;

    /**
     * @param int $categoryId
     * @return object
     */
    public function getOneCategory(int $categoryId): object;

    /**
     * @param Category $category
     * @return object
     */
    public function setCreateCategory(Category $category): object;

    /**
     * @param Category $category
     * @return object
     */
    public function setSaveCategory(Category $category): object;

    /**
     * @param Category $category
     * @return Category
     *
     */
    public function setUpdateCategory(Category $category): object;

    /**
     * @param Category $category
     */
    public function setDeleteCategory(Category $category);

    /**
     * @param PostRepository $postRepository
     * @param int $categoryId
     * @return bool
     */
    public function getHavePostsCategory(PostRepository $postRepository, int $categoryId): bool;

}