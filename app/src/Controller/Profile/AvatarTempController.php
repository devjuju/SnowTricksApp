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
        $file = $request->files->get('avatar');

        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        $filename = $avatarTempService->upload($file);

        return new JsonResponse([
            'url' => '/uploads/avatars_tmp/' . $filename
        ]);
    }


    #[Route('/profile/avatar/temp/delete', name: 'profile_avatar_temp_delete', methods: ['POST'])]
    public function delete(AvatarTempService $avatarTempService): JsonResponse
    {
        $avatarTempService->clear();

        return new JsonResponse([
            'success' => true
        ]);
    }
}
