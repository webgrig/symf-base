<?php


namespace App\Controller\Main;

use App\Entity\Category;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\PostRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * HomeController constructor.
     * @param PostRepositoryInterface $postRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(PostRepositoryInterface $postRepository, CategoryRepositoryInterface $categoryRepository)
    {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function indexAction(){
        $forRender = $this->renderDefault();
        $forRender['posts'] = $this->postRepository->getAll();
        return $this->render('main/index.html.twig', $forRender);
    }

    /**
     * @Route("/filter/categories", name="filter_categories")
     * @return Response
     */
    public function categoryFilter(): Response
    {
        $forRender = $this->renderDefault();
        $forRender['categories'] = $this->categoryRepository->findBy(['is_published' => true], ['updated_at' => 'ASC']);
        $forRender['title'] = 'Фильтр категорий';
        return $this->render('main/filter/index.html.twig', $forRender);
    }
}