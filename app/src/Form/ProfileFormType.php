<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            // -----------------
            // EMAIL
            // -----------------
            ->add('email', EmailType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'L’email est obligatoire'),
                ],
            ])

            // -----------------
            // USERNAME
            // -----------------
            ->add('username', null, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Le pseudo est obligatoire'),
                    new Length(
                        min: 3,
                        max: 50,
                        minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le pseudo ne peut pas dépasser {{ limit }} caractères'
                    ),
                ],
            ])

            // -----------------
            // PASSWORD (OPTIONNEL)
            // -----------------
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                    ),
                ],
            ])

            // -----------------
            // AVATAR (UPLOAD)
            // -----------------
            ->add('avatar', FileType::class, [
                'label' => 'Avatar',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Veuillez sélectionner une image valide (jpeg, png, webp).'
                    ),
                ],
            ])

            // -----------------
            // DELETE AVATAR (HIDDEN)
            // -----------------
            ->add('deleteAvatar', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
