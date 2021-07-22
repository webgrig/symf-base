<?php


namespace App\Controller\Admin;


use App\Entity\Post;
use App\Service\Post\PostService;
use App\Service\Post\PostServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class PostController
 * @package App\Controller\Admin
 */
class PostController extends BaseController
{
    private $postService;

    /**
     * PostController constructor.
     * @param PostServiceInterface $postService
     */
    public function __construct(PostServiceInterface $postService)
    {
        $this->postService = $postService;
    }

    /**
     * @Route("/admin/post", name="admin_post")
     */
    public function indexAction()
    {
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Посты';
        $forRender['posts'] = $this->postService->getAll();
//        dd($forRender['categories']);
        return $this->render('admin/post/index.html.twig', $forRender);
    }

    /**
     * @Route("/admin/post/create", name="admin_post_create")
     * @param RedirectResponse|Response
     */
    public function createAction()
    {
        $post = new Post();
        $form = $this->postService->createForm($post);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);
            return $this->redirectToRoute('admin_post');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Создание поста';
        $forRender['form'] = $form->createView();


        return $this->render('admin/post/form.html.twig', $forRender);

    }

    /**
     * @Route("/admin/post/update/{post_id}", name="admin_post_update")
     * @param int $post_id
     * @param RedirectResponse|Response
     *
     */
    public function updateAction(int $post_id)
    {
        $post = $this->postService->getEntity($post_id);
        $form = $this->postService->createForm($post);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->postService->save($post);
            } elseif ($form->get('delete')->isSubmitted()) {
                return $this->deleteAction($post->getId());

            }

            return $this->redirectToRoute('admin_post');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование поста';
        $forRender['form'] = $form->createView();

        return $this->render('admin/post/form.html.twig', $forRender);

    }


    /**
     * @Route("/admin/post/delete/{post_id}", name="admin_post_delete")
     * @param int $post_id
     * @return Response
     */
    public function deleteAction(int $post_id): Response
    {
        return $this->postService->delete($post_id);
    }
}
