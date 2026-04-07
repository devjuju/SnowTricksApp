<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class SlugService
{
    public function __construct(
        private SluggerInterface $slugger,
        private EntityManagerInterface $em
    ) {}

    /**
     * Génère un slug unique pour une entité.
     * 
     * @param object $entity L'entité (ex: Tricks)
     * @param string $field Le champ utilisé pour générer le slug (ex: title)
     * @param EntityManagerInterface|null $em
     * @return string Slug unique
     */
    public function generateUniqueSlug(object $entity, string $field = 'title', ?EntityManagerInterface $em = null): string
    {
        $em ??= $this->em;

        $getter = $this->resolveGetter($entity, $field);
        $value = $entity->$getter();

        $baseSlug = $this->slugger->slug($value)->lower()->toString();

        return $this->makeUniqueSlug($entity, $baseSlug, $em);
    }

    private function resolveGetter(object $entity, string $field): string
    {
        $getter = 'get' . ucfirst($field);

        if (!method_exists($entity, $getter)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Le champ %s n\'existe pas sur l\'entité %s',
                    $field,
                    $entity::class
                )
            );
        }

        return $getter;
    }


    private function makeUniqueSlug(object $entity, string $baseSlug, EntityManagerInterface $em): string
    {
        $repository = $em->getRepository($entity::class);
        $entityId = method_exists($entity, 'getId') ? $entity->getId() : null;

        $slug = $baseSlug;
        $i = 1;

        while ($this->slugExists($repository, $slug, $entityId)) {
            $slug = $baseSlug . '-' . $i++;
        }

        return $slug;
    }

    private function slugExists($repository, string $slug, ?int $entityId): bool
    {
        $existing = $repository->findOneBy(['slug' => $slug]);

        if (!$existing) {
            return false;
        }

        return $entityId && $existing->getId() === $entityId ? false : true;
    }
}
