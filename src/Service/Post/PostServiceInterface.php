<?php

namespace App\Service\Post;

use App\Entity\Post;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

interface PostServiceInterface
{
    /**
     * @param Post $post
     * @return Form
     */
    public function createForm(Post $post): object;

    /**
     * @return Post[]|mixed|object[]
     */
    public function getAll();

    /**
     * @return bool
     */
    public function countAvailableCategories(): bool;

    /**
     * @param int $id
     * @return Post
     */
    public function getEntity(int $id): object;

    /**
     * @param Post $post
     * @return Post
     */
    public function saveImg(Post $post);

    /**
     * @param Post $post
     */
    public function deleteImg(Post $post): void;

    /**
     * @param Post $post
     * @return $this
     */
    public function save(Post $post);

    /**
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response;
}