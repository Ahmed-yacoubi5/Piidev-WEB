<?php
// src/EventListener/LoginRedirectListener.php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginRedirectListener implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        // Rediriger en fonction du rôle
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // Rediriger vers /add_Reservation
            $response = new RedirectResponse($this->urlGenerator->generate('app_user_index'));
            $event->setResponse($response);
        } else {
            // Rediriger vers une autre page (par exemple, la page d'accueil)
            $response = new RedirectResponse($this->urlGenerator->generate('user_chatbot_front'));
            $event->setResponse($response);
        }
    }
}