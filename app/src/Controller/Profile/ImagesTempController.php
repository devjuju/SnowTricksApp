<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ImagesTempService;

#[Route('/profile/images')]
class ImagesTempController extends AbstractController
{
    private const CONTEXT = 'trick_upload';
    private const MAX_SIZE = 2 * 1024 * 1024; // 2 Mo

    #[Route('/temp', name: 'profile_images_temp', methods: ['POST'])]
    public function upload(Request $request, ImagesTempService $imagesTempService): JsonResponse
    {
        // 🔐 Sécurité
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // 📦 Récupération fichiers
        $files = $request->files->all('images');

        if (!$files) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        $uploaded = [];

        foreach ($files as $file) {

            // 🚨 Vérifie erreur upload PHP
            if ($file->getError() !== UPLOAD_ERR_OK) {
                return new JsonResponse(['error' => 'Erreur upload fichier'], 400);
            }

            // 🎯 Vérification MIME
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
                return new JsonResponse(['error' => 'Type de fichier invalide'], 400);
            }

            // ⚖️ Vérification taille
            if ($file->getSize() > self::MAX_SIZE) {
                return new JsonResponse(['error' => 'Fichier trop lourd (max 2 Mo)'], 400);
            }

            // 💾 Upload temporaire
            $filename = $imagesTempService->upload($file, self::CONTEXT);

            $uploaded[] = [
                'filename' => $filename,
                'url' => '/uploads/images_tmp/' . $filename,
            ];
        }

        return new JsonResponse([
            'images' => $uploaded
        ]);
    }

    #[Route('/temp/delete', name: 'profile_images_temp_delete', methods: ['POST'])]
    public function delete(Request $request, ImagesTempService $imagesTempService): JsonResponse
    {
        // 🔐 Sécurité
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $filename = $request->request->get('filename');

        if (!$filename) {
            return new JsonResponse(['error' => 'Nom de fichier manquant'], 400);
        }

        // 🧹 Suppression sécurisée
        $imagesTempService->delete($filename, self::CONTEXT);

        return new JsonResponse(['success' => true]);
    }
}
