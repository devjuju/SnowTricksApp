<?php

namespace App\Form;

use App\Entity\Categories;
use App\Form\ImageType;
use App\Form\VideoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

trait TrickBaseFieldsTrait
{
    public function addBaseFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Titre de la figure',
                    'class' => 'w-full rounded-xl border px-4 py-2'
                ],

            ])
            ->add('content', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Décrivez la figure en détail',
                    'class' => 'w-full h-28 sm:h-36 rounded-xl border px-4 py-2'
                ],

            ])
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'attr' => [
                    'class' => 'rounded-xl border px-4 py-2 pr-10'
                ],

            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__name__',
                'required' => false,
            ])

            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__name__',
                'delete_empty' => true,
            ]);
    }
}
