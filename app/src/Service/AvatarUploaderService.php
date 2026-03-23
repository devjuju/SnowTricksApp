<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class AvatarUploaderService
{
    private Filesystem $filesystem;

    public function __construct(
        private string $targetDirectoryAvatar,
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

        $file->move($this->targetDirectoryAvatar, $filename);

        return $filename;
    }

    public function delete(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectoryAvatar . '/' . $filename;

        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    private function ensureDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->targetDirectoryAvatar)) {
            $this->filesystem->mkdir($this->targetDirectoryAvatar, 0755);
        }
    }
}
