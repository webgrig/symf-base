<?php


namespace App\Service\Post;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostType;
use App\Service\Category\CategoryServiceInterface;
use App\Service\File\FileManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class PostService implements PostServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FileManagerInterface
     */
    private $fm;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string|\Stringable|UserInterface|null
     */
    private $currentUserOfSession = null;

    private $request;

    private $session;

    private $form;

    private $postImgDirectory;

    private $categoryService;

    /**
     * PostService constructor.
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        FileManagerInterface $fileManagerService,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        CategoryServiceInterface $categoryService,
        $postImgDirectory

    )
    {
        $this->em = $entityManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->fm = $fileManagerService;
        $this->request = $requestStack->getMainRequest();
        $this->session = $this->request->getSession();
        $this->postImgDirectory = $postImgDirectory;
        if (null !== $tokenStorage->getToken()){
            $this->currentUserOfSession = $tokenStorage->getToken()->getUser();
        }
        $this->categoryService = $categoryService;

    }

    /**
     * @param Post $post
     * @return Form
     */

    public function createForm(Post $post): object
    {
        $categories = $this->em->getRepository(Category::class)->findAll();
        $this->form = $this->formFactory->create(PostType::class, $post, ['categories' => $categories]);
        $this->form->handleRequest($this->request);

        return $this->form;
    }

    /**
     * @return Post[]|mixed|object[]
     */
    public function getAll()
    {
        $this->categoryService->countAvailableEntities();

        return $this->em->getRepository(Post::class)->getAll();
    }

    /**
     * @return bool
     */
    public function countAvailableCategories(): bool
    {
        return $this->categoryService->countAvailableEntities();
    }

    /**
     * @param int $id
     * @return Post
     */

    public function getEntity(int $id) : object
    {
        return $this->em->getRepository(Post::class)->find($id);
    }

    /**
     * @param Post $post
     * @return Post
     */
    public function saveImg(Post $post)
    {
        if (null !== $file = $this->form->get('img')->getData()){
            if ($file instanceof UploadedFile){
                $this->deleteImg($post);

                $post->setImg($this->fm->upload($this->postImgDirectory, $file));
            }
        }
        return $post;
    }

    /**
     * @param Post $post
     */
    public function deleteImg(Post $post): void
    {
        if (null !== $postImg = $post->getImg()){
            $this->fm->remove($this->postImgDirectory,$postImg);
        }
    }


    /**
     * @param Post $post
     * @return PostServiceInterface
     */
    public function save(Post $post)
    {
        $post = $this->saveImg($post);
        $this->em->persist($post);
        $entityCreate = !$post->getId() ? true : false;
        try {
            $this->em->flush();
            if ($entityCreate){
                $this->session->getFlashBag()->add('success', 'Пост создан');
            }
            else{
                $this->session->getFlashBag()->add('success', 'Изменения сохранены');
            }
        }catch (\Exception $e){
            $this->deleteImg($post);
        }
        return $this;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $this->em->getRepository(Post::class);
        if (!in_array('ROLE_SUPER', $this->currentUserOfSession->getRoles())) {
            $this->session->getFlashBag()->add('error', 'У вас нет прав на удаление постов');
        } else {
            $post = $this->em->getRepository(Post::class)->find($id);
            $this->deleteImg($post);
            $this->em->remove($post);
            $this->em->flush();
            $this->session->getFlashBag()->add('error', 'Пост удален');
        }
        return new RedirectResponse($this->router->generate('admin_post'));
    }
}