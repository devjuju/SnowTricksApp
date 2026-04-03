<?php
// src/Controller/MediaController.php
namespace App\Controller;

use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{
    #[Route('/media/featured/{hash}', name: 'media_featured')]
    public function featured(string $hash, TricksRepository $repo): Response
    {
        $trick = $repo->findOneBy(['featuredImageHash' => $hash]);

        if (!$trick || !$trick->getFeaturedImage()) {
            throw $this->createNotFoundException();
        }

        $path = $this->getParameter('featured_images_dir')
            . '/' . $trick->getFeaturedImage();

        return new Response(
            file_get_contents($path),
            200,
            [
                'Content-Type' => mime_content_type($path),
                'Cache-Control' => 'public, max-age=31536000'
            ]
        );
    }
}
