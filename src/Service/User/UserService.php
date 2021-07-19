<?php


namespace App\Service\User;


use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepositoryInterface;
use App\Service\FileManagerServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    private $formFactory;

    private $router;

    private $fm;

    private $user;

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
        FileManagerServiceInterface $fileManagerService,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->fm = $fileManagerService;
        $this->user = $tokenStorage->getToken()->getUser();

    }

    /**
     * @param Request $request
     * @param User $user
     * @return Form
     */

    public function createForm(Request $request, User $user): object
    {
        $form = $this->formFactory->create(UserType::class, $user);
        $form->handleRequest($request);
        return $form;
    }

    /**
     * @param User $user
     * @param Form $form
     * @param bool $isVerified
     */
    public function prepareEntity(User $user, Form $form, string $storageDirName, bool $isVerified = false): void
    {
        if (null !== $file = $form->get('img')->getData()){
            $this->deleteImg($user, $storageDirName);
            $user->setImg($this->fm->imageUpload($file, $storageDirName));
        }
        if ($form->get('plainPassword')->getData()) {
            $password = $this->passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);
        }

        if (null !== $isVerified) {
            $user->setIsVerified($isVerified);
        }
        $this->addRolesCollection($user);
    }


    /**
     * @param User $user
     */
    public function addRolesCollection(User $user): void
    {
        if (null == $user->getId()){
            $user->setRoles();
            $roles =  $user->getRoles();
            $roles_collection = $this->entityManager->getRepository(Role::class)->findBy(['title' => $roles]);
            foreach ($roles_collection as $role)
            {
                $user->addRolesCollection($role);
            }
        }
        else{
            $roles_collection = $user->getRolesCollection();
            $roles = [];
            foreach ($roles_collection as $role){
                $roles[] = $role->getTitle();
            }
            $user->setRoles($roles);
        }

    }

    /**
     * @param User $user
     * @param string $storageDirName
     */
    public function deleteImg(User $user, string $storageDirName): void
    {
        if (null !== $userImg = $user->getImg()){
            $this->fm->removeImage($userImg, $storageDirName);
        }
    }


    /**
     * @param User $user
     * @param UploadedFile $file
     * @return object
     */
    public function save(User $user): object
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    /**
     * @param Request $request
     * @param int $id
     * @param string $storageDirName
     * @return Response
     */
    public function delete(Request $request, int $id, string $storageDirName): Response
    {
        $session = $request->getSession();
        $user = $this->entityManager->getRepository(User::class)->findOne($id);
        if (!in_array('ROLE_SUPER', $this->user->getRoles())) {
            $session->getFlashBag()->add('error', 'У вас нет прав на удаление пользователей');
        } elseif ($this->user->getId() == $id) {
            $session->getFlashBag()->add('error', 'Вы не можете удалить сами себя');
        } elseif (in_array('ROLE_SUPER', $user->getRoles())) {
            $session->getFlashBag()->add('error', 'Невозможно удалить Супер-Админа');
        } else {
            $this->deleteImg($user, $storageDirName);
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $session->getFlashBag()->add('error', 'Пользователь удален');
        }
        return new RedirectResponse($this->router->generate('admin_user'));
    }
}