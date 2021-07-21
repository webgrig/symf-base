<?php


namespace App\Controller\Admin;


use App\Entity\Post;
use App\Repository\PostRepositoryInterface;
use App\Service\Post\PostService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class PostController
 * @package App\Controller\Admin
 */
class PostController extends BaseController
{
    private $postRepository;

    private $postService;

    /**
     * PostController constructor.
     * @param PostRepositoryInterface $postRepository
     * @param PostService $postService
     */
    public function __construct(PostRepositoryInterface $postRepository,
                                PostService $postService)
    {
        $this->postRepository = $postRepository;
        $this->postService = $postService;
    }

    /**
     * @Route("/admin/post", name="admin_post")
     */
    public function indexAction()
    {
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Посты';
        $forRender['categories'] = $this->postRepository->findAll();
        return $this->render('admin/post/index.html.twig', $forRender);
    }

    /**
     * @Route("/admin/post/create", name="admin_post_create")
     * @param Request $request
     * @param RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form->get('image')->getData();
            $this->postService->handleCreatePost($form, $post);
            $this->addFlash('success', 'Пост добавлен');
            return $this->redirectToRoute('admin_post');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Создание поста';
        $forRender['form'] = $form->createView();

        return $this->render('admin/post/form.html.twig', $forRender);

    }



    /**
     * @Route("/admin/post/update/{id}", name="admin_post_update")
     * @param int $id
     * @param Request $request
     * @param RedirectResponse|Response
     *
     */
    public function updateAction(int $id, Request $request)
    {
        $post = $this->postRepository->getOnePost($id);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($form->get('save')->isClicked())
            {
                $file = $form->get('image')->getData();
                $this->postRepository->setUpdatePost($post, $file);
                $this->addFlash('success', 'Пост обновлен');
            }
            if ($form->get('delete')->isClicked())
            {
                $this->postRepository->setDeletePost($post);
                $this->addFlash('error', 'Пост удален');
            }
            return $this->redirectToRoute('admin_post');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование поста';
        $forRender['form'] = $form->createView();

        return $this->render('admin/post/form.html.twig', $forRender);

    }
}