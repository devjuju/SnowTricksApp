<?php

namespace App\Controller\Profile;

use App\Entity\Tricks;
use App\Entity\Users;
use App\Form\TrickAddFormType;
use App\Form\TrickUpdateFormType;
use App\Repository\TricksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FeaturedImageUploaderService;
use App\Service\FeaturedImageTempService;
use App\Service\ImagesTempService;
use App\Service\MediaManagerService;
use App\Service\SlugService;

#[Route('/profile/tricks')]
class TricksController extends AbstractController
{
    #[Route('/ajouter', name: 'app_profile_tricks_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        FeaturedImageTempService $featuredImageTempService,
        ImagesTempService $imagesTempService,
        MediaManagerService $mediaManager,
        SlugService $slugService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof Users) {
            throw new \LogicException('User not authenticated');
        }

        // =========================
        // 🧹 CLEAR TEMP ON FIRST LOAD
        // =========================
        if (!$request->isMethod('POST')) {
            $featuredImageTempService->clear();
            $imagesTempService->clear();
        }

        // =========================
        // 🧾 FORM
        // =========================
        $trick = new Tricks();

        $form = $this->createForm(TrickAddFormType::class, $trick, [
            'featured_image_temp_service' => $featuredImageTempService,
        ]);

        $form->handleRequest($request);

        $saveButton = $form->get('save');

        // =========================
        // ✏ SAVE TRICK
        // =========================
        if (
            $form->isSubmitted() &&
            $saveButton instanceof \Symfony\Component\Form\SubmitButton &&
            $saveButton->isClicked()
        ) {
            if ($form->isValid()) {

                // =========================
                // 👤 USER OWNER
                // =========================
                $trick->setUser($user);

                // =========================
                // 🔗 SLUG GENERATION
                // =========================
                $trick->setSlug(
                    $slugService->generateUniqueSlug($trick, 'title', $em)
                );

                // =========================
                // 🖼 FEATURED IMAGE FINALIZE
                // =========================
                $tempFeaturedImage = $featuredImageTempService->get();

                if ($tempFeaturedImage) {
                    $featuredImageTempService->moveToFinal($tempFeaturedImage);
                    $trick->setFeaturedImage($tempFeaturedImage);
                    $featuredImageTempService->clear();
                }

                // =========================
                // 🚀 MEDIA MANAGER (IMAGES + VIDEOS)
                // =========================
                $mediaManager->handleImages($trick, $request, $user);
                $mediaManager->handleVideos($trick, $request);

                // =========================
                // 💾 SAVE
                // =========================
                $em->persist($trick);
                $em->flush();

                // 🔥 ici seulement
                $imagesTempService->cleanup();

                $this->addFlash('success', 'Figure créée avec succès');

                return $this->redirectToRoute('app_profile_index');
            }
        }


        // =========================
        // 🎨 RENDER
        // =========================
        return $this->render('profile/tricks/add.html.twig', [
            'form' => $form->createView(),
            'tempFeaturedImage' => $featuredImageTempService->get(),
            'tempImages' => $this->resolveTempImages($request, $imagesTempService),
        ]);
    }


    private function resolveTempImages(Request $request, ImagesTempService $service): array
    {
        if (!$request->isMethod('POST')) {
            return $service->getAll();
        }

        return $service->getUnused(
            $request->request->all('replace_images', []),
            $request->request->all('removed_images', [])
        );
    }






    #[Route('/modifier/{slug}', name: 'app_profile_tricks_edit')]
    public function edit(
        string $slug,
        Request $request,
        EntityManagerInterface $em,
        TricksRepository $repository,
        FeaturedImageUploaderService $featuredImageUploaderService,
        FeaturedImageTempService $featuredImageTempService,
        ImagesTempService $imagesTempService,
        MediaManagerService $mediaManager,
        SlugService $slugService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user instanceof Users) {
            throw new \LogicException('User not authenticated');
        }

        // =========================
        // 🔍 LOAD TRICK
        // =========================
        $trick = $repository->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createNotFoundException('Figure introuvable');
        }

        $this->denyAccessUnlessGranted('TRICK_EDIT', $trick);

        // =========================
        // 🧹 CLEAR TEMP ON GET
        // =========================
        if (!$request->isMethod('POST')) {
            $featuredImageTempService->clear();
            $imagesTempService->clear();
        }

        // =========================
        // 🧾 FORM
        // =========================
        $form = $this->createForm(TrickUpdateFormType::class, $trick, [
            'featured_image_temp_service' => $featuredImageTempService,
        ]);

        $form->handleRequest($request);

        $deleteButton = $form->get('delete');
        $saveButton = $form->get('save');

        // =========================
        // 🗑 DELETE TRICK
        // =========================
        if (
            $form->isSubmitted() &&
            $deleteButton instanceof \Symfony\Component\Form\SubmitButton &&
            $deleteButton->isClicked()
        ) {
            $mediaManager->deleteAll($trick);


            $em->flush();

            $this->addFlash('success', 'Figure supprimée avec succès');

            return $this->redirectToRoute('app_profile_index');
        }

        // =========================
        // ✏ UPDATE TRICK
        // =========================
        if (
            $form->isSubmitted() &&
            $saveButton instanceof \Symfony\Component\Form\SubmitButton &&
            $saveButton->isClicked()
        ) {
            if ($form->isValid()) {

                // =========================
                // 🔗 SLUG UPDATE
                // =========================
                if ($trick->getTitle()) {
                    $trick->setSlug(
                        $slugService->generateUniqueSlug($trick, 'title', $em)
                    );
                }

                // =========================
                // 🖼 FEATURED IMAGE
                // =========================
                $tempFeaturedImage = $featuredImageTempService->get();

                if ($tempFeaturedImage) {
                    $featuredImageTempService->moveToFinal($tempFeaturedImage);
                    $trick->setFeaturedImage($tempFeaturedImage);
                    $featuredImageTempService->clear();
                }

                // =========================
                // 🚀 MEDIA MANAGER (IMAGES + VIDEOS)
                // =========================
                $mediaManager->handleImages($trick, $request, $user);
                $mediaManager->handleVideos($trick, $request);

                // =========================
                // 💾 SAVE
                // =========================
                $em->flush();

                // 🔥 ici seulement
                $imagesTempService->cleanup();

                $this->addFlash('success', 'Figure modifiée avec succès');

                return $this->redirectToRoute('app_profile_index');
            }
        }

        // =========================
        // 🎨 RENDER
        // =========================
        return $this->render('profile/tricks/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'tempFeaturedImage' => $featuredImageTempService->get(),
            'tempImages' => $this->resolveTempImages($request, $imagesTempService),
        ]);
    }


    #[Route('/supprimer/{slug}', name: 'app_profile_tricks_delete', methods: ['POST'])]
    public function delete(
        string $slug,
        Request $request,
        TricksRepository $repository,
        EntityManagerInterface $em,
        MediaManagerService $mediaManager
    ): Response {

        $trick = $repository->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createNotFoundException('Figure introuvable');
        }

        $this->denyAccessUnlessGranted('TRICK_DELETE', $trick);

        if (!$this->isCsrfTokenValid('delete-trick-' . $trick->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_profile_index');
        }

        $mediaManager->deleteAll($trick);

        $em->flush();



        $this->addFlash('success', 'Figure supprimée avec succès');

        return $this->redirectToRoute('app_profile_index');
    }
}
