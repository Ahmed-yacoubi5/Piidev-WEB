<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('email', null, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis.']),
                    new Email(['message' => 'Veuillez entrer un email valide.']),
                ]
            ])
            ->add('nom', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis.']),
                    new Length(['min' => 3, 'minMessage' => 'Le nom doit avoir au moins 3 caractères.'])
                ]
            ])
            ->add('prenom', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est requis.']),
                    new Length(['min' => 3, 'minMessage' => 'Le prénom doit avoir au moins 3 caractères.'])
                ]
            ])
            ->add('age', null, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'âge est requis.']),
                    new Range(['min' => 10, 'max' => 99, 'notInRangeMessage' => 'L\'âge doit être compris entre 10 et 99.'])
                ]
            ])
            ->add('numtlf', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est requis.']),
                    new Regex(['pattern' => '/^\d{8}$/', 'message' => 'Le numéro de téléphone doit comporter 8 chiffres.'])
                ]
            ])
            ->add('photoDeProfile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => !$isEdit,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit avoir au moins 6 caractères.'])
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
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