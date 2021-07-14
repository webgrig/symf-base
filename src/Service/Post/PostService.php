<?php


namespace App\Service\Post;


use App\Entity\Post;
use App\Repository\PostRepositoryInterface;
use Symfony\Component\Form\FormInterface;

class PostService
{
    public function __construct(PostRepositoryInterface $postRepository)
    {
    }

    /**
     * @param FormInterface $form
     * @param Post $post
     */
    public function handleCreatePost(FormInterface $form, Post $post)
    {
    }
}