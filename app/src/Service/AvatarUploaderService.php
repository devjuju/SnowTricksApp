<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class AvatarUploaderService
{
    // Service dédié à la gestion des avatars utilisateurs (upload + suppression)
    // 👉 Contrairement au TempService, ici on est sur du stockage FINAL

    private Filesystem $filesystem;

    public function __construct(
        private string $targetDirectoryAvatar, // Dossier final de stockage des avatars
        private SluggerInterface $slugger      // Permet de sécuriser les noms de fichiers
    ) {
        $this->filesystem = new Filesystem();

        // On s'assure que le dossier existe (évite erreurs en prod)
        $this->ensureDirectoryExists();
    }

    /**
     * Upload un avatar utilisateur
     *
     * @param UploadedFile|null $file Fichier envoyé
     * @param string $type Permet de préfixer le nom (ex: image, avatar)
     * @return string|null Nom du fichier généré
     */
    public function upload(?UploadedFile $file, string $type = 'image'): ?string
    {
        // Si aucun fichier → on ne fait rien
        if (!$file) {
            return null;
        }

        // Nettoyage du nom original (évite pathinfo + gère Unicode)
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        // Slug du nom pour éviter caractères spéciaux / sécurité
        $safeName = $this->slugger->slug($originalName);

        // Génération d’un nom unique (évite collisions)
        $filename = $type . '_' . $safeName . '_' . uniqid() . '.' . $file->guessExtension();

        // Déplacement du fichier vers le dossier final
        $file->move($this->targetDirectoryAvatar, $filename);

        return $filename;
    }

    /**
     * Supprime un avatar existant
     *
     * @param string|null $filename
     */
    public function delete(?string $filename): void
    {
        // Sécurité : si rien → on ne fait rien
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectoryAvatar . '/' . $filename;

        // Vérifie que le fichier existe avant suppression
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * Crée le dossier de stockage s'il n'existe pas
     */
    private function ensureDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->targetDirectoryAvatar)) {
            $this->filesystem->mkdir($this->targetDirectoryAvatar, 0755);
        }
    }
}
