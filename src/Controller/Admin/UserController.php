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

    /**
     * UserController constructor.
     * @param UserRepositoryInterface $userRepository
     * @param UserService $userService
     */
    public function __construct(UserRepositoryInterface $userRepository, UserService $userService)
    {
        $this->userRepository =  $userRepository;
        $this->userService = $userService;
    }

    /**
     * @Route("/admin/user", name="admin_user")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request){
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Пользователи';
        $forRender['users'] = $this->userRepository->getAll();
        $forRender['userCrateId'] = $request->get('userCrateId');
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
            $this->userService->saveUser($user);
            return $this->redirectToRoute('admin_user', ['userCrateId' => $user->getId()]);

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
        $user = $this->userRepository->findOne($user_id);
        $form = $this->userService->createForm($user);

        if ($form->isSubmitted() && $form->isValid()){

            if ($form->get('save')->isClicked())
            {
                $this->userService->prepareEntity($user, $form);
                $this->userService->saveUser($user);
            }
            if ($form->get('delete')->isClicked())
            {
                return $this->deleteAction($user->getId());
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
     * @Route("/admin/user/delete/{user_id}", name="admin_user_delete")
     * @param int $user_id
     * @return Response
     */
    public function deleteAction(int $user_id): Response
    {
        return $this->userService->deleteUser($user_id);
    }
}