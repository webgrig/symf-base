<?php

namespace App\Controller\Admin;


use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryType;
use App\Repository\CategoryRepositoryInterface;
use App\Service\Category\CategoryService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 */
class CategoryController extends BaseController
{
    private $categoryRepository;

    private $categoryService;

    /**
     * CategoryController constructor.
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository, CategoryService $categoryService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->categoryService =  $categoryService;
    }
    /**
     * @Route("/admin/category", name="admin_category")
     */
    public function indexAction()
    {
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Категории';
        $forRender['categories'] = $this->categoryRepository->findAll();
        if (!$forRender['categories']){
            $this->addFlash('error', 'В настоящий момент нет ни одной категории.');
        }
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
        $category = $this->categoryRepository->find($category_id);
        $form = $this->categoryService->createForm($category);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($form->get('save')->isClicked())
            {
                $this->categoryService->save($category);
            }
            if ($form->get('delete')->isClicked())
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
