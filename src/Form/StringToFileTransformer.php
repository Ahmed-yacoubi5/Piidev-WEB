<?php
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class StringToFileTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        // Convertit l'entité en données pour le formulaire
        if ($value instanceof File) {
            return $value;
        }

        return null;
    }

    public function reverseTransform($value)
    {
        // Convertit les données du formulaire en entité
        if ($value instanceof File) {
            return $value;
        }

        // Correction : parenthèse fermante ajoutée ici
        if (is_string($value)) {
            return new File($value);
        }

        return null;
    }
}