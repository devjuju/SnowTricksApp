<?php

namespace App\Service;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Entity\Videos;
use App\Service\ImagesUploaderService;
use App\Service\ImagesTempService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class TrickMediaManagerService
{
    public function __construct(
        private ImagesUploaderService $imagesUploaderService,
        private ImagesTempService $imagesTempService,
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function handle(Tricks $trick, Request $request): void
    {
        $this->handleImages($trick, $request);
        $this->handleVideos($trick, $request);
        $this->cleanupEmptyImages($trick);
    }

    private function handleImages(Tricks $trick, Request $request): void
    {
        // REPLACEMENT
        $replacements = $request->request->all('replace_images', []);

        foreach ($replacements as $old => $new) {
            foreach ($trick->getImages() as $image) {
                if ($image->getPicture() === $old) {
                    $this->imagesUploaderService->delete($old);
                    $image->setPicture($new);
                    break;
                }
            }
        }

        $replacedFiles = array_values($replacements);

        // AJOUT TEMP → FINAL
        foreach ($this->imagesTempService->moveAllToFinal() as $filename) {
            if (!$filename) continue;

            if (in_array($filename, $replacedFiles, true)) {
                continue;
            }

            $image = new Images();
            $image->setPicture($filename);
            $image->setTrick($trick);

            $trick->addImage($image);
            $this->em->persist($image);
        }

        // SUPPRESSION
        $removedImages = $request->request->all('removed_images', []);

        foreach ($removedImages as $filename) {
            if (!$filename || $filename === 'new') continue;

            foreach ($trick->getImages() as $image) {
                if ($image->getPicture() === $filename) {
                    if ($this->security->isGranted('MEDIA_DELETE', $image)) {
                        $this->imagesUploaderService->delete($filename);
                        $trick->removeImage($image);
                        $this->em->remove($image);
                    }
                    break;
                }
            }
        }
    }

    private function handleVideos(Tricks $trick, Request $request): void
    {
        // SUPPRESSION
        $removedVideos = $request->request->all('removed_videos', []);

        foreach ($removedVideos as $id) {
            if (!ctype_digit((string)$id)) continue;

            $video = $this->em->getRepository(Videos::class)->find($id);

            if ($video && $this->security->isGranted('MEDIA_DELETE', $video)) {
                $trick->removeVideo($video);
                $this->em->remove($video);
            }
        }
    }

    private function cleanupEmptyImages(Tricks $trick): void
    {
        foreach ($trick->getImages() as $image) {
            if (!$image->getPicture()) {
                $trick->removeImage($image);
                $this->em->remove($image);
            }
        }
    }
}
