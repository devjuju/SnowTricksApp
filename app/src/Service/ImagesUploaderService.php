<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class ImagesUploaderService
{
    // Service de gestion des images en stockage FINAL
    // 👉 Utilisé pour les uploads simples ou validation après phase temporaire

    private Filesystem $filesystem;

    public function __construct(
        private string $targetDirectoryImages, // Dossier final des images
        private SluggerInterface $slugger      // Sécurisation des noms de fichiers
    ) {
        $this->filesystem = new Filesystem();

        // Sécurisation : création du dossier si inexistant
        $this->ensureDirectoryExists();
    }

    /**
     * Upload d’une image unique
     * 👉 Utilisé pour les uploads simples
     */
    public function upload(?UploadedFile $file, string $type = 'image'): ?string
    {
        if (!$file) {
            return null;
        }

        // Nettoyage du nom original (support Unicode)
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        // Sécurisation du nom (suppression caractères spéciaux)
        $safeName = $this->slugger->slug($originalName);

        // Nom unique pour éviter collisions
        $filename = $type . '_' . $safeName . '_' . uniqid() . '.' . $file->guessExtension();

        // Déplacement vers stockage final
        $file->move($this->targetDirectoryImages, $filename);

        return $filename;
    }

    /**
     * Upload multiple fichiers
     *
     * @param UploadedFile[]|null $files
     */
    public function uploadMultiple(?array $files, string $type = 'image'): array
    {
        $uploadedFiles = [];

        if (!$files) {
            return [];
        }

        foreach ($files as $file) {
            $filename = $this->upload($file, $type);

            if ($filename) {
                $uploadedFiles[] = $filename;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Supprime une image du stockage
     */
    public function delete(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectoryImages . '/' . $filename;

        // Vérification avant suppression (robustesse)
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * Suppression multiple sécurisée
     *
     * @param string[]|null $filenames
     */
    public function deleteMultiple(?array $filenames): void
    {
        if (!$filenames) {
            return;
        }

        foreach ($filenames as $filename) {
            $this->delete($filename);
        }
    }

    /**
     * Création du dossier de stockage si absent
     */
    private function ensureDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->targetDirectoryImages)) {
            $this->filesystem->mkdir($this->targetDirectoryImages, 0755);
        }
    }
}
