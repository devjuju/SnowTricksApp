<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Repository\CommentsRepository;
use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CommentsFormType;

#[Route('/tricks', name: 'app_tricks_')]
final class TricksController extends AbstractController
{
    #[Route('/details/{slug}', name: 'details')]
    public function details(
        string $slug,
        Request $request,
        TricksRepository $tricksRepository,
        CommentsRepository $commentsRepository,
        EntityManagerInterface $entityManager
    ): Response {

        // Récupération de la figure via son slug (SEO-friendly URL)
        $trick = $tricksRepository->findOneBy(['slug' => $slug]);

        // Sécurité : gestion des cas où la figure n'existe pas
        if (!$trick) {
            throw $this->createNotFoundException('Cette figure n\'existe pas');
        }

        // ==========================
        // 💬 GESTION DES COMMENTAIRES
        // ==========================

        $comment = new Comments();

        // Création du formulaire de commentaire lié à l'entité
        $form = $this->createForm(CommentsFormType::class, $comment);
        $form->handleRequest($request);

        // Traitement du commentaire
        if ($form->isSubmitted() && $form->isValid()) {

            // Vérification sécurité : seul un utilisateur connecté peut commenter
            if (!$this->getUser()) {
                throw $this->createAccessDeniedException();
            }

            // Association commentaire -> utilisateur + trick
            $comment->setUser($this->getUser())
                ->setTrick($trick);

            // Sauvegarde en base
            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirection pour éviter double soumission du formulaire
            return $this->redirectToRoute('app_tricks_details', [
                'slug' => $slug
            ]);
        }

        // ==========================
        // 📄 PAGINATION COMMENTAIRES
        // ==========================

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;

        // Récupération paginée des commentaires
        $comments = $commentsRepository->findByTrickPaginated($trick, $page, $limit);

        // ==========================
        // ⚡ AJAX - LOAD MORE COMMENTS
        // ==========================

        if ($request->isXmlHttpRequest()) {

            // HTML partiel injecté côté front
            $html = $this->renderView('_partials/comment/item.html.twig', [
                'comments' => $comments
            ]);

            // Calcul pour savoir s’il reste des commentaires
            $offset = ($page - 1) * $limit;
            $totalComments = $commentsRepository->count(['trick' => $trick]);

            $hasMore = ($offset + $limit) < $totalComments;

            // Réponse JSON pour le JS (infinite scroll / load more)
            return $this->json([
                'html' => $html,
                'hasMore' => $hasMore
            ]);
        }

        // Nombre total de commentaires (affichage compteur UI)
        $totalComments = $commentsRepository->count(['trick' => $trick]);

        return $this->render('tricks/details.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'commentForm' => $form->createView(),
            'page' => $page,
            'totalComments' => $totalComments,
            'limit' => $limit,
        ]);
    }
}
