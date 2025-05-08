<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;

use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoginController extends AbstractController
{
    
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();
        

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion de votre pare-feu.
        throw new \Exception('This should never be reached!');
    }

#[Route('/chatbotFront', name: 'user_chatbot_front', methods: ['GET','POST'])]
public function chatbotFront(
    Request $request,
    HttpClientInterface $client,
    Security $security // ajouter l'injection de dépendance
): Response {
    $session = $request->getSession();
    $apiKey = 'AIzaSyAVIj0z5bkr-qxMU4sFGCBbcs4CbnflgYw';
    $model  = 'gemini-1.5-flash';

    /** @var \App\Entity\User $user */
$user = $security->getUser();// récupérer l'utilisateur connecté
    $firstName = $user->getFirstName(); // suppose que ta classe User a ces méthodes
    $lastName  = $user->getLastName();

    $userMessage = $request->request->get('message', '');
    $botResponse = null;
    $error = null;

    $history = $session->get('chat_history', []);

    try {
        if ($request->isMethod('GET')) {
            $history = [];

            $botResponse = "Welcome to your smart HR assistant, ask me any question about your tasks and I will help you realise your goals more efficiently.";
            $history[] = ['sender' => 'bot', 'message' => $botResponse];

            $session->set('chat_history', $history);
        } elseif ($request->isMethod('POST') && trim($userMessage) !== '') {
            $history[] = ['sender' => 'user', 'message' => $userMessage];

            $prompt = <<<TXT
You are the smart HR assistant for a company. Answer the following employee question clearly and helpfully.

Employee: "{$userMessage}"
TXT;

            $botResponse = $this->callGemini($client, $apiKey, $model, $prompt);
            $history[] = ['sender' => 'bot', 'message' => $botResponse];

            $session->set('chat_history', $history);
        }
    } catch (\Exception $e) {
        $error = 'Erreur API : ' . $e->getMessage();
    }

    return $this->render('chatbot/chatbot.html.twig', [
        'message'    => '',
        'response'   => $botResponse,
        'error'      => $error,
        'history'    => $history,
        'first_name' => $firstName,
        'last_name'  => $lastName,
    ]);
}


    

private function callGemini(HttpClientInterface $client, string $apiKey, string $model, string $prompt): string
{
    $url = sprintf(
        'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
        $model,
        $apiKey
    );

    $payload = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
    ];

    $resp = $client->request('POST', $url, ['json' => $payload]);
    $data = $resp->toArray(false);

    return $data['candidates'][0]['content']['parts'][0]['text']
         ?? '(réponse introuvable)';
}


#[Route('/my-profile', name: 'user_edit_profile', methods: ['GET', 'POST'])]
public function editMyProfile(
    Request $request,
    EntityManagerInterface $entityManager,
    Security $security
): Response {
    /** @var \App\Entity\User $user */
    $user = $security->getUser();

    $form = $this->createForm(UserType::class, $user, [
        'is_edit' => true
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('kernel.project_dir').'/public/uploads',
                $newFilename
            );
            $user->setImage('/uploads/'.$newFilename);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
        return $this->redirectToRoute('user_chatbot_front');
    }

    return $this->render('user/editFront.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
        'button_label' => 'Mettre à jour'
    ]);
}

}