<?php

namespace App\Service;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MediaManagerService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImagesUploaderService $imagesUploaderService,
        private ImagesTempService $imagesTempService,
        private VideoManagerService $videoManager,
        private FeaturedImageUploaderService $featuredImageUploaderService,
    ) {}

    // =========================
    // 🖼 IMAGES
    // =========================

    public function handleImages(Tricks $trick, Request $request)
    {
        $removed = $request->request->all('removed_images', []);
        $replacements = (array) $request->request->all('replace_images', []);

        $this->deleteImages($trick, $removed);
        $this->replaceImages($trick, $replacements);
        $this->addImages($trick, $removed, $replacements);
    }

    private function replaceImages(Tricks $trick, array $replacements): void
    {
        foreach ($trick->getImages() as $image) {

            $oldId = $image->getPublicId();

            if (!isset($replacements[$oldId])) {
                continue;
            }

            $newId = $replacements[$oldId];

            $temp = $this->imagesTempService->getByPublicId($newId);
            if (!$temp) {
                continue;
            }

            $filename = $this->imagesTempService->moveToFinalByPublicId($newId);

            if (!$filename) {
                continue;
            }

            // 🔥 CLEAN REPLACE (IMPORTANT)
            $this->imagesUploaderService->delete($image->getPicture());

            $image->setPicture($filename);
            $image->setPublicId($newId); // 🔥 FIX CRITIQUE
        }
    }

    private function addImages(Tricks $trick, array $removed, array $replacements): void
    {
        $blacklist = array_merge($removed, array_keys($replacements));

        $tempImages = $this->imagesTempService->getAll();

        foreach ($tempImages as $publicId => $filename) {

            if (in_array($publicId, $blacklist, true)) {
                continue;
            }

            // déjà existant dans le trick
            $exists = $trick->getImages()->exists(
                fn($k, $img) => $img->getPublicId() === $publicId
            );

            if ($exists) {
                continue;
            }

            $finalFilename = $this->imagesTempService->moveToFinalByPublicId($publicId);

            if (!$finalFilename) {
                continue;
            }

            $image = (new Images())
                ->setPublicId($publicId)
                ->setPicture($finalFilename)
                ->setTrick($trick);

            $trick->addImage($image);
            $this->em->persist($image);
        }
    }

    private function deleteImages(Tricks $trick, array $removed): void
    {
        foreach ($trick->getImages() as $image) {

            if (!in_array($image->getPublicId(), $removed, true)) {
                continue;
            }

            $this->imagesUploaderService->delete($image->getPicture());

            $trick->removeImage($image);
            $this->em->remove($image);
        }
    }



    // =========================
    // 🎬 VIDEOS
    // =========================

    public function handleVideos(Tricks $trick, Request $request): void
    {
        $this->addVideos($trick, $request);
        $this->replaceVideos($trick, $request);
        $this->deleteVideos($trick, $request);
    }

    private function addVideos(Tricks $trick, Request $request): void
    {
        $urls = $request->request->all('videos_urls', []);
        $removed = $request->request->all('removed_videos', []);

        $result = $this->videoManager->createFromUrls($urls, $trick);

        foreach ($result['videos'] as $video) {

            $id = $video->getYoutubeId();

            if (!$id) {
                continue;
            }

            if (in_array($id, $removed, true)) {
                continue;
            }

            $alreadyExists = $trick->getVideos()->exists(
                fn($k, $v) => $v->getYoutubeId() === $id
            );

            if ($alreadyExists) {
                continue;
            }

            $video->setTrick($trick);
            $trick->addVideo($video);

            $this->em->persist($video);
        }
    }

    private function replaceVideos(Tricks $trick, Request $request): void
    {
        $replacements = $request->request->all('replace_videos', []);

        foreach ($trick->getVideos() as $video) {

            $oldId = $video->getYoutubeId();

            if (!$oldId || !isset($replacements[$oldId])) {
                continue;
            }

            $newUrl = trim($replacements[$oldId]);

            if (!$newUrl) {
                continue;
            }

            $video->setUrl($newUrl);
        }
    }

    private function deleteVideos(Tricks $trick, Request $request): void
    {
        $removed = $request->request->all('removed_videos', []);

        foreach ($trick->getVideos() as $video) {

            $id = $video->getYoutubeId();

            if (!in_array($id, $removed, true)) {
                continue;
            }

            $trick->removeVideo($video);
            $this->em->remove($video);
        }
    }

    // =========================
    // 🧹 DELETE ALL
    // =========================

    public function deleteAll(Tricks $trick): void
    {
        if ($trick->getFeaturedImage()) {
            $this->featuredImageUploaderService->delete($trick->getFeaturedImage());
        }

        foreach ($trick->getImages() as $image) {
            $this->imagesUploaderService->delete($image->getPicture());
            $this->em->remove($image);
        }

        foreach ($trick->getVideos() as $video) {
            $this->em->remove($video);
        }

        $this->em->remove($trick);
    }
}
