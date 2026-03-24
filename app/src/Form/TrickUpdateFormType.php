<?php
// src/Form/TrickUpdateFormType.php
namespace App\Form;

use App\Entity\Tricks;
use App\Form\Traits\TrickBaseFieldsTrait;
use App\Form\TrickBaseFieldsTrait as FormTrickBaseFieldsTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickUpdateFormType extends AbstractType
{
    use FormTrickBaseFieldsTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $featuredImageTempService = $options['featured_image_temp_service'] ?? null;

        $this->addBaseFields($builder);

        $builder
            ->add('featuredImage', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    ),
                    new Callback(function ($value, ExecutionContextInterface $context) use ($featuredImageTempService) {
                        $trick = $context->getRoot()->getData();

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
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => [
                    'class' => 'bg-blue-600 text-white px-6 py-3 rounded-2xl hover:bg-blue-800 font-semibold transition-all duration-300 uppercase'
                ]
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'bg-[#f8285a] text-white px-6 py-3 rounded-2xl hover:bg-[#d81a48] font-semibold transition-all duration-300 uppercase'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
            'featured_image_temp_service' => null,
        ]);

        $resolver->setAllowedTypes('featured_image_temp_service', ['null', 'object']);
    }
}
