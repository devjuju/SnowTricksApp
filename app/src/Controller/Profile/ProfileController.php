<?php

namespace App\Controller\Profile;

use App\Repository\TricksRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\ProfileFormType;
use App\Service\AvatarTempService;
use App\Service\AvatarUploaderService;

#[Route('/profile', name: 'app_profile_')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, TricksRepository $tricksRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $tricks = $tricksRepository->findByTrickPaginated($page, $limit);

        if ($request->isXmlHttpRequest()) {

            $html = $this->renderView('_partials/tricks.html.twig', [
                'tricks' => $tricks
            ]);

            $offset = ($page - 1) * $limit;

            // ✅ CORRECTION ICI
            $totalTricks = $tricksRepository->count([]);

            $hasMore = ($offset + $limit) < $totalTricks;

            return $this->json([
                'html' => $html,
                'hasMore' => $hasMore
            ]);
        }

        // Page classique
        $totalTricks = $tricksRepository->count([]);

        // ----------------------------
        // Page classique
        // ----------------------------
        return $this->render('profile/profile/index.html.twig', [
            'tricks' => $tricks,
            'page' => $page,
            'totalTricks' => $totalTricks,
            'limit' => $limit,
        ]);
    }


    #[Route('/edit', name: 'edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AvatarUploaderService $avatarUploaderService,
        AvatarTempService $avatarTempService
    ): Response {
        /** @var \App\Entity\Users $user */
        $user = $this->getUser();

        if (!$request->isMethod('POST')) {
            $avatarTempService->clear();
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Intention utilisateur : suppression avatar
            if ($form->get('deleteAvatar')->getData()) {
                if ($user->getAvatar()) {
                    $avatarUploaderService->delete($user->getAvatar());
                    $user->setAvatar(null);
                }

                if ($avatarTempService->get()) {
                    $avatarTempService->clear();
                }
            }

            // Validation OK → actions définitives
            if ($form->isValid()) {
                // -----------------
                // Mot de passe
                // -----------------
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }

                // -----------------
                // Suppression avatar
                // -----------------
                if ($form->get('deleteAvatar')->getData()) {
                    if ($user->getAvatar()) {
                        $avatarUploaderService->delete($user->getAvatar());
                        $user->setAvatar(null);
                    }
                    // Si un avatar temporaire existe, on le supprime aussi
                    if ($avatarTempService->get()) {
                        $avatarTempService->clear();
                    }
                }

                // -----------------
                // Avatar upload (temp -> final)
                // -----------------
                $tempAvatar = $avatarTempService->get();
                if ($tempAvatar && !$form->get('deleteAvatar')->getData()) { // uniquement si on ne supprime pas
                    if ($user->getAvatar()) {
                        $avatarUploaderService->delete($user->getAvatar());
                    }
                    $avatarTempService->moveToFinal($tempAvatar);
                    $user->setAvatar($tempAvatar);
                }

                // -----------------
                // Enregistrement
                // -----------------
                $em->flush();

                $this->addFlash('success', 'Profil mis à jour avec succès !');

                return $this->redirectToRoute('app_profile_index');
            }
        }

        return $this->render('profile/profile/edit.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user,
            'tempAvatar' => $avatarTempService->get(),
        ]);
    }
}
