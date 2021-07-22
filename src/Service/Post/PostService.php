<?php


namespace App\Service\Post;


use App\Entity\Post;
use App\Form\PostType;
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

class PostService
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

    }

    /**
     * @param Post $post
     * @return Form
     */

    public function createForm(Post $post): object
    {
        $this->form = $this->formFactory->create(PostType::class, $post);
        $this->form->handleRequest($this->request);

        return $this->form;
    }

    /**
     * @return array
     */
    public function getAllEntities(): array
    {
        return  $this->em->getRepository(Post::class)->findAll();
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
     */
    public function deleteImg(Post $post): void
    {
        if (null !== $postImg = $post->getImg()){
            $this->fm->remove($this->postImgDirectory,$postImg);
        }
    }


    /**
     * @param Post $post
     * @return Post|string
     */
    public function save(Post $post): mixed
    {
        if (null !== $file = $this->form->get('img')->getData()){
            if ($file instanceof UploadedFile){
                $this->deleteImg($post);
                $post->setImg($this->fm->upload($this->postImgDirectory, $file));
            }
        }
        if (!$post->getId()){
            $this->session->getFlashBag()->add('success', 'Пост создан');
        }
        else{
            $this->session->getFlashBag()->add('success', 'Изменения сохранены');
        }
        $this->em->persist($post);
        try {
            $this->em->flush();
        } catch (\Exception $e){
            $this->deleteImg($post);
            $this->session->getFlashBag()->add('error', $e->getMessage());
            return $e->getMessage();
        }
        return $post;
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