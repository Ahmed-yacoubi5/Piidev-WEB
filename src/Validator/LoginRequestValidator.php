<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class LoginRequestValidator
{
    private $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    public function validateLoginRequest(string $email, string $password): array
    {
        $errors = [];

        // Email validation
        $emailConstraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank([
                    'message' => 'L\'adresse email ne peut pas être vide.',
                ]),
                new Assert\Email([
                    'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    'mode' => 'strict',
                ]),
                new Assert\Length([
                    'max' => 180,
                    'maxMessage' => 'L\'adresse email ne peut pas dépasser {{ limit }} caractères.',
                ]),
            ],
        ]);

        $emailViolations = $this->validator->validate(['email' => $email], $emailConstraints);
        if (count($emailViolations) > 0) {
            foreach ($emailViolations as $violation) {
                $errors['email'][] = $violation->getMessage();
            }
        }

        // Password validation
        $passwordConstraints = new Assert\Collection([
            'password' => [
                new Assert\NotBlank([
                    'message' => 'Le mot de passe ne peut pas être vide.',
                ]),
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    'max' => 4096,
                    'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                ]),
            ],
        ]);

        $passwordViolations = $this->validator->validate(['password' => $password], $passwordConstraints);
        if (count($passwordViolations) > 0) {
            foreach ($passwordViolations as $violation) {
                $errors['password'][] = $violation->getMessage();
            }
        }

        return $errors;
    }
} 