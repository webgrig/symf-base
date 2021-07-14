<?php


namespace App\Repository;


use App\Entity\Post;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface PostRepositoryInterface
{
    /**
     * @param Post $post
     * @param UploadedFile $file
     * @return Post
     */
    public function setCreatePost(Post $post, UploadedFile $file): self;

    /**
     * @param Post $post
     * @param UploadedFile $file
     * @return Post
     */
    public function setSavePost(Post $post, UploadedFile $file): self;

}