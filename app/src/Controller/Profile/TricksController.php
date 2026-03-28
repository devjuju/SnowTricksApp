<?php

namespace App\Controller\Profile;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Entity\Users;
use App\Entity\Videos;
use App\Form\TrickAddFormType;
use App\Form\TrickUpdateFormType;
use App\Repository\TricksRepository;
use App\Service\ImagesUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FeaturedImageUploaderService;
use App\Service\FeaturedImageTempService;
use App\Service\ImagesTempService;
use App\Service\SlugService;
use App\Service\VideosTempService;

#[Route('/profile/tricks')]
class TricksController extends AbstractController
{
    #[Route('/ajouter', name: 'app_profile_tricks_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        ImagesUploaderService $imagesUploaderService,
        FeaturedImageTempService $featuredImageTempService,
        ImagesTempService $imagesTempService,
        SlugService $slugService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trick = new Tricks();
        $trick->setUser($this->getUser());

        $imagesTempService->setContext('trick_add');

        if (!$request->isMethod('POST')) {
            $featuredImageTempService->clear();
            $imagesTempService->clear();
        }

        $form = $this->createForm(TrickAddFormType::class, $trick, [
            'featured_image_temp_service' => $featuredImageTempService,
        ]);

        $form->handleRequest($request);
        $saveButton = $form->get('save');

        // TEMP uploads vidéos
        $videosUrls = $request->request->all('videos_urls', []);

        foreach ($videosUrls as $url) {

            if (!is_string($url)) {
                continue; // ⛔ ignore les valeurs invalides
            }

            $url = trim($url);
            if ($url === '') continue;
        }

        if ($form->isSubmitted() && $saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

            if ($trick->getTitle()) {
                $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
            }

            // Images temp upload (déjà existant)
            $uploadedImages = $request->files->get('trick_add_form')['images'] ?? [];
            foreach ($uploadedImages as $imageFormData) {
                $file = $imageFormData['file'] ?? null;
                if ($file) {
                    $imagesTempService->upload($file);
                }
            }

            if ($form->isValid()) {

                // Featured image
                $tempFeaturedImage = $featuredImageTempService->get();
                if ($tempFeaturedImage) {
                    $featuredImageTempService->moveToFinal($tempFeaturedImage);
                    $trick->setFeaturedImage($tempFeaturedImage);
                    $featuredImageTempService->clear();
                }

                $this->handleMedia(
                    $trick,
                    $request,
                    $em,
                    $imagesUploaderService,
                    $imagesTempService,
                );

                $em->persist($trick);
                $em->flush();

                $this->addFlash('success', 'Figure ajoutée avec succès');
                return $this->redirectToRoute('app_profile_index');
            }
        }

