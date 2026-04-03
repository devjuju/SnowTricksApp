<?php

namespace App\Service;

use App\Entity\Users;
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

    public function upload(UploadedFile $file): array
    {
        $this->validateFile($file);

        $publicId = bin2hex(random_bytes(16));

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

        // 🔥 stocker mapping publicId → filename
        $images = $this->getAll();
        $images[$publicId] = $filename;

        $this->session?->set($this->sessionKey, $images);

        return [
            'publicId' => $publicId,
            'filename' => $filename,
        ];
    }

    public function add(string $publicId, string $filename): void
    {
        $images = $this->getAll();
        $images[$publicId] = $filename;

        $this->session?->set($this->sessionKey, $images);
    }

    public function getAll(): array
    {
        return $this->session?->get($this->sessionKey, []) ?? [];
    }


    // ✅ FIX logique
    public function moveToFinal(string $filename): bool
    {
        $tmpPath = $this->tempDir . '/' . $filename;
        $finalPath = $this->finalDir . '/' . $filename;

        if (!$this->filesystem->exists($tmpPath)) {
            return false;
        }

        $this->filesystem->rename($tmpPath, $finalPath, true);

        // ❌ mauvais : removeByFilename
        // ✔ correct : rebuild session map

        $images = $this->getAll();

        $images = array_filter(
            $images,
            fn($file) => $file !== $filename
        );

        $this->session?->set($this->sessionKey, $images);

        return true;
    }


    public function delete(string $filename): void
    {
        $path = $this->tempDir . '/' . $filename;

        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }

        $this->removeFromSession($filename);
    }

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

    private function removeFromSession(string $filename): void
    {
        $images = $this->getAll();

        foreach ($images as $publicId => $file) {
            if ($file === $filename) {
                unset($images[$publicId]);
            }
        }

        if ($images) {
            $this->session?->set($this->sessionKey, $images);
        } else {
            $this->session?->remove($this->sessionKey);
        }
    }

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

    // =========================
    // 📂 GET FULL TEMP PATH
    // =========================
    public function getPath(string $filename): string
    {
        return rtrim($this->tempDir, '/') . '/' . ltrim($filename, '/');
    }

    // =========================
    // 📂 GET FINAL PATH
    // =========================
    public function getFinalPath(string $filename): string
    {
        return rtrim($this->finalDir, '/') . '/' . ltrim($filename, '/');
    }

    // =========================
    // ✅ FILE EXISTS (TEMP)
    // =========================
    public function exists(string $filename): bool
    {
        return $this->filesystem->exists($this->getPath($filename));
    }

    public function moveToFinalByPublicId(string $publicId): ?string
    {
        $images = $this->getAll();

        if (!isset($images[$publicId])) {
            return null;
        }

        $filename = $images[$publicId];

        $tmpPath = $this->tempDir . '/' . $filename;
        $finalPath = $this->finalDir . '/' . $filename;

        if (!$this->filesystem->exists($tmpPath)) {
            return null;
        }

        $this->filesystem->rename($tmpPath, $finalPath, true);

        unset($images[$publicId]);
        $this->session?->set($this->sessionKey, $images);

        return $filename;
    }

    public function getByUserId(int $userId): array
    {
        return $this->session->get('images_' . $userId, []);
    }

    public function getByPublicId(string $publicId): ?string
    {
        $images = $this->getAll();
        return $images[$publicId] ?? null;
    }

    public function existsByPublicId(string $publicId): bool
    {
        return isset($this->getAll()[$publicId]);
    }

    public function cleanup(): void
    {
        foreach ($this->getAll() as $filename) {
            $path = $this->tempDir . '/' . $filename;

            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }

        $this->session?->remove($this->sessionKey);
    }

    // ✅ FIX OBLIGATOIRE (simple et propre)
    // ✔️ sécurise toujours les arrays
    public function getUnused(array $replacements = [], array $removed = []): array
    {
        $images = $this->getAll();

        if (!$images) {
            return [];
        }

        $usedInReplace = array_values($replacements);

        // 🔥 FORCE SAFE ARRAYS
        $removed = is_array($removed) ? $removed : [$removed];
        $usedInReplace = is_array($usedInReplace) ? $usedInReplace : [$usedInReplace];

        $blacklist = array_merge($usedInReplace, $removed);

        return array_filter(
            $images,
            fn($filename, $publicId) => !in_array($publicId, $blacklist, true),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
