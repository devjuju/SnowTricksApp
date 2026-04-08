<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;

/**
 * Service de gestion des images temporaires
 *
 * Rôle principal :
 * - gérer les uploads temporaires (session + filesystem)
 * - permettre la validation avant passage en "final"
 * - déplacer les fichiers vers stockage définitif
 * - assurer la cohérence entre session et filesystem
 *
 * ⚠️ Ce service agit comme un mini "storage manager"
 */
class ImagesTempService
{
    private ?SessionInterface $session;
    private Filesystem $filesystem;

    /**
     * Clé de session utilisée pour stocker les fichiers temporaires
     */
    private string $sessionKey = 'temp_images';

    public function __construct(
        private string $tempDir,
        private string $finalDir,
        private SluggerInterface $slugger,
        RequestStack $requestStack
    ) {
        $this->session = $requestStack->getSession();
        $this->filesystem = new Filesystem();

        // S'assure que les dossiers existent dès l'initialisation du service
        $this->ensureDirectoryExists($this->tempDir);
        $this->ensureDirectoryExists($this->finalDir);
    }

    /**
     * Permet de contextualiser les uploads (ex: trick, user, post...)
     *
     * Chaque contexte a sa propre clé de session
     */
    public function setContext(string $context): void
    {
        $this->sessionKey = 'temp_images_' . $context;
    }

    // =========================
    // 📤 UPLOAD TEMPORAIRE
    // =========================

    /**
     * Upload un fichier en zone temporaire
     *
     * Étapes :
     * - validation sécurité (type + taille)
     * - génération d'un nom sécurisé unique
     * - déplacement vers dossier temporaire
     * - stockage en session
     */
    public function upload(UploadedFile $file): string
    {
        $this->validateFile($file);

        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        $safeName = $this->slugger->slug($originalName);
        $extension = $file->guessExtension() ?: 'bin';

        // Nom unique pour éviter collisions
        $filename = sprintf(
            '%s-%s.%s',
            $safeName,
            bin2hex(random_bytes(8)),
            $extension
        );

        $file->move($this->tempDir, $filename);

        $this->add($filename);

        return $filename;
    }

    /**
     * Ajoute un fichier à la session temporaire
     *
     * ⚠️ protège contre :
     * - doublons
     * - fichiers inexistants
     */
    public function add(string $filename): void
    {
        $images = $this->getAll();

        if (in_array($filename, $images, true)) {
            return;
        }

        if (!$this->filesystem->exists($this->tempDir . '/' . $filename)) {
            return;
        }

        $images[] = $filename;

        $this->session?->set(
            $this->sessionKey,
            array_values(array_unique($images))
        );
    }

    /**
     * Upload multiple fichiers
     */
    public function uploadMultiple(?array $files, string $type = 'image'): array
    {
        $uploadedFiles = [];

        if (!$files) {
            return [];
        }

        foreach ($files as $file) {
            $filename = $this->upload($file, $type);

            if ($filename && !in_array($filename, $uploadedFiles, true)) {
                $uploadedFiles[] = $filename;
            }
        }

        return array_values(array_unique($uploadedFiles));
    }

    /**
     * Retourne tous les fichiers temporaires du contexte courant
     */
    public function getAll(): array
    {
        return $this->session?->get($this->sessionKey, []) ?? [];
    }

    // =========================
    // 🔁 TRANSITION TEMP → FINAL
    // =========================

    /**
     * Déplace un fichier du dossier temporaire vers le dossier final
     */
    public function moveToFinal(string $filename): bool
    {
        $tmpPath = $this->tempDir . '/' . $filename;
        $finalPath = $this->finalDir . '/' . $filename;

        if (!$this->filesystem->exists($tmpPath)) {
            return false;
        }

        $this->filesystem->rename($tmpPath, $finalPath, true);

        $this->removeFromSession($filename);

        return true;
    }

    /**
     * Déplace tous les fichiers temporaires vers le dossier final
     *
     * ⚠️ Gère aussi :
     * - fichiers déjà existants
     * - nettoyage session
     * - anti double move
     */
    public function moveAllToFinal(): array
    {
        $files = $this->getAll();
        $moved = [];

        foreach ($files as $filename) {

            if (!$filename) continue;

            $tmpPath = $this->tempDir . '/' . $filename;
            $finalPath = $this->finalDir . '/' . $filename;

            if (!$this->filesystem->exists($tmpPath)) {
                continue;
            }

            // Empêche écrasement ou double déplacement
            if ($this->filesystem->exists($finalPath)) {
                $this->removeFromSession($filename);
                continue;
            }

            $this->filesystem->rename($tmpPath, $finalPath, true);
            $this->removeFromSession($filename);

            $moved[] = $filename;
        }

        return array_values(array_unique($moved));
    }

    // =========================
    // 🗑️ SUPPRESSION
    // =========================

    /**
     * Supprime un fichier (temp + final + session)
     *
     * ⚠️ suppression globale volontaire
     */
    public function delete(string $filename): void
    {
        $paths = [
            $this->tempDir . '/' . $filename,
            $this->finalDir . '/' . $filename,
        ];

        foreach ($paths as $path) {
            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }

        $this->removeFromSession($filename);
    }

    /**
     * Nettoie tous les fichiers temporaires du contexte
     */
    public function clear(): void
    {
        foreach ($this->getAll() as $filename) {
            $path = $this->tempDir . '/' . $filename;

            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }

        $this->session?->remove($this->sessionKey);
    }

    /**
     * Remplace un fichier existant par un nouveau upload
     */
    public function replace(string $old, UploadedFile $file): string
    {
        $newFilename = $this->upload($file);

        $this->delete($old);

        return $newFilename;
    }

    // =========================
    // 🧠 SESSION HELPERS
    // =========================

    /**
     * Retire un fichier de la session
     *
     * ⚠️ maintient la cohérence session ↔ filesystem
     */
    private function removeFromSession(string $filename): void
    {
        $images = array_filter(
            $this->getAll(),
            fn($img) => $img !== $filename
        );

        if (!empty($images)) {
            $this->session?->set($this->sessionKey, array_values($images));
        } else {
            $this->session?->remove($this->sessionKey);
        }
    }

    // =========================
    // 📁 FILESYSTEM HELPERS
    // =========================

    /**
     * Crée les dossiers s'ils n'existent pas
     */
    private function ensureDirectoryExists(string $dir): void
    {
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir, 0755);
        }
    }

    /**
     * Validation de sécurité des fichiers uploadés
     *
     * ⚠️ protège contre :
     * - fichiers non image
     * - fichiers trop volumineux
     */
    private function validateFile(UploadedFile $file): void
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file->getMimeType(), $allowed, true)) {
            throw new \RuntimeException('Type de fichier non autorisé.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \RuntimeException('Fichier trop volumineux (max 5MB).');
        }
    }
}
