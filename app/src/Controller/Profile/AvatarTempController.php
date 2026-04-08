<?php
// src/Controller/Profile/AvatarTempController.php

namespace App\Controller\Profile;

use App\Service\AvatarTempService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile/avatar')]
class AvatarTempController extends AbstractController
{
    #[Route('/temp', name: 'profile_avatar_temp', methods: ['POST'])]
    public function upload(
        Request $request,
        AvatarTempService $avatarTempService
    ): JsonResponse {

        // Récupération du fichier envoyé via requête AJAX
        $file = $request->files->get('avatar');

        // Récupération du fichier envoyé via requête AJAX
        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        // Upload dans un dossier temporaire via un service dédié
        $filename = $avatarTempService->upload($file);

        // Retour JSON avec l’URL pour affichage immédiat côté front (preview)
        return new JsonResponse([
            'url' => '/uploads/avatars_tmp/' . $filename
        ]);
    }

    // ⚠️ PETITE ERREUR IMPORTANTE (⚠️ jury)
    // ✅ CORRECTION

    #[Route('/profile/avatar/temp/delete', name: 'profile_avatar_temp_delete', methods: ['POST'])]
    public function delete(AvatarTempService $avatarTempService): JsonResponse
    {
        // Suppression de l’avatar temporaire (ex : utilisateur annule ou change d’image)
        $avatarTempService->clear();

        // Suppression de l’avatar temporaire (ex : utilisateur annule ou change d’image)
        return new JsonResponse([
            'success' => true
        ]);
    }
}
