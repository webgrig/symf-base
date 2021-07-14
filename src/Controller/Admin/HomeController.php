<?php


namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    /**
     * @Route("/admin", name="admin_home")
     * @return Response
     */
    public function indexAction(){
        $forRender = parent::renderDefault();
        return $this->render('admin/index.html.twig', $forRender);
    }
}