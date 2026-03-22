<?php

namespace App\Controller;

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
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // ----------------------------
        // QueryBuilder pour les Tricks actifs
        // ----------------------------
        $query = $tricksRepository->createQueryBuilder('t')

            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        $tricks = new Paginator($query);

        // ----------------------------
        // Réponse AJAX pour infinite scroll ou load more
        // ----------------------------
        if ($request->isXmlHttpRequest()) {
            return $this->render('_partials/tricks.html.twig', [
                'tricks' => $tricks,
            ]);
        }

        // ----------------------------
        // Page classique
        // ----------------------------
        return $this->render('main/index.html.twig', [
            'tricks' => $tricks,
            'page' => $page,
        ]);
    }
}
