<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true // Pour adapter le formulaire en mode édition
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
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

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index');
    }


    #[Route('/chatbot', name: 'user_chatbot', methods: ['GET','POST'])]
public function chatbot(
    Request $request,
    HttpClientInterface $client,
    UserRepository $repo
): Response {
    $session = $request->getSession();
    $apiKey = 'AIzaSyAVIj0z5bkr-qxMU4sFGCBbcs4CbnflgYw';
    $model  = 'gemini-1.5-flash';

    $userMessage = $request->request->get('message', '');
    $botResponse = null;
    $error = null;

    // Charger l’historique (sera vidé dans GET)
    $history = $session->get('chat_history', []);

    // Construire la description des users
    $users = $repo->findAll();
    $lines = [];
    foreach ($users as $p) {
        $lines[] = sprintf(
            '- %s (%s) à %0.2f €',
            $p->getEmail(),
            $p->getPhoneNumber(),
            $p->getStatus()
        );
    }
    $listeusers = implode("\n", $lines);

    try {
        if ($request->isMethod('GET')) {
            // 🧼 Vider l’historique
            $history = [];

            $prompt = <<<TXT
Bienvenue ! Voici nos users disponibles :
{$listeusers}

Fais-moi un petit texte commercial chaleureux en français, en mentionnant ces users et leurs atouts.
TXT;
            $botResponse = $this->callGemini($client, $apiKey, $model, $prompt);

            // 👇 Ajouter le message de bienvenue à l’historique
            $history[] = ['sender' => 'bot', 'message' => $botResponse];

            // 👇 Sauvegarder l'historique réinitialisé avec le message
            $session->set('chat_history', $history);
        } elseif ($request->isMethod('POST') && trim($userMessage) !== '') {
            $history[] = ['sender' => 'user', 'message' => $userMessage];

            $prompt = <<<TXT
Voici la liste de nos users :
{$listeusers}

Question du client : "{$userMessage}"
Réponds en français clair et précis.
TXT;

            $botResponse = $this->callGemini($client, $apiKey, $model, $prompt);
            $history[] = ['sender' => 'bot', 'message' => $botResponse];

            $session->set('chat_history', $history);
        }
    } catch (\Exception $e) {
        $error = 'Erreur API : ' . $e->getMessage();
    }

    return $this->render('chatbot/chatbot.html.twig', [
        'message'  => '',
        'response' => $botResponse,
        'error'    => $error,
        'history'  => $history,
    ]);
}

    

//


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
}