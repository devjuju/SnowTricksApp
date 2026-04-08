<?php

namespace App\Controller;

use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(Request $request, TricksRepository $tricksRepository): Response
    {
        // Pagination sécurisée : page minimum = 1
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Récupération paginée des tricks
        $tricks = $tricksRepository->findByTrickPaginated($page, $limit);

        // Cas AJAX : chargement dynamique (bouton "voir plus")
        if ($request->isXmlHttpRequest()) {

            // Rendu partiel HTML pour injection JS
            $html = $this->renderView('_partials/main/tricks/item.html.twig', [
                'tricks' => $tricks
            ]);

            // Calcul offset pour savoir si d'autres tricks existent
            $offset = ($page - 1) * $limit;

            // Nombre total de tricks en base
            $totalTricks = $tricksRepository->count([]);

            // Détermine s’il reste du contenu à charger
            $hasMore = ($offset + $limit) < $totalTricks;

            // Retour JSON pour front JS
            return $this->json([
                'html' => $html,
                'hasMore' => $hasMore
            ]);
        }

        // Mode normal (chargement initial page)
        $totalTricks = $tricksRepository->count([]);

        return $this->render('main/index.html.twig', [
            'tricks' => $tricks,
            'page' => $page,
            'totalTricks' => $totalTricks,
            'limit' => $limit,
        ]);
    }
}
