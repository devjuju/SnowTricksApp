<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class FeaturedImageUploaderService
{
    private Filesystem $filesystem;

    public function __construct(
        private string $targetDirectoryFeaturedImage,
        private SluggerInterface $slugger
    ) {
        $this->filesystem = new Filesystem();
        $this->ensureDirectoryExists();
    }

    public function upload(?UploadedFile $file, string $type = 'image'): ?string
    {
        if (!$file) {
            return null;
        }

        // Remplacement de pathinfo()
        $originalName = (new UnicodeString($file->getClientOriginalName()))
            ->beforeLast('.')
            ->toString();

        $safeName = $this->slugger->slug($originalName);

        $filename = $type . '_' . $safeName . '_' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->targetDirectoryFeaturedImage, $filename);

        return $filename;
    }

    public function delete(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectoryFeaturedImage . '/' . $filename;

        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    private function ensureDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->targetDirectoryFeaturedImage)) {
            $this->filesystem->mkdir($this->targetDirectoryFeaturedImage, 0755);
        }
    }
}
