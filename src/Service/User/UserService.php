<?php


namespace App\Service\User;


use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
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
        RouterInterface $router
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->router = $router;

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
     * @param array|null $roles
     */
    public function prepareEntity(User $user, Form $form, bool $isVerified = false, array $roles = NULL): void
    {
        if ($form->get('plainPassword')->getData()) {
            $password = $this->passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);
        }

        if (null !== $isVerified) {
            $user->setIsVerified($isVerified);
        }

        if (null !== $roles) {
            $user->setRoles($roles);
        }
    }

    /**
     * @param User $user
     * @return User
     */
    public function save(User $user): object
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    /**
     * @param $id
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $session = $request->getSession();
        $user = $this->entityManager->getRepository(User::class)->findOne($id);
        if (!in_array('ROLE_SUPER', $user->getRoles())) {
            $session->getFlashBag()->add('error', 'У вас нет прав на удаление пользователей');
        } elseif ($user->getId() == $id) {
            $session->getFlashBag()->add('error', 'Вы не можете удалить сами себя');
        } elseif (in_array('ROLE_SUPER', $user->getRoles())) {
            $session->getFlashBag()->add('error', 'Невозможно удалить Супер-Админа');
        } else {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $session->getFlashBag()->add('error', 'Пользователь удален');
        }
        return new RedirectResponse($this->router->generate('admin_user'));
    }
}