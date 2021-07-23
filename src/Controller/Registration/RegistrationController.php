<?php

namespace App\Controller\Registration;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;
use App\Security\EmailVerifier;
use App\Service\User\UserService;
use App\Service\User\UserServiceInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(EmailVerifier $emailVerifier, UserServiceInterface $userService)
    {
        $this->emailVerifier = $emailVerifier;
        $this->userService = $userService;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_home');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->get('agreeTermsRefuseButton')->isClicked()) {
            return $this->redirectToRoute('home');
        }
        $agreeTermsAgree = $form->get('agreeTermsAgreeButton')->isClicked()?'hide-row':false;
        $hideFormBodyCssClass = $form->get('agreeLinkButton')->isClicked()?'hide-row':"";
        $hideTermsBodyCssClass = !$form->get('agreeLinkButton')->isClicked()?'hide-row':"";

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setIsVerified(false);
            $this->userService->updateRolesCollection($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('send-confirmation', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('registration/index.html.twig', [
            'agreeTermsAgree' => $agreeTermsAgree,
            'hideFormBodyCssClass' => $hideFormBodyCssClass,
            'hideTermsBodyCssClass' => $hideTermsBodyCssClass,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/send-confirmation/{id}/{force?}", name="send-confirmation")
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @return Response
     */
    public function sendConfirmation(Request $request, UserRepositoryInterface $userRepository): Response
    {

        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user->getId()) {
            return $this->redirectToRoute('app_register');
        }
        if(!$user->isVerified() || $request->get('force')){
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
        if ($request->get('force')){
            return $this->redirectToRoute('admin_user', ['cratedEntityId' => $user->getId()]);
        }

        return $this->render('registration/confirm-page.html.twig', ['id' => $user->getId()]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepositoryInterface $userRepository): Response
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
