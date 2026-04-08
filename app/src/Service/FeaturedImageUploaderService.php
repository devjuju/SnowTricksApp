<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class FeaturedImageUploaderService
{
    // Service responsable du stockage FINAL des images principales (featured image)
    // 👉 Utilisé uniquement après validation du formulaire

    private Filesystem $filesystem;

    public function __construct(
        private string $targetDirectoryFeaturedImage, // Dossier final de stockage
        private SluggerInterface $slugger             // Sécurisation des noms de fichiers
    ) {
        $this->filesystem = new Filesystem();

        // On garantit que le dossier existe (évite erreurs en production)
        $this->ensureDirectoryExists();
    }

    /**
     * Upload une image principale en stockage définitif
     *
     * @param UploadedFile|null $file
     * @param string $type Préfixe du fichier (optionnel)
     * @return string|null Nom du fichier généré
     */
    public function upload(?UploadedFile $file, string $type = 'image'): ?string
    {
        // Sécurité : aucun fichier → aucune action
        if (!$file) {
            return null;
        }

        // Nettoyage du nom original (Unicode safe + sans extension)
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        // Transformation en slug (évite caractères spéciaux / injections)
        $safeName = $this->slugger->slug($originalName);

        // Génération d’un nom unique (évite collisions)
        $filename = $type . '_' . $safeName . '_' . uniqid() . '.' . $file->guessExtension();

        // Déplacement vers le dossier final
        $file->move($this->targetDirectoryFeaturedImage, $filename);

        return $filename;
    }

    /**
     * Supprime une image existante
     * 👉 Utilisé lors de la modification ou suppression d’un trick
     */
    public function delete(?string $filename): void
    {
        // Sécurité : si aucun fichier → on sort
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectoryFeaturedImage . '/' . $filename;

        // Vérifie l'existence avant suppression (robustesse)
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * Crée le dossier de stockage s’il n’existe pas
     */
    private function ensureDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->targetDirectoryFeaturedImage)) {
            $this->filesystem->mkdir($this->targetDirectoryFeaturedImage, 0755);
        }
    }
}
