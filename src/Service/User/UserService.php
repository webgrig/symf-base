<?php


namespace App\Service\User;


use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepositoryInterface;
use App\Service\File\FileManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    private $em;

    /**
     * @var FileManagerInterface
     */
    private $fm;


    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

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
    private $currentUserOfSession = null;

    private $request;

    private $session;

    private $form;

    private $userImgDirectory;

    /**
     * UserService constructor.
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        RouterInterface $router,
        FileManagerInterface $fileManagerService,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        $userImgDirectory
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $entityManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->fm = $fileManagerService;
        $this->request = $requestStack->getMainRequest();
        $this->session = $this->request->getSession();
        $this->userImgDirectory = $userImgDirectory;
        if (null !== $tokenStorage->getToken()){
            $this->currentUserOfSession = $tokenStorage->getToken()->getUser();
        }

    }

    /**
     * @param User $user
     * @return Form
     */

    public function createForm(User $user): object
    {
        $this->form = $this->formFactory->create(UserType::class, $user);
        $this->form->handleRequest($this->request);

        return $this->form;
    }

    /**
     * @return array
     */
    public function getAllEntities(): array
    {
        $users =  $this->em->getRepository(User::class)->findAll();
        if (!$users){
            $this->session->getFlashBag()->add('error', 'В настоящий момент нет ни одной статьи.');
        }
        return $users;
    }

    /**
     * @param int $id
     * @return User
     */

    public function getEntity(int $id) : object
    {
        return $this->em->getRepository(User::class)->find($id);
    }

    /**
     * @param User $user
     */
    public function prepareEntity(User $user): void
    {
        if (null !== $password = $user->getPlainPassword()) {
            $password = $this->passwordEncoder->encodePassword($user, $password);
            $user->setPassword($password);
        }
        $this->updateRolesCollection($user);
    }


    /**
     * @param User $user
     * @return Role
     */
    public function updateRolesCollection(User $user): object
    {
        if (null == $user->getId()){
            if (!count($user->getRolesCollection())){
                $user->setRoles();
                $roles =  $user->getRoles();
                $roles_collection = $this->em->getRepository(Role::class)->findBy(['title' => $roles]);
                foreach ($roles_collection as $role)
                {
                    $user->addRolesCollection($role);
                }
                return $user->getRolesCollection();
            }
        }
        $roles_collection = $user->getRolesCollection();
        $roles = [];
        foreach ($roles_collection as $role){
            $roles[] = $role->getTitle();
        }
        $user->setRoles($roles);
        return $user->getRolesCollection();
    }

    /**
     * @param User $user
     */
    public function deleteImg(User $user): void
    {
        if (null !== $userImg = $user->getImg()){
            $this->fm->remove($this->userImgDirectory, $userImg);
        }
    }


    /**
     * @param User $user
     * @return object
     */
    public function save(User $user): object
    {
        if (null !== $file = $this->form->get('img')->getData()){
            if ($file instanceof UploadedFile){
                $this->deleteImg($user);
                $user->setImg($this->fm->upload($this->userImgDirectory, $file));
            }
        }
        if (!$user->getId()){
            $this->session->getFlashBag()->add('success_create', 'Пользователь создан');
        }
        else{
            $this->session->getFlashBag()->add('success', 'Изменения сохранены');
        }
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function getCratedEntityId()
    {
        if (null !== $cratedEntityId = $this->request->get('cratedEntityId')){
            return $cratedEntityId;
        }
    }


    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!in_array('ROLE_SUPER', $this->currentUserOfSession->getRoles())) {
            $this->session->getFlashBag()->add('error', 'У вас нет прав на удаление пользователей');
        } elseif ($this->currentUserOfSession->getId() == $id) {
            $this->session->getFlashBag()->add('error', 'Вы не можете удалить сами себя');
        } elseif (in_array('ROLE_SUPER', $user->getRoles())) {
            $this->session->getFlashBag()->add('error', 'Невозможно удалить Супер-Админа');
        } else {
            $this->deleteImg($user);
            $this->em->remove($user);
            $this->em->flush();
            $this->session->getFlashBag()->add('error', 'Пользователь удален');
        }
        return new RedirectResponse($this->router->generate('admin_user'));
    }
}