<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;

class AvatarTempService
{
    private ?\Symfony\Component\HttpFoundation\Session\SessionInterface $session;
    private Filesystem $filesystem;

    public function __construct(
        private string $tempDir,
        private string $finalDir,
        private SluggerInterface $slugger,
        RequestStack $requestStack
    ) {
        // Initialisation des dépendances et récupération de la session utilisateur
        // Permet de stocker temporairement le nom du fichier uploadé
        $this->session = $requestStack->getSession();

        // Utilitaire Symfony pour manipuler les fichiers (création, suppression, déplacement)
        $this->filesystem = new Filesystem();

        // Vérifie que les dossiers existent (temporaire et final)
        $this->ensureDirectoryExists($this->tempDir);
        $this->ensureDirectoryExists($this->finalDir);
    }

    public function upload(UploadedFile $file): string
    {
        // Nettoyage du nom original du fichier (suppression extension)
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        // Transformation en nom safe (slug) pour éviter caractères spéciaux
        $safeName = $this->slugger->slug($originalName);

        // Génération d’un nom unique pour éviter collisions
        $filename = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

        // Génération d’un nom unique pour éviter collisions
        $file->move($this->tempDir, $filename);

        // Stockage en session pour suivre le fichier temporaire
        $this->session?->set('temp_avatar', $filename);

        return $filename;
    }

    public function get(): ?string
    {
        // Retourne le nom du fichier temporaire stocké en session
        return $this->session?->get('temp_avatar');
    }

    public function clear(): void
    {
        $filename = $this->get();

        if ($filename) {
            $path = $this->tempDir . '/' . $filename;

            // Supprime le fichier temporaire s’il existe
            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }

        // Nettoyage de la session
        $this->session?->remove('temp_avatar');
    }

    public function moveToFinal(string $filename): void
    {
        $tmpPath = $this->tempDir . '/' . $filename;
        $finalPath = $this->finalDir . '/' . $filename;

        // Déplace le fichier du dossier temporaire vers le dossier final
        if ($this->filesystem->exists($tmpPath)) {
            $this->filesystem->rename($tmpPath, $finalPath, true);
        }

        // Nettoyage session après validation
        $this->session?->remove('temp_avatar');
    }

    private function ensureDirectoryExists(string $dir): void
    {
        // Crée le dossier s’il n’existe pas (sécurité environnement Docker / prod)
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir, 0755);
        }
    }
}
