<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;

class ImagesTempService
{
    private ?SessionInterface $session;
    private Filesystem $filesystem;
    private string $sessionKey = 'temp_images';

    public function __construct(
        private string $tempDir,
        private string $finalDir,
        private SluggerInterface $slugger,
        RequestStack $requestStack
    ) {
        $this->session = $requestStack->getSession();
        $this->filesystem = new Filesystem();

        $this->ensureDirectoryExists($this->tempDir);
        $this->ensureDirectoryExists($this->finalDir);
    }

    public function setContext(string $context): void
    {
        $this->sessionKey = 'temp_images_' . $context;
    }

    // -------------------------
    // UPLOAD TEMP
    // -------------------------
    public function upload(UploadedFile $file): string
    {
        $this->validateFile($file);

        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        $safeName = $this->slugger->slug($originalName);
        $extension = $file->guessExtension() ?: 'bin';

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

    // ✅ FIX add()
    public function add(string $filename): void
    {
        $images = $this->getAll();

        // 🔥 anti doublon strict
        if (in_array($filename, $images, true)) {
            return;
        }

        // 🔥 sécurité FS
        if (!$this->filesystem->exists($this->tempDir . '/' . $filename)) {
            return;
        }

        $images[] = $filename;

        $this->session?->set(
            $this->sessionKey,
            array_values(array_unique($images))
        );
    }

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

    public function getAll(): array
    {
        return $this->session?->get($this->sessionKey, []) ?? [];
    }

    // -------------------------
    // MOVE TEMP -> FINAL
    // -------------------------
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

    // ✅ FIX moveAllToFinal()
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

            // 🔥 ANTI MOVE DOUBLE
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

    // -------------------------
    // DELETE SAFE (TEMP + FINAL)
    // -------------------------
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

    // -------------------------
    // CLEAR SESSION + TEMP FILES
    // -------------------------
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

    // -------------------------
    // REPLACE (SAFE VERSION)
    // -------------------------
    public function replace(string $old, UploadedFile $file): string
    {
        $newFilename = $this->upload($file);

        $this->delete($old);

        return $newFilename;
    }

    // -------------------------
    // SESSION HELPERS
    // -------------------------
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

    // -------------------------
    // FS HELPERS
    // -------------------------
    private function ensureDirectoryExists(string $dir): void
    {
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir, 0755);
        }
    }

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
