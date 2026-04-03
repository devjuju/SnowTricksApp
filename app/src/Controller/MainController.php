<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Repository\TricksRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
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

        return $this->render('main/index.html.twig', [
            'tricks' => $tricks,
            'page' => $page,
            'totalTricks' => $totalTricks,
            'limit' => $limit,
        ]);
    }
}
