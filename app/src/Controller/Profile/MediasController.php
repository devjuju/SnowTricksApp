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

        // Sécurité : accès uniquement aux utilisateurs connectés
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération du trick via son slug (SEO friendly)
        $trick = $repository->findOneBy(['slug' => $slug]);

        // Gestion d'erreur si le trick n'existe pas
        if (!$trick) throw $this->createNotFoundException('Figure introuvable');

        $this->denyAccessUnlessGranted('TRICK_EDIT', $trick);

        // Nettoyage des images temporaires si accès hors soumission (UX + propreté stockage)
        if (!$request->isMethod('POST')) {
            $imagesTempService->clear();
        }

        // Création du formulaire dédié à l'ajout de médias (images + vidéos)
        $form = $this->createForm(MediasFormType::class, $trick);
        $form->handleRequest($request);

        // Bouton submit (permet de différencier plusieurs actions si besoin)
        $saveButton = $form->get('save');

        if ($form->isSubmitted()) {

            // Vérifie que le bouton "save" est utilisé (bonne pratique multi-actions)
            if ($saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

                // Génération d’un slug unique si le titre a été modifié
                if ($trick->getTitle()) {
                    $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
                }

                // Si formulaire valide → traitement des médias
                if ($form->isValid()) {

                    // Service centralisé pour gérer images et vidéos
                    $trickMediaManagerService->handle($trick, $request);

                    $em->flush();

                    $this->addFlash('success', 'Medias ajoutés avec succès');

                    return $this->redirectToRoute('app_profile_index');
                }
            }
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;

        // // Récupération paginée des commentaires (UX : éviter surcharge)
        $comments = $commentsRepository->findByTrickPaginated($trick, $page, $limit);

        // Gestion AJAX pour chargement dynamique ("Afficher plus")
        if ($request->isXmlHttpRequest()) {

            // Rendu partiel Twig
            $html = $this->renderView('_partials/comment/item.html.twig', [
                'comments' => $comments
            ]);

            // Rendu partiel Twig
            $offset = ($page - 1) * $limit;

            // Nombre total de commentaires (utilisé pour affichage compteur + logique UX)
            $totalComments = $commentsRepository->count(['trick' => $trick]);

            $hasMore = ($offset + $limit) < $totalComments;

            // Réponse JSON pour le front
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
