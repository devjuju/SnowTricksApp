<?php

namespace App\Controller\Profile;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Entity\Users;
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

        $trick = $repository->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createNotFoundException('Figure introuvable');
        }

        $this->denyAccessUnlessGranted('TRICK_CONTRIBUTE', $trick);

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
        $user = $this->getUser();

        if (!$user instanceof Users) {
            throw new \LogicException('Utilisateur non authentifié.');
        }

        $formData = $request->request->all('trick_add_form');

        // ------------------------
        // Gérer les images
        // ------------------------
        $this->handleImagesReplace($trick, $request, $imagesUploaderService, $imagesTempService);
        $this->handleImagesAdd($trick, $request, $em, $imagesTempService, $user);
        $this->handleImagesDelete($trick, $request, $em, $imagesUploaderService);

        // ------------------------
        // Gérer les vidéos
        // ------------------------
        $this->handleVideosDelete($trick, $request, $em);

        $this->handleVideosReplace($trick, $request, $em);

        $this->handleVideosAdd($trick, $request, $em);

        // ------------------------
        // Nettoyage des images sans fichier
        // ------------------------
        $this->cleanupEmptyImages($trick, $em);
    }


    private function handleImagesReplace(
        Tricks $trick,
        Request $request,
        ImagesUploaderService $imagesUploaderService,
        ImagesTempService $imagesTempService
    ): void {
        $replacements = $request->request->all('replace_images', []);

        foreach ($trick->getImages() as $image) {
            $old = $image->getPicture();

            if (!isset($replacements[$old])) {
                continue;
            }

            $new = $replacements[$old];

            if (!$new) {
                continue;
            }

            if (!$this->isGranted('MEDIA_EDIT', $image)) {
                continue;
            }

            // 🔥 delete old file
            $imagesUploaderService->delete($old);

            // 🔥 move new file
            $imagesTempService->moveToFinal($new);

            // 🔥 update entity
            $image->setPicture($new);

            // 🔥 CRUCIAL: prevent double-processing later
            unset($replacements[$old]);
        }
    }





    private function handleImagesAdd(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em,
        ImagesTempService $imagesTempService,
        Users $user
    ): void {


        $removed = $request->request->all('removed_images', []);
        $replacements = $request->request->all('replace_images', []);

        // 🔥 build blacklist (removed + replaced old files)
        $blacklist = array_merge($removed, array_keys($replacements));

        foreach ($imagesTempService->getAll() as $filename) {

            if (!$filename) {
                continue;
            }

            // ❌ skip removed
            if (in_array($filename, $removed, true)) {
                continue;
            }

            // ❌ skip replaced originals
            if (in_array($filename, $blacklist, true)) {
                continue;
            }

            // ❌ avoid duplicates already in DB
            $exists = $trick->getImages()->exists(
                fn($i, $img) => $img->getPicture() === $filename
            );

            if ($exists) {
                continue;
            }

            $imagesTempService->moveToFinal($filename);

            $image = (new Images())
                ->setPicture($filename)
                ->setTrick($trick);

            $trick->addImage($image);
            $em->persist($image);
        }
    }

    private function handleImagesDelete(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em,
        ImagesUploaderService $imagesUploaderService
    ): void {

        $removed = $request->request->all('removed_images', []);

        foreach ($trick->getImages() as $image) {

            if (!in_array($image->getPicture(), $removed, true)) {
                continue;
            }

            if (!$this->isGranted('MEDIA_DELETE', $image)) {
                continue;
            }

            $imagesUploaderService->delete($image->getPicture());

            $trick->removeImage($image);
            $em->remove($image);
        }
    }

    // =========================
    // 🧠 LOGIQUE VIDÉOS

    private function handleVideosAdd(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em
    ): void {

        $removed = $request->request->all('removed_videos', []);
        $videos = $request->request->all('videos_urls', []);

        foreach ($videos as $url) {

            $url = trim($url);
            if (!$url) continue;

            // 🔥 utiliser ton entity pour parser
            $video = new Videos();
            $video->setUrl($url);

            $id = $video->getYoutubeId();

            if (!$id) continue;

            // ❌ skip removed
            if (in_array($id, $removed, true)) continue;

            // ❌ éviter doublons (PAR ID)
            $exists = $trick->getVideos()->exists(
                fn($i, $v) => $v->getYoutubeId() === $id
            );

            if ($exists) continue;

            $video->setTrick($trick);
            $trick->addVideo($video);
            $em->persist($video);
        }
    }

    private function handleVideosReplace(
        Tricks $trick,
        Request $request,
    ): void {

        $replacements = $request->request->all('replace_videos', []);

        foreach ($trick->getVideos() as $video) {

            $oldId = $video->getYoutubeId();

            if (!$oldId || !isset($replacements[$oldId])) {
                continue;
            }

            $newUrl = trim($replacements[$oldId]);

            if (!$newUrl) continue;

            if (!$this->isGranted('MEDIA_EDIT', $video)) {
                continue;
            }

            $video->setUrl($newUrl);
        }
    }

    private function handleVideosDelete(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em
    ): void {

        $removed = $request->request->all('removed_videos', []);

        foreach ($trick->getVideos() as $video) {

            $id = $video->getYoutubeId();

            if (!$id || !in_array($id, $removed, true)) {
                continue;
            }

            if (!$this->isGranted('MEDIA_DELETE', $video)) {
                continue;
            }

            $trick->removeVideo($video);
            $em->remove($video);
        }
    }

    private function cleanupEmptyImages(Tricks $trick, EntityManagerInterface $em): void
    {
        foreach ($trick->getImages() as $image) {
            if (!$image->getPicture()) {
                $trick->removeImage($image);
                $em->remove($image);
            }
        }
    }
}
