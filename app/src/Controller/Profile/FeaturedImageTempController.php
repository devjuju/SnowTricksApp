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
        $file = $request->files->get('featuredImage');

        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        $filename = $featuredImageTempService->upload($file);

        return new JsonResponse([
            'url' => '/uploads/featured_images_tmp/' . $filename
        ]);
    }

    #[Route('/profile/featured-image/temp/delete', name: 'profile_featured_image_temp_delete', methods: ['POST'])]
    public function delete(FeaturedImageTempService $featuredImageTempService): JsonResponse
    {
        $featuredImageTempService->clear();

        return new JsonResponse([
            'success' => true
        ]);
    }
}
