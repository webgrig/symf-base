<?php

namespace App\Controller\API;


use App\Service\API\ApiPostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiPostController extends AbstractController
{
    /**
     * @var ApiPostService
     */
    private $apiPostService;

    public function __construct(ApiPostService $apiPostService)
    {
        $this->apiPostService = $apiPostService;
    }

    /**
     * @Route("/api/post", name="api_post", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request): JsonResponse
    {
        $response = $this->apiPostService->getPostsCategory($request);
        return $this->json($response);
    }
}
