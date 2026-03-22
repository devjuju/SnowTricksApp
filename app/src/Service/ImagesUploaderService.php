<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class ImagesUploaderService
{
    private Filesystem $filesystem;

    public function __construct(
        private string $targetDirectoryImages,
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

        $file->move($this->targetDirectoryImages, $filename);

        return $filename;
    }

    /**
     * @param UploadedFile[]|null $files
     */
    public function uploadMultiple(?array $files, string $type = 'image'): array
    {
        $uploadedFiles = [];

        if (!$files) {
            return $uploadedFiles;
        }

        foreach ($files as $file) {
            $filename = $this->upload($file, $type);

            if ($filename) {
                $uploadedFiles[] = $filename;
            }
        }

        return $uploadedFiles;
    }

    public function delete(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $this->targetDirectoryImages . '/' . $filename;

        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * @param string[]|null $filenames
     */
    public function deleteMultiple(?array $filenames): void
    {
        if (!$filenames) {
            return;
        }

        foreach ($filenames as $filename) {
            $this->delete($filename);
        }
    }

    private function ensureDirectoryExists(): void
    {
        if (!$this->filesystem->exists($this->targetDirectoryImages)) {
            $this->filesystem->mkdir($this->targetDirectoryImages, 0755);
        }
    }
}
