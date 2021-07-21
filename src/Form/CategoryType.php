<?php

namespace App\Form;

use App\Entity\Category;
use App\Form\EventListener\Category\CategorySubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('img', FileType::class, [
                'label' => 'Главное изображение',
                'required' => false,
                'mapped' => false,

            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок категории',
                'attr' => [
                    'placeholder' => 'Введите текст'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание категории',
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
                    'class' => 'btn btn-primary float-left'
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
