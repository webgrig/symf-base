<?php


namespace App\Controller\Main;

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
     * HomeController constructor.
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function indexAction(){
        $forRender = parent::renderDefault();
        $forRender['posts'] = $this->postRepository->getAll();
        return $this->render('main/index.html.twig', $forRender);
    }
}