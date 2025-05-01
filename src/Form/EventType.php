<?php

namespace App\Form;
namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Title is required.']),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s]*$/',
                        'message' => 'Title must not contain special characters.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Start date is required.']),
                ],
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'End date is required.']),
                ],
            ])
            ->add('location', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Location is required.']),
                ],
            ])
            ->add('capacity', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Capacity is required.']),
                    new Type([
                        'type' => 'integer',
                        'message' => 'Capacity must be an integer.',
                    ]),
                    new Positive(['message' => 'Capacity must be a positive number.']),
                ],
            ])
            ->add('isPublic', CheckboxType::class, [
                'required' => false,
            ]);

        // Ajout d'une validation personnalisée pour vérifier que l'endDate est après startDate
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['startDate'], $data['endDate'])) {
                // Conversion des chaînes en objets DateTime
                $startDate = new \DateTime($data['startDate']);
                $endDate = new \DateTime($data['endDate']);

                // Vérification que la date de fin est après la date de début
                if ($startDate >= $endDate) {
                    $form->get('endDate')->addError(new FormError('End date should be later than start date.'));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}


