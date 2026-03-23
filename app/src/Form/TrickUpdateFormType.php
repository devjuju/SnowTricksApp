<?php
// src/Form/TrickUpdateFormType.php
namespace App\Form;

use App\Entity\Tricks;
use App\Entity\Categories;
use App\Form\ImageType;
use App\Form\VideoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TrickUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération sécurisée du service temporaire
        $featuredImageTempService = $options['featured_image_temp_service'] ?? null;

        $builder
            ->add('title', TextType::class, [
                'attr' => ['placeholder' => 'Titre de la figure', 'class' => 'w-full rounded-xl border px-4 py-2'],
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un titre.'])]
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['placeholder' => 'Décrivez la figure en détail', 'class' => 'w-full h-28 sm:h-36 rounded-xl border px-4 py-2'],
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un contenu.'])]
            ])
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'attr' => ['class' => 'rounded-xl border px-4 py-2 pr-10'],
                'constraints' => [new NotNull(['message' => 'Veuillez choisir une catégorie.'])]
            ])
            // Image mise en avant
            ->add('featuredImage', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Veuillez sélectionner une image valide (jpeg, png, webp).'
                    ),
                    new Callback(function ($value, ExecutionContextInterface $context) use ($featuredImageTempService) {
                        $trick = $context->getRoot()->getData();
                        // On vérifie seulement si le service est disponible
                        if ($featuredImageTempService) {
                            if (!$value && !$trick->getFeaturedImage() && !$featuredImageTempService->get()) {
                                $context->buildViolation('Veuillez ajouter une image principale.')
                                    ->addViolation();
                            }
                        }
                    }),
                ],
            ])
            ->add('deleteFeaturedImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__image__',
                'required' => false,
                'label' => false,
            ])
            ->add('images_tmp', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => false,
                'mapped' => false,
                'required' => false,
                'label' => false,
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__video__',
                'required' => false,
                'label' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => ['class' => 'bg-blue-600 text-white px-6 py-3 rounded-2xl hover:bg-blue-800 font-semibold transition-all duration-300 uppercase']
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => ['class' => 'bg-[#f8285a] text-white px-6 py-3 rounded-2xl hover:bg-[#d81a48] font-semibold transition-all duration-300 uppercase '],
            ]);
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
            'featured_image_temp_service' => null, // injection du service temporaire
        ]);

        $resolver->setAllowedTypes('featured_image_temp_service', ['null', 'object']);
    }
}
