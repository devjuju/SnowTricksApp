<?php
// src/Controller/Profile/AvatarTempController.php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\FeaturedImageTempService;

#[Route('/profile/featured-image')]
class FeaturedImageTempController extends AbstractController
{
    #[Route('/temp', name: 'profile_featured_image_temp', methods: ['POST'])]
    public function upload(
        Request $request,
        FeaturedImageTempService $featuredImageTempService
    ): JsonResponse {

        // Récupération du fichier envoyé en AJAX (image principale du trick)
        $file = $request->files->get('featuredImage');

        // Vérification : aucun fichier → retour erreur HTTP 400
        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        // Upload dans un dossier temporaire via un service dédié
        $filename = $featuredImageTempService->upload($file);

        // Retour de l’URL pour affichage immédiat côté front (preview)
        return new JsonResponse([
            'url' => '/uploads/featured_images_tmp/' . $filename
        ]);
    }

    #[Route('/profile/featured-image/temp/delete', name: 'profile_featured_image_temp_delete', methods: ['POST'])]
    public function delete(FeaturedImageTempService $featuredImageTempService): JsonResponse
    {
        // Suppression de l’image temporaire (ex : utilisateur change ou annule)
        $featuredImageTempService->clear();

        // Confirmation côté front
        return new JsonResponse([
            'success' => true
        ]);
    }
}
