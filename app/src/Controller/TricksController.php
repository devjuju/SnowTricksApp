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

        // Pagination commentaires
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $comments = $commentsRepository->findBy(
            ['trick' => $trick],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        // Nombre total de commentaires pour le calcul des pages
        $totalComments = $commentsRepository->count(['trick' => $trick]);
        $totalPages = (int) ceil($totalComments / $limit);




        return $this->render('tricks/details.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'commentForm' => $form->createView(),
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }



    #[Route('/{slug}/comments/load', name: 'comments_load')]
    public function loadMoreComments(
        string $slug,
        Request $request,
        TricksRepository $tricksRepository,
        CommentsRepository $commentsRepository
    ): Response {
        $offset = $request->query->getInt('offset', 0);

        $trick = $tricksRepository->findOneBy(['slug' => $slug]);

        if (!$trick) {
            return new Response('', 404);
        }

        $comments = $commentsRepository->findBy(
            ['trick' => $trick],
            ['createdAt' => 'DESC'],
            10,
            $offset
        );

        return $this->render('_partials/comments.html.twig', [
            'comments' => $comments
        ]);
    }
}
