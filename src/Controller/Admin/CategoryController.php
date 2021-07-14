<?php

namespace App\Controller\Admin;


use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryType;
use App\Repository\CategoryRepositoryInterface;
use App\Service\Category\CategoryService;
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
        $forRender['categories'] = $this->categoryRepository->getAllCategories();
        if (!$forRender['categories']){
            $this->addFlash('error', 'В настоящий момент нет ни одной категории.');
        }
        return $this->render('admin/category/index.html.twig', $forRender);
    }

    /**
     * @Route("/admin/category/create", name="admin_category_create")
     * @param Request $request
     * @param RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->categoryService->handleCreateCategory($category);
            $this->addFlash('success', 'Категория добавлена');
            return $this->redirectToRoute('admin_category');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Создание категории';
        $forRender['form'] = $form->createView();

        return $this->render('admin/category/form.html.twig', $forRender);

    }

    /**
     * @Route("/admin/category/update/{id}", name="admin_category_update")
     * @param int $id
     * @param Request $request
     * @param RedirectResponse|Response
     */
    public function updateAction(int $id, Request $request)
    {
        $category = $this->categoryRepository->getOneCategory($id);
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($form->get('save')->isClicked())
            {
                $this->categoryService->handleUpdateCategory($category);
                $this->addFlash('success', 'Категория обновлена');
            }
            if ($form->get('delete')->isClicked())
            {
                $postRepository = $this->getDoctrine()->getManager()->getRepository(Post::class);
                if (!$this->categoryRepository->getHavePostsCategory($postRepository, $category->getId())){
                    $this->categoryService->handleDeleteCategory($category);
                    $this->addFlash('error', 'Категория удалена');
                }
                else{
                    $this->addFlash('error', 'Категория не пуста, и не может быть удалена');
                }

            }

            return $this->redirectToRoute('admin_category');
        }
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование категории';
        $forRender['form'] = $form->createView();

        return $this->render('admin/category/form.html.twig', $forRender);

    }
}
