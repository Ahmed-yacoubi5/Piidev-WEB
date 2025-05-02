<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Doctrine\ORM\EntityManagerInterface;

class ResetPasswordController extends AbstractController
{
    private $utilisateurRepository;
    private $emailService;
    private $requestStack;
    private $passwordHasher;
    private $entityManager;

    public function __construct(
        UtilisateurRepository $utilisateurRepository,
        EmailService $emailService,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ) {
        $this->utilisateurRepository = $utilisateurRepository;
        $this->emailService = $emailService;
        $this->requestStack = $requestStack;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    /**
     * Étape 1: Formulaire pour demander la réinitialisation
     */
    #[Route('/reset-password', name: 'app_reset_password_request')]
    public function request(Request $request): Response
    {
        // Si l'utilisateur est déjà connecté, le rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse email']),
                    new Email(['message' => 'Veuillez entrer une adresse email valide'])
                ],
                'attr' => [
                    'class' => 'form-control form-control-user',
                    'placeholder' => 'Entrez votre adresse email'
                ],
                'label' => 'Adresse email'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->utilisateurRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un code à 6 chiffres
                $code = sprintf('%06d', mt_rand(0, 999999));
                $expiresAt = new \DateTime('+10 minutes');

                // Stocker le code et l'email dans la session
                $session = $this->requestStack->getSession();
                $session->set('reset_password', [
                    'email' => $email,
                    'code' => $code,
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s')
                ]);

                // Préparation du contenu du mail
                $htmlContent = $this->renderView('emails/reset_password.html.twig', [
                    'username' => $user->getNom() . ' ' . $user->getPrenom(),
                    'code' => $code,
                    'expiresAt' => $expiresAt
                ]);

                // Envoyer l'email avec le code
                try {
                    $this->emailService->sendHtmlEmail(
                        'Réinitialisation de votre mot de passe',
                        $htmlContent,
                        $email
                    );
                    $this->addFlash('success', 'Un email contenant un code de réinitialisation a été envoyé à votre adresse email.');
                    return $this->redirectToRoute('app_reset_password_verify');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de l\'email: ' . $e->getMessage());
                }
            } else {
                // Ne pas révéler que l'email n'existe pas pour des raisons de sécurité
                $this->addFlash('success', 'Si l\'adresse email existe, un email avec les instructions a été envoyé.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Étape 2: Vérification du code
     */
    #[Route('/reset-password/verify', name: 'app_reset_password_verify')]
    public function verify(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        
        // Récupérer les informations de réinitialisation de la session
        $resetData = $session->get('reset_password');
        if (!$resetData) {
            $this->addFlash('danger', 'Aucune demande de réinitialisation en cours. Veuillez recommencer.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        // Vérifier si le délai d'expiration est dépassé
        $expiresAt = new \DateTime($resetData['expires_at']);
        if ($expiresAt < new \DateTime()) {
            $session->remove('reset_password');
            $this->addFlash('danger', 'Le code de réinitialisation a expiré. Veuillez recommencer.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        $form = $this->createFormBuilder()
            ->add('code', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer le code reçu par email']),
                    new Length([
                        'min' => 6,
                        'max' => 6,
                        'exactMessage' => 'Le code doit comporter exactement 6 chiffres'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control form-control-user',
                    'placeholder' => 'Entrez le code reçu par email'
                ],
                'label' => 'Code de réinitialisation'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();

            if ($code === $resetData['code']) {
                // Le code est valide, autoriser la réinitialisation
                $session->set('reset_password_verified', true);
                return $this->redirectToRoute('app_reset_password_reset');
            } else {
                $this->addFlash('danger', 'Code de réinitialisation invalide. Veuillez réessayer.');
            }
        }

        return $this->render('reset_password/verify.html.twig', [
            'verifyForm' => $form->createView(),
            'email' => $resetData['email']
        ]);
    }

    /**
     * Étape 3: Réinitialisation du mot de passe
     */
    #[Route('/reset-password/reset', name: 'app_reset_password_reset')]
    public function reset(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        
        // Vérifier si l'utilisateur a bien validé le code
        if (!$session->get('reset_password_verified')) {
            $this->addFlash('danger', 'Vous devez vérifier votre code avant de réinitialiser votre mot de passe.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        // Récupérer les données de réinitialisation
        $resetData = $session->get('reset_password');
        if (!$resetData) {
            $this->addFlash('danger', 'Aucune demande de réinitialisation en cours.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'form-control form-control-user']],
                'required' => true,
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['placeholder' => 'Entrez votre nouveau mot de passe']
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'attr' => ['placeholder' => 'Répétez votre nouveau mot de passe']
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur
            $user = $this->utilisateurRepository->findOneBy(['email' => $resetData['email']]);
            
            if (!$user) {
                $this->addFlash('danger', 'Utilisateur introuvable.');
                return $this->redirectToRoute('app_login');
            }

            // Encoder le nouveau mot de passe
            $encodedPassword = $this->passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($encodedPassword);

            // Sauvegarder le mot de passe
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Nettoyer la session
            $session->remove('reset_password');
            $session->remove('reset_password_verified');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
} 