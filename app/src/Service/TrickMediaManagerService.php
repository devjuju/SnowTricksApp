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

/**
 * Service de gestion des médias liés à un Trick
 *
 * Responsabilités :
 * - gestion des images (upload temporaire → final, remplacement, suppression)
 * - gestion des vidéos (suppression uniquement)
 * - synchronisation des entités Doctrine
 *
 * ⚠️ Service métier central : il orchestre plusieurs sous-services
 */
class TrickMediaManagerService
{
    public function __construct(
        private ImagesUploaderService $imagesUploaderService,
        private ImagesTempService $imagesTempService,
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    /**
     * Point d'entrée principal :
     * applique toutes les opérations médias à un Trick
     */
    public function handle(Tricks $trick, Request $request): void
    {
        $this->handleImages($trick, $request);
        $this->handleVideos($trick, $request);

        // Nettoyage final des images invalides (sécurité + cohérence DB)
        $this->cleanupEmptyImages($trick);
    }

    // =========================
    // 🖼️ GESTION DES IMAGES
    // =========================

    /**
     * Gère l'ensemble des opérations sur les images :
     * - remplacement d'image existante
     * - ajout d'images temporaires vers final
     * - suppression d'images
     */
    private function handleImages(Tricks $trick, Request $request): void
    {
        /**
         * =========================
         * REPLACEMENT D'IMAGES
         * =========================
         * Remplace une image existante par une nouvelle version
         */
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

        /**
         * =========================
         * AJOUT DES IMAGES TEMP → FINAL
         * =========================
         * Récupère les fichiers temporaires validés
         * et les transforme en entités Images persistées
         */
        foreach ($this->imagesTempService->moveAllToFinal() as $filename) {
            if (!$filename) continue;

            // Ignore les fichiers déjà remplacés
            if (in_array($filename, $replacedFiles, true)) {
                continue;
            }

            $image = new Images();
            $image->setPicture($filename);
            $image->setTrick($trick);

            $trick->addImage($image);
            $this->em->persist($image);
        }

        /**
         * =========================
         * SUPPRESSION D'IMAGES
         * =========================
         * Supprime les images demandées par l'utilisateur
         * avec vérification des permissions (Voter)
         */
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

    // =========================
    // 🎥 GESTION DES VIDÉOS
    // =========================

    /**
     * Gère la suppression des vidéos associées au Trick
     */
    private function handleVideos(Tricks $trick, Request $request): void
    {
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

    // =========================
    // 🧹 NETTOYAGE
    // =========================

    /**
     * Supprime les images incohérentes ou invalides
     * (ex: image sans fichier associé)
     */
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
