<?php


namespace App\Service\User;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
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
     * @param array $additionalFields
     * @return Form
     */

    public function createForm(Request $request, User $user, array $additionalFields): object
    {
        $form = $this->formFactory->create(UserType::class, $user);
        if (isset($additionalFields['roles'])) {
            $allRoles = $this->entityManager->getRepository(User::class)->findAllRoles()[0]->getRoles();
            $form->add('selectUser', ChoiceType::class, [
                    'label' => 'Роли',
                    'choices' => $allRoles,
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => true
                ]
            );
        }
        if (isset($additionalFields['agreeTerms'])) {

            $form->add('agreeTerms', CheckboxType::class, [
                'label' => isset($additionalFields['agreeTerms']['label']) ? $additionalFields['agreeTerms']['label'] : 'Terms',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'attr' => [
                    'class' => isset($additionalFields['agreeTerms']['class']) ? $additionalFields['agreeTerms']['class'] : ''
                ]
            ]);
        }
        if (isset($additionalFields['save'])) {
            $form->add('save', SubmitType::class, [
                'label' => isset($additionalFields['save']['label']) ? $additionalFields['save']['label'] : 'Создать',
                'attr' => [
                    'class' => isset($additionalFields['save']['class']) ? $additionalFields['save']['class'] : 'btn btn-primary mt-3 mb-3 float-left'
                ]
            ]);
        }
        if (isset($additionalFields['delete'])) {
            $form->add('delete', SubmitType::class, [
                'label' => isset($additionalFields['delete']['label']) ? $additionalFields['delete']['label'] : 'Удалить',
                'attr' => [
                    'class' => isset($additionalFields['delete']['class']) ? $additionalFields['delete']['class'] : 'btn btn-danger ml-3 mt-3 mb-3'
                ]
            ]);
        }
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
        $password = $this->passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData());
        $user->setPassword($password);

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