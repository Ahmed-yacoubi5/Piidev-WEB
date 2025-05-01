<?php

namespace App\Form;

use App\Entity\Conges;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CongesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('datedebut', null, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(),
            ])
            ->add('datefin', null, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(),
            ])
            ->add('employee_id')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conges::class,
        ]);
    }
}
