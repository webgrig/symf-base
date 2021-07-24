<?php


namespace App\Service\Category;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Service\File\FileManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CategoryService implements CategoryServiceInterface
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

    private $categoryImgDirectory;

    /**
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
        $this->request = $requestStack->getMainRequest();
        $this->session = $this->request->getSession();
        $this->categoryImgDirectory = $categoryImgDirectory;
        if (null !== $tokenStorage->getToken()){
            $this->currentUserOfSession = $tokenStorage->getToken()->getUser();
        }

    }

    /**
     * @param Category $category
     * @return FormInterface
     */

    public function createForm(Category $category): object
    {
        $this->form = $this->formFactory->create(CategoryType::class, $category);
        $this->form->handleRequest($this->request);

        return $this->form;
    }

    /**
     * @return Category[]
     */
    public function getAll(): array
    {
        if (!$categories = $this->em->getRepository(Category::class)->getAll()){
            $this->session->getFlashBag()->add('error', 'В настоящий момент нет ни одной категории.');
        }
        return $categories;
    }

    /**
     * @return bool
     */
    public function checkAvailableCategories(): bool
    {
        $amountAvailableCategories = $this->em->getRepository(Category::class)->checkAvailableCategories();
        if (!$amountAvailableCategories){
            $this->session->getFlashBag()->add('error-available-categories', 'В настоящий момент нет ни одной доступной категории.');;
        }
        return $amountAvailableCategories;
    }

    /**
     * @param int $id
     * @return Category
     */
    public function getOne(int $id): object
    {
        return $this->em->getRepository(Category::class)->find($id);
    }


    /**
     * @param Category $category
     * @return Category
     */
    public function saveImg(Category $category): object
    {
        if (null !== $file = $this->form->get('img')->getData()){
            if ($file instanceof UploadedFile){
                $this->deleteImg($category);

                $category->setImg($this->fm->upload($this->categoryImgDirectory, $file));
            }
        }
        return $category;
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
        $category = $this->saveImg($category);
        $this->em->persist($category);
        $entityCreate = !$category->getId() ? true : false;
        try {
            $this->em->flush();
            if ($entityCreate){
                $this->session->getFlashBag()->add('success', 'Категория создана');
            }
            else{
                $this->session->getFlashBag()->add('success', 'Изменения сохранены');
            }
        }catch (\Exception $e){
            $this->deleteImg($category);
        }
        return $category;
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        if (!in_array('ROLE_SUPER', $this->currentUserOfSession->getRoles())) {
            $this->session->getFlashBag()->add('error', 'У вас нет прав на удаление категорий');
        }
        elseif ($this->em->getRepository(Category::class)->findOneBy(['id' => $id])->getPosts()[0]){
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