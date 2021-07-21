<?php


namespace App\Service\Category;


use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryType;
use App\Repository\CategoryRepositoryInterface;
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

class CategoryService
{
    /**
     * @var CategoryRepositoryInterface
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
     * @var string|\Stringable|UserInterface
     */
    private $tokenStorage;

    private $request;

    private $session;

    private $form;

    private $categoryImgDirectory;

    /**
     * CategoryService constructor.
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
        $categoryImgDirectory

    )
    {
        $this->em = $entityManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->fm = $fileManagerService;
        $this->tokenStorage = $tokenStorage;
        $this->request = $requestStack->getMainRequest();
        $this->session = $this->request->getSession();
        $this->categoryImgDirectory = $categoryImgDirectory;

    }

    /**
     * @param Category $category
     * @return Form
     */

    public function createForm(Category $category): object
    {
        $this->form = $this->formFactory->create(CategoryType::class, $category);
        $this->form->handleRequest($this->request);

        return $this->form;
    }


    /**
     * @param Category $category
     */
    public function deleteImg(Category $category): void
    {
        if (null !== $categoryImg = $category->getImg()){
            $this->fm->remove($this->categoryImgDirectory,$categoryImg);
        }
    }


    /**
     * @param Category $category
     * @return Category
     */
    public function save(Category $category): object
    {
        if (null !== $file = $this->form->get('img')->getData()){
            if ($file instanceof UploadedFile){
                $this->deleteImg($category);
                $category->setImg($this->fm->upload($this->categoryImgDirectory, $file));
            }
        }
        if (!$category->getId()){
            $this->session->getFlashBag()->add('success_create', 'Категория создана');
        }
        else{
            $this->session->getFlashBag()->add('success', 'Изменения сохранены');
        }
        $this->em->persist($category);
        $this->em->flush();
        return $category;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $currentUserOfSession = $this->tokenStorage->getToken()->getUser();
        $postRepository = $this->em->getRepository(Post::class);
        if (!in_array('ROLE_SUPER', $currentUserOfSession->getRoles())) {
            $this->session->getFlashBag()->add('error', 'У вас нет прав на удаление категорий');
        }
        elseif ($this->em->getRepository(Category::class)->getHavePostsCategory($postRepository, $id)){
            $this->session->getFlashBag()->add('error', 'Категория не пуста, и не может быть удалена');
        } else {
            $category = $this->em->getRepository(Category::class)->find($id);
            $this->deleteImg($category);
            $this->em->remove($category);
            $this->em->flush();
            $this->session->getFlashBag()->add('error', 'Категория удалена');
        }
        return new RedirectResponse($this->router->generate('admin_category'));
    }
}