<?php
// src/Form/ImageType.php
namespace App\Form;

use App\Entity\Images;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => false,
            'mapped' => false,
            'required' => false,
            'attr' => [
                'class' => 'item-input',
                'accept' => 'image/jpeg,image/png,image/webp',
            ],
            'constraints' => [
                new ImageConstraint([
                    'maxSize' => '50M',
                    'maxSizeMessage' => 'L’image est trop lourde. Taille maximale : {{ limit }}.',
                    'minWidth' => 200,
                    'maxWidth' => 4000,
                    'minWidthMessage' => 'La largeur de l’image est trop petite ({{ width }}px). Largeur minimale : {{ min_width }}px.',
                    'maxWidthMessage' => 'La largeur de l’image est trop grande ({{ width }}px). Largeur maximale : {{ max_width }}px.',
                    'minHeight' => 200,
                    'maxHeightMessage' => 'La hauteur de l’image est trop grande ({{ height }}px). Hauteur maximale : {{ max_height }}px.',
                    'minHeightMessage' => 'La hauteur de l’image est trop petite ({{ height }}px). Hauteur minimale : {{ min_height }}px.',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, WebP)',
                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Images::class,
        ]);
    }
}
