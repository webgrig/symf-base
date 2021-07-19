<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Form\EventListener\User\UserSubscriber;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{

    /**
     * @var UserRepositoryInterface
     */
    private $entityManager;

    /**
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('img', FileType::class, [
                'label' => 'Аватар',
                'required' => false,
                'mapped' => false,

            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])

            ->add('full_name', TextType::class, [
                'required' => false,
                'label' => 'ФИО',
                'attr' => [
                    'placeholder' => 'Введите ФИО'
                ]
            ])
            ->add('roles_collection', EntityType::class, [
                    'required' => false,
                    'label' => 'Роли',
                    'class' => Role::class,
//                    'choices' => $this->entityManager->getRepository(Role::class)->findAll(),
                    'choice_label' => 'title',
                    'multiple' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'This cannot be empty']),
                    ]
                ]
            )
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Пароль',
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Подтвердить пароль',
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ]
            ])
            ->add('is_verified', CheckboxType::class, [
                'label' => 'Verified',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Создать'
            ])
        ;
        $builder->addEventSubscriber(new UserSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
