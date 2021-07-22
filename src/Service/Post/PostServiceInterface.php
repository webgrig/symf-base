<?php

namespace App\Service\Post;

use App\Entity\Post;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

interface PostServiceInterface
{
    /**
     * @param Post $post
     * @return Form
     */
    public function createForm(Post $post): object;

    /**
     * @return Post[]|mixed|object[]|RedirectResponse
     */
    public function getAll();

    /**
     * @param int $id
     * @return Post
     */
    public function getEntity(int $id): object;

    /**
     * @param Post $post
     */
    public function deleteImg(Post $post): void;

    /**
     * @param Post $post
     * @return $this|string
     */
    public function save(Post $post);

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response;
}