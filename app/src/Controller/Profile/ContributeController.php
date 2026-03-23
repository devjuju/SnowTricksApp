<?php

namespace App\Controller\Profile;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Entity\Videos;
use App\Form\ContributeFormType;
use App\Form\ContributeType;
use App\Form\TrickContributeType;
use App\Repository\TricksRepository;
use App\Service\ImagesUploaderService;
use App\Service\ImagesTempService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommentsRepository;


#[Route('/profile/tricks')]
class ContributeController extends AbstractController
{
    #[Route('/contribute/{slug}', name: 'app_profile_tricks_contribute')]
    public function contribute(
        string $slug,
        Request $request,
        TricksRepository $repository,
        EntityManagerInterface $em,
        ImagesUploaderService $imagesUploaderService,
        ImagesTempService $imagesTempService,
        CommentsRepository $commentsRepository,
    ): Response {
        $trick = new Tricks;

        $this->denyAccessUnlessGranted('TRICK_CONTRIBUTE', $trick);

        $trick = $repository->findOneBy(['slug' => $slug]);
        if (!$trick) {
            throw $this->createNotFoundException('Figure introuvable');
        }

        // 🔐 Vérification contribution
        if (!$this->isGranted('TRICK_CONTRIBUTE', $trick)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas contribuer à cette figure.');
        }

        // Si ce n'est pas un POST, on vide les temporaires
        if (!$request->isMethod('POST')) {
            $imagesTempService->clear();
        }

        $form = $this->createForm(ContributeFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleMedia($trick, $request, $em, $imagesUploaderService, $imagesTempService);
            $em->flush();
            $this->addFlash('success', 'Contribution enregistrée');

            return $this->redirectToRoute('app_profile_index');
        }

        $tempImages = $imagesTempService->getAll();

        // Pagination commentaires
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;
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




        return $this->render('profile/contribute/contribute.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'tempImages' => $tempImages,
            'comments' => $comments,
            'page' => $page,
            'totalPages' => $totalPages,

        ]);
    }

    private function handleMedia(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em,
        ImagesUploaderService $imagesUploaderService,
        ImagesTempService $imagesTempService
    ): void {

        $currentUser = $this->getUser();
        $removedImages = $request->request->all('removed_images', []);
        $removedVideos = $request->request->all('removed_videos', []);

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ SUPPRESSION DES IMAGES EXISTANTES
    |--------------------------------------------------------------------------
    */
        foreach ($trick->getImages() as $image) {

            if (!in_array($image->getPicture(), $removedImages, true)) {
                continue;
            }

            if (!$this->isGranted('MEDIA_DELETE', $image)) {
                continue;
            }

            $imagesUploaderService->delete($image->getPicture());
            $em->remove($image);
        }

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ AJOUT DES IMAGES TEMPORAIRES
    |--------------------------------------------------------------------------
    */
        $finalImages = $imagesTempService->moveAllToFinal();

        foreach ($finalImages as $filename) {

            // si l'utilisateur a supprimé l'image avant submit
            if (in_array($filename, $removedImages, true)) {
                continue;
            }

            $image = new Images();
            $image->setPicture($filename);
            $image->setTrick($trick);
            $image->setUser($currentUser);

            $em->persist($image);
        }

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ SUPPRESSION DES VIDÉOS
    |--------------------------------------------------------------------------
    */
        foreach ($removedVideos as $videoId) {

            if (!$videoId) {
                continue;
            }

            $video = $em->getRepository(Videos::class)->find($videoId);

            if ($video && $this->isGranted('MEDIA_DELETE', $video)) {
                $em->remove($video);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ ATTACHE LES NOUVELLES VIDÉOS
    |--------------------------------------------------------------------------
    */
        foreach ($trick->getVideos() as $video) {

            if ($video->getId()) {
                continue;
            }

            $video->setUser($currentUser);
            $video->setTrick($trick);

            $em->persist($video);
        }
    }
}
