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
        $em = $em ?? $this->em;

        $getter = 'get' . ucfirst($field);
        if (!method_exists($entity, $getter)) {
            throw new \InvalidArgumentException("Le champ {$field} n'existe pas sur l'entité " . get_class($entity));
        }

        $value = $entity->$getter();
        $baseSlug = strtolower($this->slugger->slug($value)->toString());
        $slug = $baseSlug;
        $i = 1;

        $repository = $em->getRepository(get_class($entity));
        $entityId = method_exists($entity, 'getId') ? $entity->getId() : null;

        // Boucle pour générer un slug unique
        while ($existing = $repository->findOneBy(['slug' => $slug])) {
            // Ignorer l'entité en cours si on est en édition
            if ($entityId && $existing->getId() === $entityId) {
                break;
            }

            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
