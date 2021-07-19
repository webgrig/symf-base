<?php


namespace App\Controller\Admin;


use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserRepositoryInterface $userRepository, UserService $userService)
    {
        $this->userRepository =  $userRepository;
        $this->userService = $userService;
    }

    /**
     * @Route("/admin/user", name="admin_user")
     * @return Response
     */
    public function indexAction(){
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Пользователи';
        $forRender['users'] = $this->userRepository->getAll();
        return $this->render('admin/user/index.html.twig', $forRender);
    }

    /**
     * @Route("/admin/user/create", name="admin_user_create")
     * @param Request $request
     * @param array $options
     * @return RedirectResponse|Response
     */

    public function createAction(Request $request){
        $user = new User();
        $form = $this->userService->createForm($request, $user);
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->userService->prepareEntity($user, $form, 'user', true);
            $this->userService->save($user);
            $this->addFlash('success', 'Пользователь создан');
            return $this->redirectToRoute('admin_user');

        }
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Форма саздания пользователя';
        $forRender['form'] =  $form->createView();

        return $this->render('admin/user/form.html.twig', $forRender);
    }

    /**
     * @Route("/admin/user/update/{id}", name="admin_user_update")
     * @param Request $request
     * @param int $userId
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, int $id)
    {
        $user = $this->userRepository->findOne($id);
        $form = $this->userService->createForm($request, $user);

        if ($form->isSubmitted() && $form->isValid()){

            if ($form->get('save')->isClicked())
            {
                $this->userService->prepareEntity($user, $form, 'user');
                $this->userService->save($user);
                $this->addFlash('success', 'Изменения сохранены');
            }
            if ($form->get('delete')->isClicked())
            {
                return $this->delete($request, $user->getId());
            }
            return $this->redirectToRoute('admin_user');
        }

        $forRender = parent::renderDefault();
        $forRender['title'] = 'Редактирование пользователя';
        $forRender['form'] = $form->createView();
        $forRender['deleteButton'] = true;
        return $this->render('admin/user/form.html.twig', $forRender);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="admin_user_delete")
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        return $this->userService->delete($request, $id, 'user');
    }
}