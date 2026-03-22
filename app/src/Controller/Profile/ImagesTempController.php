<?php
// src/Controller/Profile/ImagesTempController.php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ImagesTempService;

#[Route('/profile/images')]
class ImagesTempController extends AbstractController
{
    #[Route('/temp', name: 'profile_images_temp', methods: ['POST'])]
    public function upload(Request $request, ImagesTempService $imagesTempService): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $files = $request->files->all('images');

        if (!$files) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        $uploaded = [];

        foreach ($files as $file) {

            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                return new JsonResponse(['error' => 'Type invalide'], 400);
            }

            $filename = $imagesTempService->upload($file);

            $uploaded[] = [
                'filename' => $filename,
                'url' => '/uploads/images_tmp/' . $filename,
            ];
        }

        return new JsonResponse(['images' => $uploaded]);
    }

    #[Route('/temp/delete', name: 'profile_images_temp_delete', methods: ['POST'])]
    public function delete(Request $request, ImagesTempService $imagesTempService): JsonResponse
    {
        $filename = $request->request->get('filename');
        if (!$filename) {
            return new JsonResponse(['error' => 'Nom manquant'], 400);
        }

        $imagesTempService->delete($filename);

        return new JsonResponse(['success' => true]);
    }
}
