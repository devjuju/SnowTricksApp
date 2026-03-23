<?php

namespace App\Controller\Profile;

use App\Entity\Tricks;
use App\Entity\Images;
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

        // Si ce n'est pas un POST, on vide les temporaires
        if (!$request->isMethod('POST')) {
            $featuredImageTempService->clear();
            $imagesTempService->clear();
        }

        $form = $this->createForm(TrickAddFormType::class, $trick, [
            'featured_image_temp_service' => $featuredImageTempService,
        ]);

        $form->handleRequest($request);
        $saveButton = $form->get('save');

        if ($form->isSubmitted() && $saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

            // Générer un slug unique si le titre existe
            if ($trick->getTitle()) {
                $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
            }

            // -------------------------
            // Gestion des images temporaires même si le formulaire est invalide
            // -------------------------
            $uploadedImages = $request->files->get('trick_add_form')['images'] ?? [];
            foreach ($uploadedImages as $imageFormData) {
                $file = $imageFormData['file'] ?? null;
                if ($file) {
                    $imagesTempService->upload($file);
                }
            }

            // Si le formulaire est valide
            if ($form->isValid()) {

                // Featured image
                $tempFeaturedImage = $featuredImageTempService->get();
                if ($tempFeaturedImage) {
                    $featuredImageTempService->moveToFinal($tempFeaturedImage);
                    $trick->setFeaturedImage($tempFeaturedImage);
                    $featuredImageTempService->clear();
                }

                // Gestion images et vidéos
                $this->handleMedia($trick, $request, $em, $imagesUploaderService, $imagesTempService);

                $em->persist($trick);
                $em->flush();

                $this->addFlash('success', 'Figure ajoutée avec succès');
                return $this->redirectToRoute('app_profile_index');
            }
        }

        // -------------------------
        // Rendu du formulaire
        // -------------------------
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

        $trick = $repository->findOneBy(['slug' => $slug]);
        if (!$trick) throw $this->createNotFoundException('Figure introuvable');

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

            // SUPPRESSION
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

            // MODIFICATION
            if ($saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

                // Générer un slug unique si le titre a changé
                if ($trick->getTitle()) {
                    $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
                }

                if ($form->isValid()) {
                    // Featured image
                    $tempFeaturedImage = $featuredImageTempService->get();
                    if ($tempFeaturedImage) {
                        $featuredImageTempService->moveToFinal($tempFeaturedImage);
                        $trick->setFeaturedImage($tempFeaturedImage);
                        $featuredImageTempService->clear();
                    }

                    $this->handleMedia($trick, $request, $em, $imagesUploaderService, $imagesTempService);
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


    // ---------------------
    // handleMedia sécurisé
    // ---------------------
    private function handleMedia(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em,
        ImagesUploaderService $imagesUploaderService,
        ImagesTempService $imagesTempService
    ): void {
        $currentUser = $this->getUser();

        // REPLACEMENT DES IMAGES TEMPORAIRES
        $replacements = $request->request->all('replace_images', []);

        foreach ($replacements as $old => $new) {

            foreach ($trick->getImages() as $image) {

                if ($image->getPicture() === $old) {

                    $imagesUploaderService->delete($old);

                    $image->setPicture($new);

                    break;
                }
            }
        }

        $replacedFiles = array_values($replacements);

        // AJOUT DES IMAGES TEMP → FINAL
        foreach ($imagesTempService->moveAllToFinal() as $filename) {

            if (!$filename) continue;

            // 🔥 si déjà utilisé pour remplacement → on skip
            if (in_array($filename, $replacedFiles, true)) {
                continue;
            }

            $image = new Images();
            $image->setPicture($filename);
            $image->setTrick($trick);
            $image->setUser($this->getUser());

            $trick->addImage($image);
            $em->persist($image);
        }

        // SUPPRESSION DES IMAGES EXISTANTES
        $removedImages = $request->request->all('removed_images', []);
        foreach ($removedImages as $filename) {
            if (!$filename || $filename === 'new') continue;

            foreach ($trick->getImages() as $image) {
                if ($image->getPicture() === $filename && $this->isGranted('MEDIA_DELETE', $image)) {
                    $imagesUploaderService->delete($filename);
                    $trick->removeImage($image);
                    $em->remove($image);
                    break;
                }
            }
        }

        // SUPPRESSION DES VIDÉOS
        $removedVideos = $request->request->all('removed_videos', []);
        foreach ($removedVideos as $id) {
            if (!ctype_digit((string)$id)) continue;

            $video = $em->getRepository(Videos::class)->find($id);
            if ($video && $this->isGranted('MEDIA_DELETE', $video)) {
                $trick->removeVideo($video);
                $em->remove($video);
            }
        }

        // AJOUT DES VIDÉOS
        $videosData = $request->request->all('videos', []);
        foreach ($videosData as $videoData) {
            $url = trim($videoData['url'] ?? '');
            if (!$url) continue;

            $exists = false;
            foreach ($trick->getVideos() as $existing) {
                if ($existing->getUrl() === $url) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) continue;

            $video = new Videos();
            $video->setUrl($url);
            $video->setTrick($trick);
            $video->setUser($currentUser);

            $trick->addVideo($video);
            $em->persist($video);
        }

        // SUPPRESSION DES IMAGES VIDES CRÉÉES PAR SYMFONY
        foreach ($trick->getImages() as $image) {
            if (!$image->getPicture()) {
                $trick->removeImage($image);
                $em->remove($image);
            }
        }
    }
}
