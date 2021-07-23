<?php

namespace App\Form;

use App\Entity\Category;
use App\Form\EventListener\CategorySubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок категории',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Введите текст'
                ]
            ])
            ->add('img', FileType::class, [
                'label' => 'Главное изображение',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2000k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image file',
                    ])
                ],

            ])

            ->add('description', TextareaType::class, [
                'label' => 'Описание категории',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Введите описание'
                ]
            ])
            ->add('is_published', CheckboxType::class, [
                'label' => 'Published',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
        $builder->addEventSubscriber(new CategorySubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
