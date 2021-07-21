<?php


namespace App\Controller\Admin;


use App\Entity\User;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * UserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/admin/user", name="admin_user")
     * @return Response
     */
    public function indexAction(){
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Пользователи';
        $forRender['users'] = $this->userService->getAllEntities();
        $forRender['cratedEntityId'] = $this->userService->getCratedEntityId();
        return $this->render('admin/user/index.html.twig', $forRender);
    }

    /**
     * @Route("/admin/user/create", name="admin_user_create")
     * @return RedirectResponse|Response
     */

    public function createAction(){
        $user = new User();
        $form = $this->userService->createForm($user);
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->userService->prepareEntity($user, $form);
            $this->userService->save($user);
            return $this->redirectToRoute('admin_user', ['cratedEntityId' => $user->getId()]);

        }
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Форма саздания пользователя';
        $forRender['form'] =  $form->createView();

        return $this->render('admin/user/form.html.twig', $forRender);
    }

    /**
     * @Route("/admin/user/update/{user_id}", name="admin_user_update")
     * @param int $user_id
     * @return RedirectResponse|Response
     */
    public function updateAction(int $user_id)
    {
        $user = $this->userService->getEntity($user_id);
        $form = $this->userService->createForm($user);

        if ($form->isSubmitted() && $form->isValid()){

            if ($form->get('save')->isClicked())
            {
                $this->userService->prepareEntity($user, $form);
                $this->userService->save($user);
            }
            elseif ($form->get('delete')->isSubmitted())
            {
                return $this->deleteAction($user->getId());
            }
            return $this->redirectToRoute('admin_user');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование пользователя';
        $forRender['form'] = $form->createView();
        return $this->render('admin/user/form.html.twig', $forRender);
    }

    /**
     * @Route("/admin/user/delete/{user_id}", name="admin_user_delete")
     * @param int $user_id
     * @return Response
     */
    public function deleteAction(int $user_id): Response
    {
        return $this->userService->delete($user_id);
    }
}