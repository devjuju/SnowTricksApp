<?php

namespace App\Service;

use App\Entity\Tricks;
use App\Entity\Images;
use App\Service\ImagesUploaderService;
use App\Service\ImagesTempService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class ImageManagerService
{
    public function __construct(
        private ImagesUploaderService $imagesUploaderService,
        private ImagesTempService $imagesTempService,
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function handle(Tricks $trick, Request $request): void
    {
        $this->replaceImages($trick, $request);
        $this->addImages($trick);
        $this->removeImages($trick, $request);
        $this->cleanupEmptyImages($trick);
    }


    private function replaceImages(Tricks $trick, Request $request): void
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
    }

    private function addImages(Tricks $trick,): void
    {

        $replacements = [];
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
    }


    private function removeImages(Tricks $trick, Request $request): void
    {

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
