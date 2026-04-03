<?php

namespace App\Service;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MediaHandlerService
{
    public function __construct(
        private ImagesUploaderService $imagesUploaderService,
        private ImagesTempService $imagesTempService,
        private VideoManagerService $videoManager,
    ) {}

    public function process(
        Tricks $trick,
        Request $request,
        EntityManagerInterface $em,
        Users $user
    ): void {

        $this->handleImagesAdd($trick, $request, $em);
        $this->handleImagesReplace($trick, $request);
        $this->handleImagesDelete($trick, $request, $em);

        $this->handleVideosAdd($trick, $request, $em);
        $this->handleVideosReplace($trick, $request);
        $this->handleVideosDelete($trick, $request, $em);

        $this->cleanupEmptyImages($trick, $em);
    }


    private function handleImagesAdd(Tricks $trick, Request $request, EntityManagerInterface $em): void
    {
        $filenames = array_filter(
            $request->request->all('images_tmp', []),
            fn($f) => is_string($f) && trim($f) !== ''
        );

        foreach ($filenames as $filename) {

            $this->imagesTempService->moveToFinal($filename);

            $image = (new Images())
                ->setPicture($filename)
                ->setTrick($trick);

            $em->persist($image);
            $trick->addImage($image);
        }
    }

    private function handleImagesReplace(Tricks $trick, Request $request): void
    {
        $replacements = $request->request->all('replace_images', []);

        foreach ($trick->getImages() as $image) {

            $id = $image->getIdentifier();

            if (!isset($replacements[$id])) {
                continue;
            }

            $new = trim($replacements[$id]);

            if (!$new) {
                continue;
            }

            $this->imagesUploaderService->delete($image->getPicture());
            $this->imagesTempService->moveToFinal($new);

            $image->setPicture($new);
        }
    }

    private function handleImagesDelete(Tricks $trick, Request $request, EntityManagerInterface $em): void
    {
        $removed = $request->request->all('removed_images', []);

        foreach ($trick->getImages() as $image) {

            if (!in_array($image->getIdentifier(), $removed, true)) {
                continue;
            }

            $this->imagesUploaderService->delete($image->getPicture());

            $trick->removeImage($image);
            $em->remove($image);
        }
    }


    private function handleVideosAdd(Tricks $trick, Request $request, EntityManagerInterface $em): void
    {
        $urls = $request->request->all('videos_urls', []);

        $result = $this->videoManager->createFromUrls($urls, $trick);

        foreach ($result['errors'] as $error) {
            // optionnel: log ou flash
        }

        foreach ($result['videos'] as $video) {
            $video->setTrick($trick);
            $trick->addVideo($video);

            $em->persist($video);
        }
    }

    private function handleVideosReplace(Tricks $trick, Request $request): void
    {
        $replacements = $request->request->all('replace_videos', []);

        foreach ($trick->getVideos() as $video) {

            $id = $video->getYoutubeId();

            if (!$id || !isset($replacements[$id])) {
                continue;
            }

            $newUrl = trim($replacements[$id]);

            if (!$newUrl) {
                continue;
            }

            $video->setUrl($newUrl);
        }
    }


    private function handleVideosDelete(Tricks $trick, Request $request, EntityManagerInterface $em): void
    {
        $removed = $request->request->all('removed_videos', []);

        foreach ($trick->getVideos() as $video) {

            $id = $video->getYoutubeId() ?? $video->getUrl();

            if (!in_array($id, $removed, true)) {
                continue;
            }

            $trick->removeVideo($video);
            $em->remove($video);
        }
    }

    private function cleanupEmptyImages(Tricks $trick, EntityManagerInterface $em): void
    {
        foreach ($trick->getImages() as $image) {

            if (empty($image->getPicture())) {
                $trick->removeImage($image);
                $em->remove($image);
            }
        }
    }
}
