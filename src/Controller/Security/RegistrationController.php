<?php

namespace App\Controller\Security;

use App\Controller\Admin\UserController;
use App\Entity\User;
use App\Form\UserCreateType;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;
use App\Security\EmailVerifier;
use App\Service\User\UserService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * RegistrationController constructor.
     * @param EmailVerifier $emailVerifier
     * @param UserRepositoryInterface $userRepository
     * @param UserService $userService
     */
    public function __construct(EmailVerifier $emailVerifier, UserRepositoryInterface $userRepository, UserService $userService)
    {
        $this->emailVerifier = $emailVerifier;
        $this->userRepository =  $userRepository;
        $this->userService = $userService;
    }

    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_home');
        }
        $user = new User();
        $form = $this->userService->createForm($request, $user, [
            'agreeTerms' => true,
            'save' => [
                'label' => 'Зарегистрироваться',
                'class' => 'btn btn-block btn-primary mt-3'
            ]
        ]);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userService->prepareEntity($user, $form, false, ['ROLE_USER']);
            $this->userService->save($user);
            return $this->redirectToRoute('send-confirmation', [
                'id' => $user->getId()
            ]);
        }
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/send-confirmation/{id}", name="send-confirmation")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function sendConfirmation(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }
        if(!$user->isVerified()){
            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('mailer@gmail.com', 'Acme Mail Bot'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            $session = $request->getSession();
            $session->getFlashBag()->add('registration-success', 'A message has been sent to your email with a link to confirm registration. Check your email and confirm registration by clicking on this link.');
        }
        else{
            return $this->redirectToRoute('app_verify_email', ['id' => $user->getId()]);
        }

        return $this->render('registration/confirm-page.html.twig', ['id' => $user->getId()]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        if ($user->isVerified())
        {
            return $this->redirectToRoute('app_login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }
        $session = $request->getSession();
        $session->getFlashBag()->add('confirmation-success', 'Your email address has been verified. Now you can log in with your username and password');

        return $this->render('registration/confirm-page.html.twig');
    }
}
