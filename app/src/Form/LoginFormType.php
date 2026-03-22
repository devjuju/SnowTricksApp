<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'attr' => [
                    'placeholder' => 'Entrez votre nom d’utilisateur',
                ],
                'constraints' => [
                    new NotBlank([
                        "message" => "Veuillez entrer votre nom d'utilisateur."
                    ])
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'placeholder' => 'Entrez votre mot de passe',
                ],
                'constraints' => [
                    new NotBlank([
                        "message" => "Veuillez entrer votre mot de passe."
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
