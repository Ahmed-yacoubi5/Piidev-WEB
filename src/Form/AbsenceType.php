<?php

namespace App\Form;

use App\Entity\Absence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('datedebut', null, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // 👈 Remplit automatiquement avec la date actuelle


            ])
            ->add('datefin', null, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // 👈 Remplit automatiquement avec la date actuelle

                
            ])
            ->add('employee_id')
            ->add('statut')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Absence::class,
        ]);
    }
}
