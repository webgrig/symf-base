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
    private $currentUserOfSession;

    private $request;

    private $session;

    private $form;

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
        RequestStack $requestStack
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $entityManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->fm = $fileManagerService;
        $this->currentUserOfSession = $tokenStorage->getToken()->getUser();
        $this->request = $requestStack->getMainRequest();
        $this->session = $this->request->getSession();

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
     * @param User $user
     */
    public function prepareEntity(User $user): void
    {
        if (null !== $this->form->get('plainPassword')->getData()) {
            $password = $this->passwordEncoder->encodePassword($user, $this->form->get('plainPassword')->getData());
            $user->setPassword($password);
        }


        if (null !== $this->form->get('roles_collection')->getData()) {
            $this->addRolesCollection($user);
        }
    }


    /**
     * @param User $user
     * @return Role
     */
    public function addRolesCollection(User $user): object
    {

        if (null == $user->getId()){
            if (null == $user->getRolesCollection()){
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
            $this->fm->removeImage($userImg, $user->getStorageDirName());
        }
    }


    /**
     * @param User $user
     * @return object
     */
    public function saveUser(User $user): object
    {
        if (null !== $file = $this->form->get('img')->getData()){
            $this->deleteImg($user);
            $user->setImg($this->fm->imageUpload($file, $user->getStorageDirName()));
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

    /**
     * @param int $id
     * @return Response
     */
    public function deleteUser(int $id): Response
    {
        $user = $this->em->getRepository(User::class)->findOne($id);
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