        return $this->render('profile/tricks/add.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'tempFeaturedImage' => $featuredImageTempService->get(),
            'tempImages' => $imagesTempService->getAll(),
        ]);
    }


    #[Route('/modifier/{slug}', name: 'app_profile_tricks_edit')]
    public function edit(
        string $slug,
        Request $request,
        EntityManagerInterface $em,
        TricksRepository $repository,
        ImagesUploaderService $imagesUploaderService,
        FeaturedImageUploaderService $featuredImageUploaderService,
        FeaturedImageTempService $featuredImageTempService,
        ImagesTempService $imagesTempService,


        SlugService $slugService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        if (!$user) {
            throw new \LogicException('User not authenticated');
        }

        $trick = $repository->findOneBy(['slug' => $slug]);
        if (!$trick) {
            throw $this->createNotFoundException('Figure introuvable');
        }

        $this->denyAccessUnlessGranted('TRICK_EDIT', $trick);

        if (!$request->isMethod('POST')) {
            $featuredImageTempService->clear();
            $imagesTempService->clear();
        }

        $form = $this->createForm(TrickUpdateFormType::class, $trick, [
            'featured_image_temp_service' => $featuredImageTempService,
        ]);

        $form->handleRequest($request);

        $deleteButton = $form->get('delete');
        $saveButton = $form->get('save');




        if ($form->isSubmitted()) {

            // DELETE TRICK
            if ($deleteButton instanceof \Symfony\Component\Form\SubmitButton && $deleteButton->isClicked()) {

                if ($trick->getFeaturedImage()) {
                    $featuredImageUploaderService->delete($trick->getFeaturedImage());
                }

                foreach ($trick->getImages() as $image) {
                    $imagesUploaderService->delete($image->getPicture());
                    $em->remove($image);
                }

                foreach ($trick->getVideos() as $video) {
                    $em->remove($video);
                }

                $em->remove($trick);
                $em->flush();

                $this->addFlash('success', 'Figure supprimée avec succès');
                return $this->redirectToRoute('app_profile_index');
            }

            // UPDATE
            if ($saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

                if ($trick->getTitle()) {
                    $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
                }

                if ($form->isValid()) {

                    // 🔥 FIX ICI
                    $user = $this->getUser();

                    if (!$user) {
                        throw new \LogicException('User not authenticated');
                    }


                    // Featured image
                    $tempFeaturedImage = $featuredImageTempService->get();
                    if ($tempFeaturedImage) {
                        $featuredImageTempService->moveToFinal($tempFeaturedImage);
                        $trick->setFeaturedImage($tempFeaturedImage);
                        $featuredImageTempService->clear();
                    }

                    $this->handleMedia(
                        $trick,
                        $request,
                        $em,
                        $imagesUploaderService,
                        $imagesTempService,
                    );

                    $em->flush();

                    $this->addFlash('success', 'Figure modifiée avec succès');
                    return $this->redirectToRoute('app_profile_index');
                }
            }
        }

        return $this->render('profile/tricks/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'tempFeaturedImage' => $featuredImageTempService->get(),
            'tempImages' => $imagesTempService->getAll(),

        ]);
    }



    #[Route('/supprimer/{slug}', name: 'app_profile_tricks_delete', methods: ['POST'])]
    public function delete(
        string $slug,
        Request $request,
        TricksRepository $repository,
        EntityManagerInterface $em,
        FeaturedImageUploaderService $featuredImageUploaderService,
        ImagesUploaderService $imagesUploaderService
    ): Response {
        $trick = $repository->findOneBy(['slug' => $slug]);
        if (!$trick) throw $this->createNotFoundException('Figure introuvable');

        $this->denyAccessUnlessGranted('TRICK_DELETE', $trick);

        if (!$this->isCsrfTokenValid('delete-trick-' . $trick->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_profile_index');
        }

        if ($trick->getFeaturedImage()) {
            $featuredImageUploaderService->delete($trick->getFeaturedImage());
        }

        foreach ($trick->getImages() as $image) {
            $imagesUploaderService->delete($image->getPicture());
            $em->remove($image);
        }

        foreach ($trick->getVideos() as $video) {
            $em->remove($video);
        }

        $em->remove($trick);
        $em->flush();

        $this->addFlash('success', 'Figure supprimée avec succès');
        return $this->redirectToRoute('app_profile_index');
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
                ->setTrick($trick)
                ->setUser($user);

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

    private function handleVideosAdd(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em
    ): void {

        $removed = $request->request->all('removed_videos', []);
        $replacements = $request->request->all('replace_videos', []);

        $blacklist = array_merge($removed, array_keys($replacements));

        $videos = $request->request->all('videos_urls', []);

        foreach ($videos as $url) {

            $url = trim($url);

            if (!$url) {
                continue;
            }

            if (in_array($url, $removed, true)) {
                continue;
            }

            if (in_array($url, $blacklist, true)) {
                continue;
            }

            $exists = $trick->getVideos()->exists(
                fn($i, $v) => $v->getUrl() === $url
            );

            if ($exists) {
                continue;
            }

            $video = (new Videos())
                ->setUrl($url)
                ->setTrick($trick)
                ->setUser($this->getUser());

            $trick->addVideo($video);

            $em->persist($video);
        }
    }



    private function handleVideosReplace(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em
    ): void {

        $replacements = $request->request->all('replace_videos', []);

        foreach ($trick->getVideos() as $video) {

            $old = $video->getUrl();

            if (!isset($replacements[$old])) {
                continue;
            }

            $new = trim($replacements[$old]);

            if (!$new) {
                continue;
            }

            if (!$this->isGranted('MEDIA_EDIT', $video)) {
                continue;
            }

            $video->setUrl($new);
        }
    }



    private function handleVideosDelete(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em
    ): void {

        $removed = $request->request->all('removed_videos', []);

        foreach ($trick->getVideos() as $video) {

            if (!in_array($video->getUrl(), $removed, true)) {
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
