<?php

namespace App\Controller\Profile;

use App\Form\MediasFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ImagesTempService;
use App\Service\SlugService;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comments;
use App\Repository\CommentsRepository;
use App\Service\TrickMediaManagerService;

#[Route('/profile/tricks')]
final class MediasController extends AbstractController
{
    #[Route('/{slug}/ajouter-des-medias', name: 'app_profile_add_medias_trick')]
    public function index(
        string $slug,
        Request $request,
        EntityManagerInterface $em,
        TricksRepository $repository,
        ImagesTempService $imagesTempService,
        SlugService $slugService,
        CommentsRepository $commentsRepository,
        TrickMediaManagerService $trickMediaManagerService

    ): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trick = $repository->findOneBy(['slug' => $slug]);
        if (!$trick) throw $this->createNotFoundException('Figure introuvable');

        $this->denyAccessUnlessGranted('TRICK_EDIT', $trick);

        if (!$request->isMethod('POST')) {
            $imagesTempService->clear();
        }

        $form = $this->createForm(MediasFormType::class, $trick);
        $form->handleRequest($request);

        $saveButton = $form->get('save');

        if ($form->isSubmitted()) {

            // MODIFICATION
            if ($saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

                // Générer un slug unique si le titre a changé
                if ($trick->getTitle()) {
                    $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
                }

                if ($form->isValid()) {
                    $trickMediaManagerService->handle($trick, $request);
                    $em->flush();

                    $this->addFlash('success', 'Medias ajoutés avec succès');
                    return $this->redirectToRoute('app_profile_index');
                }
            }
        }

        // Afficher les commentaires
        $comments = new Comments();

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

        return $this->render('profile/medias/index.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'tempImages' => $imagesTempService->getAll(),
            'comments' => $comments,
            'page' => $page,
            'totalComments' => $totalComments,
            'limit' => $limit,
        ]);
    }
}
