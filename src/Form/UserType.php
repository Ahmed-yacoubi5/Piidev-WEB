<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordOptions = [
            'label' => 'Mot de passe',
            'attr' => ['placeholder' => 'Entrez votre mot de passe'],
        ];

        if ($options['is_edit'] ?? false) {
            $passwordOptions['required'] = false;
            $passwordOptions['attr']['placeholder'] = 'Laissez vide si inchangé';
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Entrez votre prénom'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez votre nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Entrez votre email'],
            ])
            ->add('password', PasswordType::class, $passwordOptions)
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['placeholder' => 'Entrez votre numéro de téléphone'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de profil',
                'required' => false,
                'data_class' => null,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}