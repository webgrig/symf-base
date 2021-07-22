<?php


namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PostSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $post = $event->getData();
        $form = $event->getForm();
        if (null !== $post->getId()) {
            $form

                ->add('save', SubmitType::class, [
                    'label' => 'Сохранить',
                    'row_attr' => [
                        'class' => 'float-left'
                    ],
                    'attr' => [
                        'class' => 'btn btn-primary mt-3 mb-3'
                    ]
                ])

                ->add('delete', ButtonType::class, [
                    'label' => 'Удалить',
                    'attr' => [
                        'class' => 'btn btn-danger ml-3 mt-3 mb-3',
                        'data-toggle' => 'modal',
                        'data-target' => '#confirmModal',
                        'data-entity-id' => $post->getId(),
                        'data-href' => '/admin/post/delete/' . $post->getId(),
                    ]
                ])
            ;
        }
    }
}