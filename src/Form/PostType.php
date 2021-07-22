<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\EventListener\PostSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок поста',
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
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image file',
                    ])
                ],

            ])

            ->add('categories', EntityType::class, [
                    'required' => false,
                    'label' => 'Категории',
                    'class' => Category::class,
                    'choice_label' => 'title',
                    'choices' => $options['categories'],
//                    'placeholder' => 'Выбрать категорию',
                    'multiple' => true,
//                    'error_bubbling' => true
                ]
            )
            ->add('content', TextareaType::class, [
                'label' => 'Описание поста',
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
        $builder->addEventSubscriber(new PostSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'categories' => NULL,
        ]);
    }
}
