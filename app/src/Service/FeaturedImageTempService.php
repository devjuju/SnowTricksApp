<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;

class FeaturedImageTempService
{
    private ?\Symfony\Component\HttpFoundation\Session\SessionInterface $session;
    private Filesystem $filesystem;

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

    public function upload(UploadedFile $file): string
    {
        // Remplacement de pathinfo()
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        $safeName = $this->slugger->slug($originalName);

        $filename = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->tempDir, $filename);

        $this->session?->set('temp_featured_image', $filename);

        return $filename;
    }

    public function get(): ?string
    {
        return $this->session?->get('temp_featured_image');
    }

    public function clear(): void
    {
        $filename = $this->get();

        if ($filename) {
            $path = $this->tempDir . '/' . $filename;

            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }

        $this->session?->remove('temp_featured_image');
    }

    public function moveToFinal(string $filename): void
    {
        $tmpPath = $this->tempDir . '/' . $filename;
        $finalPath = $this->finalDir . '/' . $filename;

        if ($this->filesystem->exists($tmpPath)) {
            $this->filesystem->rename($tmpPath, $finalPath, true);
        }

        $this->session?->remove('temp_featured_image');
    }

    private function ensureDirectoryExists(string $dir): void
    {
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir, 0755);
        }
    }
}
