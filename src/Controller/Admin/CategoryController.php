<?php

namespace App\Controller\Admin;


use App\Entity\Category;
use App\Service\Category\CategoryService;
use App\Service\Category\CategoryServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 */
class CategoryController extends BaseController
{
    private $categoryService;

    /**
     * CategoryController constructor.
     * @param CategoryServiceInterface $categoryService
     */
    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService =  $categoryService;
    }
    /**
     * @Route("/admin/category", name="admin_category")
     */
    public function indexAction()
    {
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Категории';
        $forRender['categories'] = $this->categoryService->getAllEntities();
        return $this->render('admin/category/index.html.twig', $forRender);
    }

    /**
     * @Route("/admin/category/create", name="admin_category_create")
     * @return RedirectResponse|Response
     */
    public function createAction()
    {
        $category = new Category();
        $form = $this->categoryService->createForm($category);
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->categoryService->save($category);
            return $this->redirectToRoute('admin_category');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Создание категории';
        $forRender['form'] = $form->createView();

        return $this->render('admin/category/form.html.twig', $forRender);

    }

    /**
     * @Route("/admin/category/update/{category_id}", name="admin_category_update")
     * @param int $category_id
     * @return RedirectResponse|Response
     */
    public function updateAction(int $category_id)
    {
        $category = $this->categoryService->getEntity($category_id);
        $form = $this->categoryService->createForm($category);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($form->get('save')->isClicked())
            {
                $this->categoryService->save($category);
            }
            elseif ($form->get('delete')->isSubmitted())
            {
                return $this->deleteAction($category->getId());

            }

            return $this->redirectToRoute('admin_category');
        }
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование категории';
        $forRender['form'] = $form->createView();

        return $this->render('admin/category/form.html.twig', $forRender);

    }

    /**
     * @Route("/admin/category/delete/{category_id}", name="admin_category_delete")
     * @param int $category_id
     * @return Response
     */
    public function deleteAction(int $category_id): Response
    {
        return $this->categoryService->delete($category_id);
    }
}
