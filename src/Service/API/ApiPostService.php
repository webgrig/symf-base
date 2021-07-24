<?php


namespace App\Service\API;


use App\Entity\Post;
use App\Repository\PostRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiPostService
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @param Request $request
     * @return Post[]
     */
    public function getPostsCategory(Request $request): array
    {
        $categoryId = $request->get('categoryId');
        return $this->postRepository->getPostsCategory($categoryId);
    }


}