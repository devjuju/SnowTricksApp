<?php

namespace App\Controller\Profile;

use App\Repository\TricksRepository;
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
        // Pagination sécurisée
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Récupération paginée des tricks
        $tricks = $tricksRepository->findByTrickPaginated($page, $limit);

        // ==========================
        // ⚡ AJAX - LOAD MORE TRICKS
        // ==========================
        if ($request->isXmlHttpRequest()) {

            // Rendu partiel HTML injecté côté front
            $html = $this->renderView('_partials/profile/tricks/item.html.twig', [
                'tricks' => $tricks
            ]);

            // Calcul pagination
            $offset = ($page - 1) * $limit;
            $totalTricks = $tricksRepository->count([]);

            $hasMore = ($offset + $limit) < $totalTricks;

            // Réponse JSON pour JS
            return $this->json([
                'html' => $html,
                'hasMore' => $hasMore
            ]);
        }

        // Nombre total pour affichage / pagination
        $totalTricks = $tricksRepository->count([]);

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

        // Nettoyage avatar temporaire si accès hors POST (évite fichiers orphelins)
        if (!$request->isMethod('POST')) {
            $avatarTempService->clear();
        }

        // Formulaire de modification du profil
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // ==========================
            // 🗑️ SUPPRESSION AVATAR (intention utilisateur)
            // ==========================
            if ($form->get('deleteAvatar')->getData()) {
                if ($user->getAvatar()) {
                    $avatarUploaderService->delete($user->getAvatar());
                    $user->setAvatar(null);
                }

                // Nettoyage du temporaire
                if ($avatarTempService->get()) {
                    $avatarTempService->clear();
                }
            }

            // ==========================
            // ✅ VALIDATION FORMULAIRE
            // ==========================
            if ($form->isValid()) {

                // -----------------
                // 🔐 Mise à jour mot de passe
                // -----------------
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }

                // -----------------
                // 🗑️ Suppression avatar (sécurisée)
                // -----------------
                if ($form->get('deleteAvatar')->getData()) {
                    if ($user->getAvatar()) {
                        $avatarUploaderService->delete($user->getAvatar());
                        $user->setAvatar(null);
                    }

                    if ($avatarTempService->get()) {
                        $avatarTempService->clear();
                    }
                }

                // -----------------
                // 📸 Upload avatar (temp → final)
                // -----------------
                $tempAvatar = $avatarTempService->get();

                // Si un avatar temporaire existe et qu’on ne le supprime pas
                if ($tempAvatar && !$form->get('deleteAvatar')->getData()) {

                    // Suppression ancien avatar
                    if ($user->getAvatar()) {
                        $avatarUploaderService->delete($user->getAvatar());
                    }

                    // Déplacement vers stockage final
                    $avatarTempService->moveToFinal($tempAvatar);

                    $user->setAvatar($tempAvatar);
                }

                // -----------------
                // 💾 Sauvegarde
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
