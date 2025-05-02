<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SimpleCaptchaService
{
    private $requestStack;
    private $captchaDir;
    private $webPath;
    private $length;
    private $chars;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->captchaDir = __DIR__ . '/../../public/captcha-images';
        $this->webPath = '/captcha-images';
        $this->length = 4;
        $this->chars = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    }

    /**
     * Récupère la session
     */
    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    /**
     * Génère un code captcha et l'image associée
     */
    public function generateCaptcha(): array
    {
        // Générer un code aléatoire
        $code = $this->generateRandomCode();
        
        // Stocker le code en session
        $this->getSession()->set('captcha_code', $code);
        
        // Créer une image et y dessiner le code
        $imagePath = $this->createCaptchaImage($code);
        
        return [
            'code' => $code,
            'image_url' => $this->webPath . '/' . basename($imagePath)
        ];
    }

    /**
     * Vérifie si le code captcha entré est valide
     */
    public function validateCaptcha(string $userInput): bool
    {
        $storedCode = $this->getSession()->get('captcha_code');
        
        if (!$storedCode) {
            return false;
        }
        
        // Comparaison insensible à la casse
        return strtoupper($userInput) === strtoupper($storedCode);
    }

    /**
     * Génère un code aléatoire pour le captcha
     */
    private function generateRandomCode(): string
    {
        $code = '';
        $charsLength = strlen($this->chars) - 1;
        
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->chars[rand(0, $charsLength)];
        }
        
        return $code;
    }

    /**
     * Crée une image captcha avec le code
     */
    private function createCaptchaImage(string $code): string
    {
        // Dimensions de l'image
        $width = 200;
        $height = 50;
        
        // Créer une image
        $image = imagecreatetruecolor($width, $height);
        
        // Couleurs
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $noiseColor = imagecolorallocate($image, 200, 200, 200);
        
        // Remplir le fond
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        
        // Ajouter du bruit (points)
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        // Ajouter du bruit (lignes)
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        // Écrire le texte dans l'image
        $fontSize = 20;
        $angle = 0;
        
        // Calculer le positionnement du texte
        $textX = 10;
        $textY = $height - 15;
        
        // Dessiner chaque caractère avec un angle légèrement différent
        $length = strlen($code);
        $charWidth = $width / $length;
        
        for ($i = 0; $i < $length; $i++) {
            $char = $code[$i];
            $angle = rand(-10, 10);
            $x = $textX + $i * $charWidth;
            
            // Trouver une police système disponible
            $font = 5; // Police par défaut de GD
            
            // Dessiner le caractère
            imagestring($image, $font, $x, $textY - 15, $char, $textColor);
        }
        
        // Définir le chemin de l'image
        $captchaFilename = 'captcha_' . uniqid() . '.png';
        $captchaPath = $this->captchaDir . '/' . $captchaFilename;
        
        // Sauvegarder l'image
        imagepng($image, $captchaPath);
        imagedestroy($image);
        
        return $captchaPath;
    }
} 