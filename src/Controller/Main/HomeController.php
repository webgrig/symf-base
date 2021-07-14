<?php


namespace App\Controller\Main;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    /**
     * @Route("/", name="home")
     * @return resource
     */
    public function index(){
        $forRender = parent::renderDefault();
        $forRender['words'] = ['sky', 'cloud', 'wood', 'rock', 'forest',
            'mountain', 'breeze'];
        return $this->render('main/index.html.twig', $forRender);
    }
}