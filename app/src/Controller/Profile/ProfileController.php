<?php

namespace App\Controller\Profile;

use App\Repository\TricksRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\ProfileFormType;
use App\Service\AvatarTempService;
use App\Service\AvatarUploaderService;

#[Route('/profile', name: 'app_profile_')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
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
        return $this->render('profile/profile/index.html.twig', [
            'tricks' => $tricks,
            'page' => $page,
        ]);
    }
}
