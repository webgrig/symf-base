<?php


namespace App\Service\Category;


use App\Entity\Category;
use App\Repository\CategoryRepositoryInterface;

class CategoryService
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * CategoryService constructor.
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Category $category
     * @return CategoryService
     */
    public function handleCreateCategory(Category $category)
    {
        $category->setCreateAtValue();
        $category->setUpdateAtValue();
        $category->setIsPublished();
        $this->categoryRepository->setCreateCategory($category);
        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function handleUpdateCategory(Category $category)
    {
        $category->setUpdateAtValue();
        $this->categoryRepository->setSaveCategory($category);
        return $this;
    }

    /**
     * @param Category $category
     */
    public function handleDeleteCategory(Category $category)
    {
        $this->categoryRepository->setDeleteCategory($category);
    }

}