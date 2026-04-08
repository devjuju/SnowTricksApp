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
        // Redirection si l'utilisateur est déjà authentifié
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile_index');
        }

        // Récupération des erreurs de connexion (Symfony Security)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier username saisi (UX utilisateur)
        $lastUsername = $authenticationUtils->getLastUsername();

        // Pré-remplissage du formulaire de login
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
        // Géré automatiquement par le firewall Symfony (aucune logique nécessaire ici)
    }

    #[Route(path: '/mot-de-passe-oublie', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        JWTService $jwtService,
        SendEmailService $sendEmailService
    ): Response {

        // Formulaire de demande de réinitialisation de mot de passe
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $username = $form->get('username')->getData();

            // Recherche utilisateur en base (insensible à la casse)
            $user = $usersRepository->findOneBy([
                'username' => mb_strtolower($username)
            ]);

            if ($user) {

                // ==========================
                // 🔐 GÉNÉRATION DU TOKEN JWT
                // ==========================

                $header = [
                    'alg' => 'HS256',
                    'typ' => 'JWT'
                ];

                $payload = [
                    'user_id' => $user->getId(),
                ];

                // Token signé pour sécuriser la réinitialisation
                $token = $jwtService->generate(
                    $header,
                    $payload,
                    $this->getParameter('app.jwtsecret')
                );

                // Génération du lien absolu de reset password
                $url = $this->generateUrl(
                    'reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // Envoi de l'email contenant le lien sécurisé
                $sendEmailService->send(
                    'no-reply@snowtricks.test',
                    $user->getEmail(),
                    'Récupération de votre mot de passe',
                    'reset_password',
                    compact('user', 'url')
                );
            }

            // Message volontairement générique pour éviter l’énumération de comptes
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

        // ==========================
        // 🔐 VALIDATION DU TOKEN JWT
        // ==========================

        if (
            $jwt->isValid($token) &&
            !$jwt->isExpired($token) &&
            $jwt->check($token, $this->getParameter('app.jwtsecret'))
        ) {

            $payload = $jwt->getPayload($token);

            // Récupération utilisateur lié au token
            $user = $usersRepository->find($payload['user_id']);

            if ($user) {

                // Formulaire de nouveau mot de passe
                $form = $this->createForm(ResetPasswordFormType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    // Hash du nouveau mot de passe (sécurité Symfony)
                    $user->setPassword(
                        $passwordHasher->hashPassword(
                            $user,
                            $form->get('password')->getData()
                        )
                    );

                    $entityManagerInterface->flush();

                    $this->addFlash(
                        'success',
                        'Mot de passe mis à jour avec succès ! Vous pouvez maintenant vous connecter.'
                    );

                    return $this->redirectToRoute('app_login');
                }

                return $this->render('security/reset_password.html.twig', [
                    'passForm' => $form->createView(),
                ]);
            }
        }

        // Cas d’erreur : token invalide ou expiré
        $this->addFlash('danger', 'Le token est invalide ou a expiré');

        return $this->redirectToRoute('app_login');
    }
}
