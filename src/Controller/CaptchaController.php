<?php

namespace App\Controller;

use App\Service\SimpleCaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaptchaController extends AbstractController
{
    #[Route('/generate-captcha', name: 'app_generate_captcha')]
    public function generateCaptcha(SimpleCaptchaService $captchaService): Response
    {
        // Générer un nouveau captcha
        $captcha = $captchaService->generateCaptcha();
        
        // Rediriger vers l'image générée
        return $this->redirect($captcha['image_url']);
    }
    
    #[Route('/refresh-captcha', name: 'app_refresh_captcha')]
    public function refreshCaptcha(SimpleCaptchaService $captchaService): Response
    {
        // Générer un nouveau captcha
        $captcha = $captchaService->generateCaptcha();
        
        // Renvoyer l'URL en JSON pour les requêtes AJAX
        return $this->json(['image_url' => $captcha['image_url']]);
    }
} 