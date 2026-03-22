<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestType;
use App\Form\LoginFormType;
use App\Repository\UsersRepository;
use App\Service\JWTService;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class, [
            'username' => $lastUsername
        ]);

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Intercepté par le firewall
    }

    #[Route(path: '/mot-de-passe-oublie', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        JWTService $jwtService,
        SendEmailService $sendEmailService
    ): Response {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $username = $form->get('username')->getData();

            $user = $usersRepository->findOneBy([
                'username' => mb_strtolower($username)
            ]);

            if ($user) {

                $header = [
                    'alg' => 'HS256',
                    'typ' => 'JWT'
                ];

                $payload = [
                    'user_id' => $user->getId(),
                ];

                $token = $jwtService->generate(
                    $header,
                    $payload,
                    $this->getParameter('app.jwtsecret')
                );

                $url = $this->generateUrl(
                    'reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $sendEmailService->send(
                    'no-reply@snowtricks.test',
                    $user->getEmail(), // toujours email en base
                    'Récupération de votre mot de passe',
                    'reset_password',
                    compact('user', 'url')
                );
            }

            // Message identique dans tous les cas
            $this->addFlash(
                'success',
                'Si un compte correspond à ce nom d’utilisateur, un email a été envoyé.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPasswordForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/mot-de-passe-oublie/{token}', name: 'reset_password')]
    public function resetPassword(
        $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManagerInterface
    ): Response {

        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {

            $payload = $jwt->getPayload($token);

            $user = $usersRepository->find($payload['user_id']);

            if ($user) {
                $form = $this->createForm(ResetPasswordFormType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $user->setPassword(
                        $passwordHasher->hashPassword(
                            $user,
                            $form->get('password')->getData()
                        )
                    );

                    $entityManagerInterface->flush();

                    $this->addFlash('success', 'Mot de passe mis à jour avec succès ! Vous pouvez maintenant vous connecter.');
                    return $this->redirectToRoute('app_login');
                }

                return $this->render('security/reset_password.html.twig', [
                    'passForm' => $form->createView(),
                ]);
            }
        }

        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
