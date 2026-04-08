<?php

namespace App\Controller\Profile;

use App\Entity\Tricks;
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
use App\Service\TrickMediaManagerService;

#[Route('/profile/tricks')]
class TricksController extends AbstractController
{
    #[Route('/ajouter', name: 'app_profile_tricks_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        FeaturedImageTempService $featuredImageTempService,
        ImagesTempService $imagesTempService,
        TrickMediaManagerService $trickMediaManagerService,
        SlugService $slugService
    ): Response {
        // Sécurité : accès réservé aux utilisateurs connectés
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Initialisation d’un nouveau trick lié à l’utilisateur
        $trick = new Tricks();
        $trick->setUser($this->getUser());

        // Contexte pour la gestion des images temporaires (évite collisions)
        $imagesTempService->setContext('trick_add');

        // Nettoyage des fichiers temporaires si accès hors soumission
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

            // Génération d’un slug unique pour éviter les conflits SEO / URL
            if ($trick->getTitle()) {
                $trick->setSlug($slugService->generateUniqueSlug($trick, 'title', $em));
            }

            // -------------------------
            // Gestion des uploads temporaires même si le formulaire est invalide
            // Permet de conserver les fichiers côté UX
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

                // -------------------------
                // Déplacement de l’image principale du temporaire vers stockage final
                // uniquement si le formulaire est valide
                // -------------------------
                $tempFeaturedImage = $featuredImageTempService->get();
                if ($tempFeaturedImage) {
                    $featuredImageTempService->moveToFinal($tempFeaturedImage);
                    $trick->setFeaturedImage($tempFeaturedImage);
                    $featuredImageTempService->clear();
                }

                // Service centralisé pour gérer images + vidéos (séparation des responsabilités)
                $trickMediaManagerService->handle($trick, $request);

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
        TrickMediaManagerService $trickMediaManagerService,
        SlugService $slugService
    ): Response {

        // Sécurité : vérifie que l'utilisateur a le droit de modifier ce trick
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

        // Gestion multi-actions via boutons (update / delete)
        $deleteButton = $form->get('delete');
        $saveButton = $form->get('save');

        if ($form->isSubmitted()) {

            // Suppression propre des fichiers associés (évite fichiers orphelins)
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

            if ($saveButton instanceof \Symfony\Component\Form\SubmitButton && $saveButton->isClicked()) {

                // Regénération du slug si le titre est modifié
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

                    $trickMediaManagerService->handle($trick, $request);


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

        // Protection CSRF pour sécuriser la suppression
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

        // Suppression complète : base + fichiers + relations
        $em->remove($trick);
        $em->flush();

        $this->addFlash('success', 'Figure supprimée avec succès');
        return $this->redirectToRoute('app_profile_index');
    }
}
