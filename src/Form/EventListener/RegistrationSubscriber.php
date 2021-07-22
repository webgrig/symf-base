<?php


namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::POST_SUBMIT => 'postSubmit'];
    }

    public function postSubmit(FormEvent $event): void
    {
//        $class= $event->getData();
//        $class->new_field = 'some_data';
//        $event->setData($class);
//        dd($event->getData());
        $form = $event->getForm();
        $row_attr = $form->setParent('agreeLinkButton')->getConfig()->set('row_attr');
        $row_attr['class'] = 'ssssss';

        dd($row_attr);
        if ($form->get('agreeLinkButton')->isClicked()) {
            $form

                ->add('email', EmailType::class, [
                    'required' => false,
                    'label' => 'Email',
                    'row_attr' => [
                        'class' => 'hide-row'
                    ]
                ])
                ->add('full_name', TextType::class, [
                    'required' => false,
                    'label' => 'ФИО',
                    'attr' => [
                        'placeholder' => 'Введите ФИО'
                    ],
                    'row_attr' => [
                        'class' => 'hide-row'
                    ]
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'required' => false,
                    'mapped' => false,
                    'row_attr' => [
                        'class' => 'hide-row'
                    ],
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
                        'class' => 'float-left hide-row'
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
                    ],
                    'row_attr' => [
                        'class' => 'hide-row'
                    ]
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Зарегистрироваться',
                    'attr' => [
                        'class' => 'btn btn-block btn-primary mt-3'
                    ],
                    'row_attr' => [
                        'class' => 'hide-row'
                    ]
                ])
                ->add('agreeTermsAgree', SubmitType::class, [
                    'label' => 'Согласен',
                    'attr' => [
                        'class' => 'btn btn-primary mt-3'
                    ]
                ])
                ->add('agreeTermsRefuse', SubmitType::class, [
                    'label' => 'Отказываюсь',
                    'attr' => [
                        'class' => 'btn btn-danger mt-3 ml-3 float-right'
                    ]
                ])
            ;
        }
    }
}