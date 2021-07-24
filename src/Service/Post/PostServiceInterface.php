<?php

namespace App\Service\Post;

use App\Entity\Post;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface PostServiceInterface
{
    /**
     * @param Post $post
     * @return FormInterface
     */
    public function createForm(Post $post): object;

    /**
     * @return Post[]
     */
    public function getAll(): array;

    /**
     * @return bool
     */
    public function checkAvailableCategories(): bool;

    /**
     * @param int $id
     * @return Post
     */
    public function getOne(int $id): object;

    /**
     * @param Post $post
     * @return Post
     */
    public function saveImg(Post $post): object;

    /**
     * @param Post $post
     */
    public function deleteImg(Post $post): void;

    /**
     * @param Post $post
     * @return Post
     */
    public function save(Post $post): object;

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse;
}