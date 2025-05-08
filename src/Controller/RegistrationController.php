<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;
class RegistrationController extends AbstractController
{
    
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserService $userService,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        MailerInterface $mailer // 👈 ajout du mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $userService->register($user, $plainPassword);
    
            // 1. Envoi du SMS
            try {
                $sid    = $params->get('twilio.sid');
                $token  = $params->get('twilio.auth_token');
                $from   = $params->get('twilio.from');
    
                $client = new Client($sid, $token);
                $client->messages->create($user->getPhoneNumber(), [
                    'from' => $from,
                    'body' => "Bonjour {$user->getFirstName()}, votre inscription a bien été enregistrée. Bienvenue !"
                ]);
    
                $logger->info('✅ SMS envoyé via Twilio.');
            } catch (\Exception $e) {
                $logger->error('❌ Erreur lors de l’envoi du SMS : ' . $e->getMessage());
            }
    
            // 2. Envoi de l’email
            try {
                $email = (new Email())
                    ->from('ahmedchihi00@gmail.com') // 👈 à personnaliser
                    ->to($user->getEmail())
                    ->subject('Bienvenue sur notre site !')
                    ->text("Bonjour {$user->getFirstName()}, votre compte a été créé avec succès.")
                    ->html("<p>Bonjour <strong>{$user->getFirstName()}</strong>,<br>Votre compte a été créé avec succès. Bienvenue parmi nous !</p>");
    
                $mailer->send($email);
                $logger->info('✅ Email envoyé avec succès.');
            } catch (\Exception $e) {
                $logger->error('❌ Erreur lors de l’envoi de l’email : ' . $e->getMessage());
            }
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
