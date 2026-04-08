<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;

class FeaturedImageTempService
{
    // Service de gestion des images principales TEMPORAIRES (UX upload)
    // 👉 Permet de stocker temporairement une image avant validation du formulaire

    private ?\Symfony\Component\HttpFoundation\Session\SessionInterface $session;
    private Filesystem $filesystem;

    public function __construct(
        private string $tempDir,   // Dossier temporaire
        private string $finalDir,  // Dossier final
        private SluggerInterface $slugger,
        RequestStack $requestStack,
    ) {
        $this->session = $requestStack->getSession();
        $this->filesystem = new Filesystem();

        // Sécurisation : on s'assure que les dossiers existent
        $this->ensureDirectoryExists($this->tempDir);
        $this->ensureDirectoryExists($this->finalDir);
    }

    /**
     * Upload une image temporaire
     * 👉 Utilisé lors de la preview avant validation du formulaire
     */
    public function upload(UploadedFile $file): string
    {
        // Nettoyage du nom du fichier (gestion unicode + sécurité)
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        $safeName = $this->slugger->slug($originalName);

        // Génération d’un nom unique pour éviter collisions
        $filename = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

        // Stockage en dossier temporaire
        $file->move($this->tempDir, $filename);

        // Sauvegarde en session pour suivre l’état de l’upload
        $this->session?->set('temp_featured_image', $filename);

        return $filename;
    }

    /**
     * Récupère le fichier temporaire actuel
     */
    public function get(): ?string
    {
        $value = $this->session?->get('temp_featured_image');

        // Sécurité : on s'assure que c'est bien une string
        return is_string($value) ? $value : null;
    }

    /**
     * Supprime le fichier temporaire (cleanup UX)
     * 👉 Évite les fichiers orphelins
     */
    public function clear(): void
    {
        $filename = $this->get();

        if ($filename) {
            $path = $this->tempDir . '/' . $filename;

            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }

        // Nettoyage session
        $this->session?->remove('temp_featured_image');
    }

    /**
     * Déplace le fichier du temporaire vers le stockage final
     * 👉 Appelé uniquement si le formulaire est validé
     */
    public function moveToFinal(string $filename): void
    {
        $tmpPath = $this->tempDir . '/' . $filename;
        $finalPath = $this->finalDir . '/' . $filename;

        if ($this->filesystem->exists($tmpPath)) {
            $this->filesystem->rename($tmpPath, $finalPath, true);
        }

        // Nettoyage session après validation
        $this->session?->remove('temp_featured_image');
    }

    /**
     * Garantit l'existence des dossiers (évite crash en prod)
     */
    private function ensureDirectoryExists(string $dir): void
    {
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir, 0755);
        }
    }

    /**
     * Retourne le chemin complet d'un fichier temporaire
     * 👉 Utile pour debug ou manipulation interne
     */
    public function getTempPath(string $filename): string
    {
        return $this->tempDir . '/' . $filename;
    }
}
