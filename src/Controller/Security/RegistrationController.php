<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use function Symfony\Component\String\u;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
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
        $agreeTerm = "hide";
        $agreeTermAgree = false;
        if ($form->get('agreeTermRefuse')->isClicked()) {
            return $this->redirectToRoute('home');
        }
        if ($form->get('agreeTermAgree')->isClicked()) {
            $agreeTermAgree = true;
        }
        if ($form->get('agreeLink')->isClicked())
        {
            $agreeTerm = "show";
        }


        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('send-confirmation', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'agreeTerm' => $agreeTerm,
            'agreeTermAgree' => $agreeTermAgree,
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
