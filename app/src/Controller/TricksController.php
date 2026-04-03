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
use App\Form\AddCommentFormType;
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
        $trick = $tricksRepository->findOneBy(['slug' => $slug]);
        if (!$trick) {
            throw $this->createNotFoundException('Cette figure n\'existe pas');
        }

        // Formulaire commentaire
        $comment = new Comments();
        $form = $this->createForm(CommentsFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                throw $this->createAccessDeniedException();
            }

            $comment->setUser($this->getUser())
                ->setTrick($trick);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_tricks_details', [
                'slug' => $slug
            ]);
        }

        // Afficher les commentaires
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;

        $comments = $commentsRepository->findByTrickPaginated($trick, $page, $limit);

        if ($request->isXmlHttpRequest()) {

            $html = $this->renderView('_partials/comments.html.twig', [
                'comments' => $comments
            ]);

            $offset = ($page - 1) * $limit;
            $totalComments = $commentsRepository->count(['trick' => $trick]);

            $hasMore = ($offset + $limit) < $totalComments;

            return $this->json([
                'html' => $html,
                'hasMore' => $hasMore
            ]);
        }

        // Nombre total de commentaires pour le compteur
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
