<?php

namespace App\Form\Type;

use App\Service\SimpleCaptchaService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SimpleCaptchaType extends AbstractType
{
    private $captchaService;

    public function __construct(SimpleCaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $service = $this->captchaService;
        
        $builder->add('captcha_code', TextType::class, [
            'attr' => [
                'autocomplete' => 'off',
                'class' => 'form-control'
            ],
            'constraints' => [
                new Callback([
                    'callback' => function($value, ExecutionContextInterface $context) use ($service) {
                        if (!$service->validateCaptcha($value)) {
                            $context->buildViolation('Code de sécurité invalide.')
                                ->addViolation();
                        }
                    }
                ])
            ]
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Générer un nouveau captcha
        $captcha = $this->captchaService->generateCaptcha();
        
        // Ajouter les variables à la vue
        $view->vars['captcha_image_url'] = $captcha['image_url'];
        $view->vars['captcha_refresh_url'] = '/refresh-captcha';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Code de sécurité',
            'mapped' => false,
            'required' => true,
            'invalid_message' => 'Le code de sécurité est incorrect.',
            'compound' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'simple_captcha';
    }

    public function getParent()
    {
        return TextType::class;
    }
} 