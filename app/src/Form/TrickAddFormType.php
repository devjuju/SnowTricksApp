<?php
// src/Form/TrickAddFormType.php
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

class TrickAddFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $featuredImageTempService = $options['featured_image_temp_service'];

        $builder
            // Titre
            ->add('title', TextType::class, [
                'attr' => ['placeholder' => 'Titre de la figure', 'class' => 'w-full rounded-xl border px-4 py-2'],
            ])
            // Contenu
            ->add('content', TextareaType::class, [
                'attr' => ['placeholder' => 'Décrivez la figure en détail', 'class' => 'w-full h-28 sm:h-36 rounded-xl border px-4 py-2'],
            ])
            // Catégorie
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'attr' => ['class' => 'rounded-xl border px-4 py-2 pr-10'],
            ])
            // Image mise en avant
            ->add('featuredImage', FileType::class, [
                'mapped' => false,
                'required' => false, // On laisse false car le service gère la temporaire
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Veuillez sélectionner une image valide (jpeg, png, webp).'
                    ),
                    // Validation personnalisée pour forcer une image principale
                    new Callback(function ($value, ExecutionContextInterface $context) use ($featuredImageTempService) {
                        $form = $context->getRoot();

                        // On valide uniquement si le formulaire a été soumis
                        if ($form->isSubmitted() && !$value && !$featuredImageTempService->get()) {
                            $context->buildViolation('Veuillez ajouter une image principale.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            // Champ caché pour suppression de l'image mise en avant
            ->add('deleteFeaturedImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            // Images secondaires
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
            // Champs cachés temporaire pour AJAX (images_tmp)
            ->add('images_tmp', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => false,
                'mapped' => false, // Ne lie pas à l'entité
                'required' => false,
                'label' => false,
            ])
            // Vidéos
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
            // Bouton Ajouter
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'bg-blue-600 text-white px-6 py-3 rounded-2xl hover:bg-blue-800 font-semibold transition-all duration-300 uppercase',
                ]
            ]);
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
            'featured_image_temp_service' => null, // On injecte le service depuis le contrôleur
        ]);

        $resolver->setAllowedTypes('featured_image_temp_service', ['null', 'object']);
    }
}
