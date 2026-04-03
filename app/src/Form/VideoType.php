<?php

namespace App\Form;

use App\Entity\Videos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Form\DataTransformer\YoutubeUrlToIdTransformer;


class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', TextType::class, [
            'label' => false,
            'required' => false,
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez entrer une URL YouTube',
                ]),
                new Assert\Regex([
                    'pattern' => '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/',
                    'message' => 'URL YouTube invalide',
                ]),
            ],
            'attr' => [
                'placeholder' => 'Lien YouTube',
                'class' => 'item-input'
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Videos::class,
        ]);
    }
}
