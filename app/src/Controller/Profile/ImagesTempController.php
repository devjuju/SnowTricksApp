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

    #[Route('/temp', name: 'profile_images_temp', methods: ['POST'])]
    public function upload(Request $request, ImagesTempService $imagesTempService): JsonResponse
    {
        // Sécurité : uniquement utilisateurs connectés
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération de plusieurs fichiers envoyés (input multiple)
        $files = $request->files->all('images');

        // Vérification : aucun fichier envoyé
        if (!$files) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        // Tableau de retour pour le front (preview + gestion dynamique)
        $uploaded = [];

        foreach ($files as $file) {

            // Validation du type MIME (sécurité basique côté serveur)
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                return new JsonResponse(['error' => 'Type invalide'], 400);
            }

            // Upload dans un dossier temporaire avec contexte
            $filename = $imagesTempService->upload($file, self::CONTEXT);

            // Retour des infos nécessaires côté front
            $uploaded[] = [
                'filename' => $filename,
                'url' => '/uploads/images_tmp/' . $filename,
            ];
        }

        // Retour JSON contenant toutes les images uploadées (multi-upload)
        return new JsonResponse(['images' => $uploaded]);
    }

    #[Route('/temp/delete', name: 'profile_images_temp_delete', methods: ['POST'])]
    public function delete(Request $request, ImagesTempService $imagesTempService): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération du nom du fichier à supprimer
        $filename = $request->request->get('filename');

        // Vérification
        if (!$filename) {
            return new JsonResponse(['error' => 'Nom manquant'], 400);
        }

        // Suppression via service (gestion centralisée + sécurité)
        $imagesTempService->delete($filename, self::CONTEXT);

        // Confirmation côté front
        return new JsonResponse(['success' => true]);
    }
}
