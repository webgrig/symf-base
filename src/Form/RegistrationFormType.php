<?php

namespace App\Form;

use App\Entity\User;
use App\Form\EventListener\Registration\RegistrationSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

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
            ->add('agreeTermsCheckBox', CheckboxType::class, [
                'label' => 'Agree Terms',
                'required' => false,
                'mapped' => false,
                'row_attr' => [
                    'class' => 'float-left'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('agreeLinkButton', SubmitType::class, [
                'label' => 'Соглашение',
                'attr' => [
                    'class' => 'btn-inline agree-link'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Зарегистрироваться',
                'attr' => [
                    'class' => 'btn btn-block btn-primary mt-3'
                ]
            ])
            ->add('agreeTermsAgreeButton', SubmitType::class, [
                'label' => 'Согласен',
                'attr' => [
                    'class' => 'btn btn-primary mt-3'
                ]
            ])
            ->add('agreeTermsRefuseButton', SubmitType::class, [
                'label' => 'Отказываюсь',
                'attr' => [
                    'class' => 'btn btn-danger mt-3 ml-3 float-right'
                ]
            ])
        ;
//        $builder->addEventSubscriber(new RegistrationSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
